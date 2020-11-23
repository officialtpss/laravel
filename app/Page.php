<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'name','content','status','slug'
    ];

    public function getListAllPages()
    {
    	return Page::select('id',"name","slug")->where("status",1)->get();
    }
}
