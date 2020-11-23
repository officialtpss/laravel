<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Config;
use Elasticsearch\ClientBuilder;
use App\Category;

class Controller extends BaseController
{

    /* 
        To check Image exists on server.
    */
     public function isImageExists($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($code == 200) {
            $status = true;
        } else {
            $status = false;
        }
        curl_close($ch);
        return $status;
    }

    /* 
        To update images url of sms,memes and jokes.
    */

    public function updateImageUrls($data,$type)
    {
        if(!empty($data)) {
             if($type == 'sms')
             {
                $img_url =  Config::get('global.sms_image_url');
             }else if($type == 'memes' || $type == 'jokes' ) {
                $img_url =  Config::get('global.jokes_image_url');
             } 

                
             $default_dark_sms_image =  Config::get('global.default_dark_sms_image');

             $arr = [];
            foreach($data as $key => $value)
            {   
                if(isset($value['image'])) {
                    if($value['image_url'] == '' && $value['image'] != '')
                    {
                        $value['image'] = $img_url.$value['image'];
                    }else if($value['image'] != '' && $value['image_url'] != ''){
                        $value['image'] = $value['image_url'];
                     }else if($value['image'] == '' && ($value['image_url'] == Null || $value['image_url'] == '')){
                         $value['image'] = $default_dark_sms_image;
                     }
                     unset($value['image_url']);
                 }if(!isset($value['image']) && !isset($value['image_url'])) {
                    $value['image'] = $default_dark_sms_image;
                 }
                $value['fav_count'] = count($value['favourites']);
                array_push($arr,$value);
            }
             return $arr;
         } 
     }

     /* 
        To update images url of single sms,memes and jokes.
    */

     public function updateSingleImageUrl($data,$type)
     {
        if(!empty($data))
        {
            if($type == 'sms')
             {
                $img_url =  Config::get('global.sms_image_url');
             }else if($type == 'memes' || $type == 'jokes' ) {
                $img_url =  Config::get('global.jokes_image_url');
             }

             $default_dark_sms_image =  Config::get('global.default_dark_sms_image');
             
            if($data->image_url == '' && $data->image != '')
             {
                $data->image = $img_url.$data->image;
            }else if($data->image != '' && $data->image_url != ''){
                $data->image = $data->image_url;
             }else if($data->image == '' && $data->image_url == Null){
                 $data->image = $default_dark_sms_image;
             }
            unset($data->image_url);
        }
        return $data;
     }

     /* 
        To update mobile api breadcrumbs.
    */

     public function mobileApiBreadcrumbs($breadcrumbs)
     {
        $breadcrumbs_arr = [];
        $category = new Category();
        $i = 1;
        foreach($breadcrumbs as $cat)
          { 
             if($i == 1) 
             {
                 $len = count($category->createBreadcrumbs($cat)['data'])-1;
                 foreach($category->createBreadcrumbs($cat)['data'] as $key2 => $val)
                  {

                    $type = $val['link'];
                    if(strtolower($val['label']) == 'english')
                    {
                      unset($val[$key2]);
                    }else{
                      $val['label'] = ucwords($val['label']);  
                      $val['link'] =  str_replace($type.'/', '', $val['mlink']);
                      if($key2 == $len)
                      {
                        $val['link'] =  $category->getCategorySlugById($cat['category_id']);
                      }
                      unset($val['mlink']);
                      array_push($breadcrumbs_arr, $val);
                    }

                  }
             }
            $i++;
          }
          return $breadcrumbs_arr;
     }

     /* 
        To update mobile api breadcrumbs.
    */

     public function mobileApiUpdatedBreadcrumbs($breadcrumbs,$category_data)
     {
        if(!empty($category_data))
        {
            $category_slug = $category_data[0]['slug'];
        }

        $breadcrumbs_arr = [];
        $category = new Category();
            $len = count($breadcrumbs['data'])-1; 
             foreach($breadcrumbs['data'] as $key2 => $val)
              {
                $type = $val['link'];
                if(strtolower($val['label']) == 'english' || strtolower($val['label']) == 'hindi')
                {
                  unset($val[$key2]);
                }else{
                  $val['label'] = ucwords($val['label']);  
                  if($key2 == $len && $category_slug != '')
                  {
                    $val['link'] =  $category_slug;
                  }else{
                    $val['link'] =  str_replace($type.'/', '', $val['mlink']);
                  }
                  unset($val['mlink']);
                  array_push($breadcrumbs_arr, $val);
                }
              }  
          return $breadcrumbs_arr;
     }

     public function getUpdatedBreadcrumbsWithFavCountAndImages($data,$type)
     {
        $arr = [];
        $category = new Category(); 
        if(!empty($data)) {
            
            foreach($data as $key => $value)
            {   
                $value['breadcrumbs'] = $category->getBreadCrumbsforSinglePage($value['id'],$type);
                $value['fav_count'] = count($value['favourites']);
                $value['mBreadcrumbs'] = $this->mobileApiUpdatedBreadcrumbs($value['breadcrumbs'],$value['categories']);
                array_push($arr, $value);
            }

            if($type == 'jokes')
            {
                return $arr; 
            }else{
                $data = $this->updateImageUrls($arr,$type);  
                return $data; 
            }
        }
        return $data;     
     }
     
    public function getElasticConnection()
    {
        try {
            $hosts = [
                    [
                      'host' => env('ELASTICSEARCH_HOST'),
                      'port' => env('ELASTICSEARCH_PORT'),
                      'scheme' => env('ELASTICSEARCH_SCHEME'),
                      'path' => '/',
                      'user' => env('ELASTICSEARCH_USER'),
                      'pass' => env('ELASTICSEARCH_PASS'),
                    ]
                ];

            $client = ClientBuilder::create()
            ->setHosts($hosts)
            ->setRetries(2)
            ->build();

            return $client;
        } catch (Exceptions $e) {
            echo $e->getMessage();
        }
     }
}