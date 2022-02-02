<?php

namespace App\Http\Controllers;

use App\Campaign;
use App\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Yajra\Datatables\Datatables;

class CommentController extends Controller
{

    public function index(){
        $title = trans('app.comments');
        return view('admin.comments', compact('title'));
    }

    public function commentData(){
        $user = Auth::user();

        if ($user->is_admin()){
            $comments = Comment::select('id','campaign_id','comment_id', 'author_name', 'author_email', 'author_ip', 'comment', 'approved', 'created_at')->orderBy('id', 'desc')->get();
        }else{
            //Get user specific comments
            $get_campaign_ids = $user->my_campaigns->pluck('id')->toArray();
            $comments = Comment::select('id','campaign_id','comment_id', 'author_name', 'author_email', 'author_ip', 'comment', 'approved', 'created_at')->whereIn('campaign_id', $get_campaign_ids)->orderBy('id', 'desc')->get();
        }

        return  DataTables::of($comments)
            ->editColumn('author_name',function($comment){
                $html = "<p><i class='fa fa-user'></i> {$comment->author_name} </p>";
                $html .= "<p><i class='fa fa-envelope-o'></i> {$comment->author_email} </p>";
                $html .= "<p><i class='fa fa-map-marker'></i> {$comment->author_ip} </p>";

                $label = '<p>';
                if ($comment->approved == 1){
                    $label .= '<span class="label label-success"><i class="fa fa-check-circle-o"></i> </span>';
                }else{
                    $label .= '<span class="label label-default"><i class="fa fa-exclamation-circle"></i> </span>';
                }

                if ($comment->comment_id){
                    $label .= '<span class="label label-default"><i class="fa fa-reply"></i> </span>';
                }else{
                    $label .= '<span class="label label-info"><i class="fa fa-comment-o"></i> </span>';
                }

                $label .= '</p>';

                $html .= $label;
                return $html;
            })
            ->editColumn('created_at',function($comment){
                return $comment->created_at_datetime();
            })
            ->editColumn('comment',function($comment){
                $html = '';
                    if ($comment->campaign) {
                    $html .= '<blockquote><i><a href="'.route('campaign_single', $comment->campaign->slug).'#comment-'.$comment->id.'" target="_blank">';
                    if ($comment->comment_id) {
                        $html .= '<i class="fa fa-reply"></i> ';
                    } else {
                        $html .= '<i class="fa fa-comment-o"></i> ';
                    }
                    $html .= safe_output($comment->campaign->title);

                    $html .= '</a></i></blockquote>';
                }
                $html .= safe_output($comment->comment);

                return $html;
            })
            ->addColumn('actions', function($comment){
                $button = '';

                if ($comment->approved != 1){
                    $button .= '<a href="javascript:;" class="btn btn-success comment_action" data-action="approve" data-id="'.$comment->id.'"><i class="fa fa-check-circle-o"></i> </a>';
                }
                $button .= '<a href="javascript:;" class="btn btn-danger comment_action" data-action="trash" data-id="'.$comment->id.'"><i class="fa fa-trash-o"></i> </a>';

                return $button;
            })
            ->removeColumn('id', 'author_email', 'author_ip', 'approved', 'comment_id', 'campaign_id')
            ->rawColumns(['author_name', 'comment', 'actions'])
            ->make(true);
    }



    public function postComments(Request $request,$id){
        $rules = [
            'comment'   => 'required|min:20|max:8000'
        ];
        $user_id = 0;

        $author_name = $request->author_name;
        $author_email = $request->author_email;
        if ( ! Auth::check()){
            $rules['author_name']  = 'required';
            $rules['author_email']  = 'required';
        }else{
            $user = Auth::user();
            $user_id = $user->id;
            $author_name = $user->name;
            $author_email = $user->email;
        }
        $this->validate($request, $rules);

        $ip = $request->ip();
        $comment_id = $request->comment_id;
        if ( ! $comment_id){
            $comment_id = 0;
        }

        //Auto approve if this ad owner
        $approved = 0;
        if (Auth::check()){
            $user_id = Auth::user()->id;
            $ad = Campaign::find($id);
            if ($user_id == $ad->user_id){
                $approved = 1;
            }
        }else{
            $request->session()->flash('success', trans('app.comment_posted'));
        }

        $data = [
            'user_id'       => $user_id,
            'campaign_id'         => $id,
            'comment_id'    => $comment_id,
            'author_name'   => strip_tags($author_name),
            'author_email'  => strip_tags($author_email),
            'author_ip'     => $ip,
            'comment'       => strip_tags($request->comment),
            'approved'      => $approved,
        ];

        //If it reply, make it approve
        if ($comment_id){
            $data['approved'] = 1;
        }
        $post_comment = Comment::create($data);

        $back_url = URL::previous().'#comment-'.$post_comment->id;
        return redirect($back_url);
    }


    public function commentAction(Request $request){
        $user = Auth::user();

        //Preventing unauthorised action
        $comment = Comment::find($request->comment_id);
        $comment_ad = Campaign::find($comment->campaign_id);

        if ($user->id != $comment_ad->user_id &&  ! $user->is_admin() ){
            return ['success' => false];
        }

        switch ($request->action){
            case 'approve':
                $comment->approved = 1;
                $comment->save();
                break;

            case 'trash':
                $comment->delete();
                break;
        }
        return ['success' => 1];

    }

}
