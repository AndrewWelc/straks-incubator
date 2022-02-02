<?php

namespace App\Http\Controllers;

use App\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class MediaController extends Controller
{

    public function loadFileManager(Request $request){
        if ( ! $request->ajax()){
            abort(404);
        }
        $user = Auth::user();
        $media_query = $user->medias();

        if ( ! empty($request->filter_name)){
            $media_query = $media_query->where("name", 'like', "%{$request->filter_name}%");
        }

        $media_query = $media_query->orderBy('id', 'desc')->paginate(30);
        $data['medias'] = $media_query;

        return view('inc.filemanager_content', $data);
    }

    /**
     * @param Request $request
     * @return array
     */

    public function store(Request $request){
        $user_id = Auth::user()->id;
        if ($request->hasFile('files')){
            $files = $request->file('files');

            try {
                foreach ($files as $file) {
                    $getFilename = $file->getClientOriginalName();
                    $ext = '.'.$file->getClientOriginalExtension();
                    $baseSlug = str_replace($ext, '', $getFilename);

                    $getMimeType = $file->getClientMimeType();
                    $getSize = $file->getClientSize();

                    $slug = strtolower($baseSlug);
                    $slug = unique_slug($slug, 'Media');
                    $slug_ext = $slug.$ext;

                    if (substr($getMimeType, 0, 5) == 'image') {
                        //It's imgae file
                        $image = $file;
                        $image_sizes = config('media.size');
                        $upload_dir = 'uploads/images/';

                        foreach ($image_sizes as $ikey => $ivalue){
                            $img_thumb_name = $upload_dir.$ikey.'/'.$slug_ext;

                            $resized = Image::make($image)->fit($ivalue[0], $ivalue[1], function ($constraint) {
                                $constraint->upsize();
                            })->stream();
                            //upload thumb image
                            current_disk()->put($img_thumb_name, $resized->__toString(), 'public');
                        }
                        current_disk()->putFileAs('uploads/images/', $file, $slug_ext, 'public');
                    }else{
                        current_disk()->putFileAs('uploads/', $file, $slug_ext, 'public');
                    }

                    $uploaded_data = [
                        'user_id'   => $user_id,
                        'name'   => $getFilename,
                        'slug'   => $slug,
                        'slug_ext'   => $slug_ext,
                        'file_size'   => $getSize,
                        'mime_type'   => $getMimeType,
                    ];
                    $is_uploaded = Media::create($uploaded_data);
                }

            }catch (\Exception $e){
                $errorMsg = $e->getMessage();
                return ['success' => false, 'msg' => $errorMsg];
            }

            return ['success' => true, 'msg' => __('app.media_uploaded')];
        }
    }


    public function delete(Request $request, $media_ids = null){
        if(config('app.is_demo'))
            return array('success' => false, 'msg' => __('app.feature_disable_demo'));

        if ( ! $media_ids){
            $media_ids = $request->media_ids;
        }
        if ( ! empty($media_ids)){

            if ( ! is_array($media_ids)){
                $media_ids = explode(',', $media_ids);
            }

            if ( is_array($media_ids)){
                try{

                    foreach ($media_ids as $media_id){
                        $media = Media::find($media_id);
                        if ($media){
                            $media_name = $media->slug_ext;

                            //Deleting from database
                            $media->delete();

                            //Deleting from storage
                            $storage = current_disk();
                            if (substr($media->mime_type, 0, 5) == 'image') {
                                $image_sizes = config('media.size');

                                //Get all image size and delete form them
                                foreach ($image_sizes as $ikey => $ivalue){
                                    $media_path = "uploads/images/{$ikey}/{$media_name}";

                                    if ($storage->has($media_path)){
                                        $storage->delete($media_path);
                                    }
                                }

                                //Delete original file
                                $media_path = "uploads/images/{$media_name}";
                                if ($storage->has($media_path)){
                                    $storage->delete($media_path);
                                }
                            }

                            //deleting any other file
                            $media_path = "uploads/{$media_name}";
                            if ($storage->has($media_path)){
                                $storage->delete($media_path);
                            }
                        }

                    }
                }catch (\Exception $e){
                    return array('success' => false, 'msg' => $e->getMessage());
                }
            }
        }
        return array('success' => true, 'msg' => __('app.media_deleted'));
    }


}
