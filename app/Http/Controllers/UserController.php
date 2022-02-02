<?php

namespace App\Http\Controllers;

use App\Campaign;
use App\Comment;
use App\Country;
use App\Faq;
use App\Media;
use App\Payment;
use App\Post;
use App\Reward;
use App\Update;
use App\User;
use App\Withdrawal_preference;
use App\Withdrawal_request;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{

    public function index(){
        $title = trans('app.users');
        $users = User::orderBy('name', 'asc')->paginate(20);
        $users_count = User::count();
        return view('admin.users', compact('title', 'users', 'users_count'));
    }

    public function show($id = 0){
        if ($id){
            $title = trans('app.profile');
            $user = User::find($id);

            $is_user_id_view = true;
            return view('admin.profile', compact('title', 'user', 'is_user_id_view'));
        }
    }

    /**
     * @param $id
     * @param null $status
     * @return \Illuminate\Http\RedirectResponse
     */
    public function statusChange($id, $status = null){
        if(config('app.is_demo')){
            return redirect()->back()->with('error', 'This feature has been disable for demo');
        }

        $user = User::find($id);
        if ($user && $status){
            if ($status == 'approve'){
                $user->active_status = 1;
                $user->save();

            }elseif($status == 'block'){
                $user->active_status = 2;
                $user->save();
            }
        }
        return back()->with('success', trans('app.status_updated'));
    }



    public function profile(){
        $title = trans('app.profile');
        $user = Auth::user();
        return view('admin.profile', compact('title', 'user'));
    }

    public function profileEdit($id = null){
        $title = trans('app.profile_edit');
        $user = Auth::user();

        if ($id){
            $user = User::find($id);
        }

        $countries = Country::all();

        return view('admin.profile_edit', compact('title', 'user', 'countries'));
    }

    public function profileEditPost($id = null, Request $request){
        if(config('app.is_demo')){
            return redirect()->back()->with('error', 'This feature has been disable for demo');
        }

        $user = Auth::user();
        if ($id){
            $user = User::find($id);
        }
        //Validating
        $rules = [
            'email'    => 'required|email|unique:users,email,'.$user->id,
        ];
        $this->validate($request, $rules);

        $inputs = array_except($request->input(), ['_token']);
        $user->update($inputs);

        return back()->with('success', trans('app.profile_edit_success_msg'));
    }

    public function withdrawalPreference(){
        $title = trans('app.withdrawal_preference');
        $user = Auth::user();

        $countries = Country::all();
        return view('admin.withdrawal_preference', compact('title', 'user', 'countries'));
    }

    public function withdrawalPreferenceUpdate(Request $request){
        $user_id = Auth::user()->id;
        $rules = [
            'default_withdrawal_account'    =>'required'
        ];
        $this->validate($request, $rules);

        $data = [
            'default_withdrawal_account'    => $request->default_withdrawal_account,
            'paypal_email'                  => $request->paypal_email,
            'straks_address'                  => $request->straks_address,
            'bank_account_holders_name'     => $request->bank_account_holders_name,
            'bank_account_number'           => $request->bank_account_number,
            'swift_code'                    => $request->swift_code,
            'bank_name_full'                => $request->bank_name_full,
            'bank_branch_name'              => $request->bank_branch_name,
            'bank_branch_city'              => $request->bank_branch_city,
            'bank_branch_address'           => $request->bank_branch_address,
            'country_id'                    => $request->country_id,
            'user_id'                       => $user_id,
        ];

        $withdrawal_preference = Withdrawal_preference::whereUserId($user_id)->first();
        if ($withdrawal_preference){
            $withdrawal_preference->update($data);
        }else{
            Withdrawal_preference::create($data);
        }

        return redirect()->back()->with('success', trans('app.changes_has_been_saved'));

    }

    public function changePassword()
    {
        $title = trans('app.change_password');
        return view('admin.change_password', compact('title'));
    }

    public function changePasswordPost(Request $request){
        if(config('app.is_demo')){
            return redirect()->back()->with('error', 'This feature has been disable for demo');
        }
        $rules = [
            'old_password'  => 'required',
            'new_password'  => 'required|confirmed',
            'new_password_confirmation'  => 'required',
        ];
        $this->validate($request, $rules);

        $old_password = $request->old_password;
        $new_password = $request->new_password;
        //$new_password_confirmation = $request->new_password_confirmation;

        if(Auth::check())
        {
            $logged_user = Auth::user();

            if(Hash::check($old_password, $logged_user->password))
            {
                $logged_user->password = Hash::make($new_password);
                $logged_user->save();
                return redirect()->back()->with('success', trans('app.password_changed_msg'));
            }
            return redirect()->back()->with('error', trans('app.wrong_old_password'));
        }
    }


    public function userDeleteByAdmin($id){
        $user = User::find($id);
        $title = trans('app.account_deletion');
        return view('admin.account_deletion', compact('title', 'user'));
    }

    public function accountDeletion(){
        $user = Auth::user();
        $title = trans('app.account_deletion');
        return view('admin.account_deletion', compact('title', 'user'));
    }

    public function accountDeletionPost(){
        $user = Auth::user();
        $time_now = Carbon::now()->toDateTimeString();

        $msg = __('app.account_deletion_requested');

        if ($user->request_deletion === 1){
            $user->request_deletion = 2;
            $user->cancel_deletion_time = $time_now;
            $msg = __('app.deletion_request_canceled');
        }else{
            $user->request_deletion = 1;
            $user->request_deletion_time = $time_now;
        }

        $user->save();

        return back()->with('success', $msg );
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     *
     */
    public function requestedAccountDeletion(){
        $title = trans('app.account_deletion');
        $users = User::whereRequestDeletion(1)->get();
        return view('admin.requested_account_deletion', compact('title', 'users'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *
     * Delete account with all of it's assets
     */
    public function requestedDeletionAction(Request $request, $id){
        $user = User::find($id);

        if (! Auth::user()->is_admin() || ! $user ){
            abort(404, __('app.unauthorised_access') );
        }

        if ($request->action_type === 'cancel_deletion_request'){
            $user->request_deletion = null;
            $user->request_deletion_time = null;
            $user->cancel_deletion_time = null;
            $user->save();
            return back();
        }elseif ($request->action_type === 'approve_deletion_request'){
            Campaign::whereUserId($id)->delete();
            Comment::whereUserId($id)->delete();
            Faq::whereUserId($id)->delete();

            //Delete all media from disks
            $media_ids = Media::whereUserId($id)->pluck('id')->all();
            if (is_array($media_ids) && count($media_ids)){
                $media_controller = new MediaController();
                $media_controller->delete($request, $media_ids);
            }

            Media::whereUserId($id)->delete();
            Payment::whereUserId($id)->delete();
            Post::whereUserId($id)->delete();
            Reward::whereUserId($id)->delete();
            Update::whereUserId($id)->delete();
            Withdrawal_preference::whereUserId($id)->delete();
            Withdrawal_request::whereUserId($id)->delete();
            $user->delete();

            if ($request->redirect_page === 'users'){
                return redirect(route('users'))->with('success', __('app.success')  );
            }

        }

        return redirect(route('requested_account_deletion'))->with('success', __('app.success')  );
    }

}
