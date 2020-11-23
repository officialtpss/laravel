<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sms;
use App\Joke;
use App\Page;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Config;


class HomeController extends Controller
{

   /**
    * Function to get list all pages
     */ 
    public function getListPages(){
        
        $page = new Page();

        $data =  $page->getListAllPages();
        
        return response()->json(array('data'=>$data));
     }
      
   /**
    * Function to get single page by slug
     */   
      
     public function pages($slug = NULL){
        $data =  Page::select("name","content","slug","status")->where(["slug"=>$slug,"status"=>1])->first();
        if($data){
           $data->seo_tags = ["meta_title"=>$data->slug,"meta_description"=>"","meta_keywords"=>$data->name]; 
        }
       
        return response()->json(array('data'=>$data));
     }

}
