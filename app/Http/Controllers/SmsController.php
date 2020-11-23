<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sms;
use App\Category;
use App\MenuItem;

class SmsController extends Controller
{

     /*
        This function is used to get the lastest sms on home page by language.
    */

     public function getLatestSms($language = '')
     {
        $language = strtolower($language);
        $smsobj = new Sms();
        $category = new Category();
        $menuobj = new MenuItem();
        $sms  = $smsobj->getLatestSmsByLanguage($language);
        if(!empty($sms['data']))
        {
            $sms['data'] = $this->getUpdatedBreadcrumbsWithFavCountAndImages($sms['data'],'sms');
            $sms['seo_tags'] = $category->getSeoDataByCategoryId($id = '');
            $featured_categories =  $menuobj->getfeaturedCategories($language,'sms');
            $sms['featured_categories'] = $featured_categories; 
        }
        return $sms;
     }        

      /*
       This function is used to get the sms by id.
    */
     public function show($sms_id = "")
     {
        $sms = new Sms();
        $category = new Category();
        $data = $sms->getSmsById($sms_id);
        if(!empty($data)){
            $data['fav_count'] = count($data['favourites']);
            $data['seo_tags'] = $category->getMetaTagsforSinglePage($data);
            $data['breadcrumbs'] = $category->getBreadCrumbsforSinglePage($sms_id,'sms');
            if(!empty($data['breadcrumbs'])){
            	$data['mBreadcrumbs'] = $this->mobileApiUpdatedBreadcrumbs($data['breadcrumbs'],$data['categories']);
            }
            $data = $this->updateSingleImageUrl($data,'sms');
        }
        return response()->json($data);
     }  

    /*
        This function is used to get the sms data by permalink.
    */
  
    public function getSmsByPermalink(Request $request, $route)
     {
        $route = urldecode($route);
        $sms = new Sms();
        $category = new Category();
        $menuobj = new MenuItem();
        $data = $sms->getSmsByPermalink($route);
        $lang = $request->input('language');
        $type = 'sms';
        if(empty($data['data'])) 
        {
            $xplo = explode('/',$route);
            $route_count = count($xplo);
            if($route_count == 3)
            {
                $slug = $xplo[0].'/'.$xplo[1];
                $id = $xplo[2];
                $data = $sms->getSmsId($slug,$id);
                $data['data'] = $this->getUpdatedBreadcrumbsWithFavCountAndImages($data['data'],'sms'); 
            }else if($route_count == 2) {
                $category_data = $category->getCategoryIdBySlug($route);
                 if(!empty($category_data))
                    {
                        $category_id = $category_data['id'];
                        $data = $sms->getSmsByParentId($route,$category_id);
                        $data['data'] = $this->getUpdatedBreadcrumbsWithFavCountAndImages($data['data'],'sms'); 
                    }
            }else if($route_count == 1) {
                $parent_slug = $xplo[0];
                if($parent_slug == 'english' || $parent_slug == 'hindi')
                {

                    $data = $this->getLatestSms($parent_slug);
                }else{
                    $check_count = $category->checkSubcatBySlug($parent_slug);
                    if($check_count > 0)
                    {
                        $data = $sms->getLatestSmsBySubcat($parent_slug,$lang,'universal');
                        $data['data'] = $this->getUpdatedBreadcrumbsWithFavCountAndImages($data['data'],'sms'); 
                        $type = 'subcat';
                    }else{
                        $parent_data = $category->getParentIdBySlug($parent_slug);
                        if(!empty($parent_data))
                        {
                            $parent_id = $parent_data['parent_id'];
                            $data = $sms->getSmsByParentId($parent_id,$parent_id);
                            $data['data'] = $this->getUpdatedBreadcrumbsWithFavCountAndImages($data['data'],'sms'); 
                             if (!$data['data']) {
                                $data['data'] = $this->getLatestSms($parent_slug);
                             } 
                        }
                    }
                   
                }
                     
            }
        }else{
            $data['data'] = $this->getUpdatedBreadcrumbsWithFavCountAndImages($data['data'],'sms');
        } 

        $data['seo_tags'] = $category->getSeoDataByCategorySlug($route);
        $data['breadcrumbs'] = $category->getBreadCrumbsforCategoryPageBySlug($route,$type);
        $data['isRestricted'] = $category->isRestricted($route);
        return response()->json($data);
     }

}
