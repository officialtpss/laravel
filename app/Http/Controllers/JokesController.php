<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Joke;
use App\Http\Controllers\MenuController;

class JokesController extends Controller
{

	/*
		This function is used to get the lastet Jokes on home page.
	*/

     public function index($lang = ""){

 	    $joke = new Joke();	
        $category = new Category(); 
        $menuobj = new MenuItem(); 
        $lang = strtolower($lang); 
        
        $data  = $joke->getLatestJokes($lang);
        $arr = [];
        foreach($data['data'] as $key => $value)
        {   
            $value['fav_count'] = count($value['favourites']);
            $value['breadcrumbs'] = $category->getBreadCrumbsforSinglePage($value['id'],'jokes');
            $value['mBreadcrumbs'] = $this->mobileApiUpdatedBreadcrumbs($value['breadcrumbs'],$value['categories']);
            array_push($arr,$value);
        }
        $data['data'] = $arr;
        
        $data['seo_tags'] = $category->getSeoDataByCategoryId($id = '');

        $jokesmenus = $menuobj->getJokesSidebarStaticMenus($lang);
        $featured_categories =  $menuobj->getfeaturedCategories($lang,'jokes');
        $data['featured_categories'] = $featured_categories; 

        return $data;
 
     } 

    /*
        This function is used to get the jokes related to specific cateogry.
    */
    
     public function getJokesByCategoryId($slug1 = "",$slug2 = "" ,$id = "")
     {
        $slug = $slug1.'/'.$slug2;
        $joke = new Joke();

        $data = $joke->getJokesByCategoryId($slug,$id);
        $arr = [];
        foreach($data['data'] as $key => $value)
        {   
            $value['fav_count'] = count($value['favourites']);
            array_push($arr,$value);
        }
        $data['data'] = $arr;
        $category = new Category();
        $data['seo_tags'] = $category->getSeoDataByCategoryId($id);
        $data['breadcrumbs'] = $category->getBreadCrumbsforCategoryPage($id,'jokes');
        return $data;
     } 

     /*
        This function is used to get jokes by parent slug and id.
    */

     public function getJokesByParentId($parent_slug = "",$id = "")
     {
        $joke = new Joke();
        $menuobj = new MenuItem();
        if($parent_slug == 'latest')
        {
             $data = $this->index($id);
        }else if($parent_slug == 'sidebarmenus') {
            $data = $menuobj->getJokesSidebarStaticMenus($id);

        } else{
            $data = $joke->getJokesByParentId($parent_slug,$id);

            $category = new Category();
            $data['seo_tags'] = $category->getSeoDataByCategoryId($id);
            $data['breadcrumbs'] = $category->getBreadCrumbsforCategoryPage($id,'jokes');
            $data['mBreadcrumbs'] = $this->mobileApiUpdatedBreadcrumbs($data['breadcrumbs'],$data['categories']);
        }
        
        return response()->json($data);
     }

     /*
		This function is used to get the joke by id.
	*/
  
     public function show($joke_id = "")
     {
        $joke = new Joke();
        $category = new Category();
        $data = $joke->getJokesId($joke_id);
        if($data){
            $data['fav_count'] = count($data['favourites']);
            $data['seo_tags'] = $category->getMetaTagsforSinglePage($data);
            $data['breadcrumbs'] = $category->getBreadCrumbsforSinglePage($joke_id,'jokes');
            $data['mBreadcrumbs'] = $this->mobileApiUpdatedBreadcrumbs($data['breadcrumbs'],$data['categories']);
        }
        
        return response()->json($data);
     }

}
