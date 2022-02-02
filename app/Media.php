<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $guarded = [];
    protected $table = 'media';


    public function media_type(){
        $type = explode('/', $this->mime_type);
        return isset($type[0]) ? $type[0] : $this->mime_type;
    }

    public function media_icon_url(){
        $ext = str_replace($this->slug.'.', '', $this->slug_ext);
        if (file_exists("./assets/images/ico/{$ext}.jpg")){
            return asset("assets/images/ico/{$ext}.jpg");
        }
        return asset("assets/images/ico/default.jpg");
    }

}
