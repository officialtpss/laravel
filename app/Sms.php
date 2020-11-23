<?php 
namespace App;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Category;
 
class Sms extends Model
{
   use SoftDeletes;

  protected $casts = [
    'updated_at' => 'datetime:Y-m-d',
    'lastmod' => 'datetime:Y-m-d',
  ];

  protected $hidden = array('pivot');
   /*
        To show the belonging relation with categories.
   */
   public function categories()
   {
      return $this->belongsToMany('App\Category');

   }

  /*
        To show the belonging relation with categories.
   */
   public function children()
   {
      return $this->belongsToMany('App\Category')->with('children');

   }


   /*
        To show the belonging relation with categories.
   */
   public function favourites()
   {
      return $this->hasMany('App\Favourite','item_id');

   }

   /*
        To show the belonging relation with categories.
   */
   public function tags()
   {
      return $this->belongsToMany('App\Tag');
   }

   /*
        To get the latest sms by Language.
  */
   public function getLatestSmsByLanguage($language)
   {
     
      return Sms::with(['categories:category_id,name,slug,parent_id','favourites:id,device_id,item_id','tags:tag_id,name,slug'])->select('sms.id','sms.content','sms.image','sms.image_url','authors.name as author_name')
          ->join('category_sms', 'sms.id', '=', 'category_sms.sms_id')
          ->leftjoin('categories', 'categories.id', '=', 'category_sms.category_id')  
          ->leftjoin('authors', 'sms.author_id', '=', 'authors.id')            
          ->whereIn('categories.viewing_prefrence',['veg','universal'])
          ->where(['categories.lang'=>$language,'sms.status'=>1,'categories.status'=>1])
          //->where('sms.language',$lang)
          ->groupBy('sms.id')
          ->orderBy('sms.id','DESC')
          //->get();
          ->paginate(18)->toArray();
   }

   /*
        To get the sms by category id and slug.
   */

   public function getSmsId($slug,$id)
   {
      return Sms::with(['categories:category_id,name,slug,parent_id,breadcrumb','favourites:id,device_id,item_id','tags:tag_id,name,slug'])->select('sms.id','sms.content','sms.language','sms.image','sms.image_url','sms.viewing_prefrence','authors.name as author_name','categories.name','categories.slug as cat_slug')
          ->join('category_sms', 'sms.id', '=', 'category_sms.sms_id')
          ->leftjoin('categories', 'categories.id', '=', 'category_sms.category_id')  
          ->leftjoin('authors', 'sms.author_id', '=', 'authors.id')  
          ->where(['categories.old_cat_id'=>$id,'categories.type'=>'sms'])
          ->orwhere('categories.slug',$slug)
          ->where(['sms.status'=>1,'categories.status'=>1])    
          ->orderBy('sms.id', 'DESC') 
          ->paginate(18)->toArray();
   }

    /*
        To get the sms by sms id.
   */
   public function getSmsById($sms_id)
   {

      $joke =  Sms::with(['categories:category_id,name,slug,parent_id,breadcrumb','favourites:id,device_id,item_id','tags:tag_id,name,slug'])->select('sms.id','sms.title','sms.language','sms.content','sms.image','sms.image_url','sms.meta_title','sms.meta_description','sms.meta_keywords','authors.name as author_name')
          ->leftjoin('category_sms', 'sms.id', '=', 'category_sms.sms_id')
          ->join('categories', 'categories.id', '=', 'category_sms.category_id')
          ->leftjoin('authors', 'sms.author_id', '=', 'authors.id') 
          ->where(['categories.type'=>'sms','sms.id'=>$sms_id])
          ->first();
       return $joke;
   }


    /*
      To get the sms data by parent id and slug.
   */

   public function getSmsByParentId($slug,$id)
   {
      $sms =  Sms::with('categories:category_id,name,slug,parent_id,breadcrumb','favourites:id,device_id,item_id','tags:tag_id,name,slug')->select('sms.id','sms.title','sms.language','sms.author_id','sms.content','sms.image','sms.image_url')->leftjoin('category_sms', 'sms.id', '=', 'category_sms.sms_id')
          ->join('categories', 'categories.id', '=', 'category_sms.category_id')
          ->where(['categories.parent_id'=>$id,'categories.type'=>'sms'])
          ->where(['sms.status'=>1,'categories.status'=>1])  
          ->groupBy('sms.id')    
          ->orderBy('sms.id', 'DESC') 
          ->paginate(18)->toArray();
       return $sms;   
   }
 

/*
      To get all the sms images by year month cat_id.
   */  
  public function getAllSmsImages($year)
  {
    $data = Sms::select('image','image_url')->whereYear('created_at', '=', $year)->where("image","!=","")->orderBy('id','DESC')->get();
    $images_arr = [];
    foreach($data as $key => $value)
    {
      if($value->image_url != null && $value->image != '')
      {
        $value->image = $value->image_url;
      }else if($value->image != ''){
        $value->image = 'https://media.santabanta.com/images/picsms/'.$value->image; 
      }
      unset($value->image_url);
      if($value->image == '')
      {
         unset($data[$key]);
      }else{
         $images_arr[] = $value->image;
      }
      
    }
    return $images_arr;
  }
  
/*
      To get all the sms by year month cat_id.
   */
  public function getAllSms($year)
  {
    $data = Sms::select('id','title','updated_at as lastmod')->whereYear('created_at', '=', $year)->orderBy('id','DESC')->get();
    foreach ($data as $key => $value) {
      $value->url = "sms/".$value->id;
      $value->changefreq = 'monthly';
      $value->priority = 0.8;
    }
    return $data;
  }


  /*
        To get the sms by category id and slug.
   */

   public function getSmsByPermalink($slug)
   {
      return Sms::with(['categories:category_id,name,slug,parent_id,breadcrumb','favourites:id,device_id,item_id','tags:tag_id,name,slug'])->select('sms.id','sms.content','sms.language','sms.image','sms.image_url','sms.viewing_prefrence','authors.name as author_name','categories.name','categories.slug as cat_slug')
          ->join('category_sms', 'sms.id', '=', 'category_sms.sms_id')
          ->leftjoin('categories', 'categories.id', '=', 'category_sms.category_id')  
          ->leftjoin('authors', 'sms.author_id', '=', 'authors.id')  
          ->where(['categories.type'=>'sms','categories.slug'=>$slug,'sms.status'=>1,'categories.status'=>1])
          ->orderBy('sms.id', 'DESC') 
          ->paginate(18)->toArray();
   }

   /*
        To get latest sms for mobile app home.
   */ 

    public function getLatestSmsByLanguageForHome($language,$limit,$skip)
    {
      $category = new Category();
      $sms_parent_names =  $category->getParentByLang($language);
      $sms_parent_ids = $category->getParentIdByName($sms_parent_names);
      
      return Sms::with(['categories:category_id,name,slug,parent_id,breadcrumb','favourites:id,device_id,item_id','tags:tag_id,name,slug'])->select('sms.id','sms.content','sms.image','sms.image_url','authors.name as author_name','sms.created_at')
          ->join('category_sms', 'sms.id', '=', 'category_sms.sms_id')
          ->leftjoin('categories', 'categories.id', '=', 'category_sms.category_id')  
          ->leftjoin('authors', 'sms.author_id', '=', 'authors.id')            
          ->where(function($query) use ($language,$sms_parent_ids) {
                return $query->where('categories.lang',$language)->whereNotIn('categories.viewing_prefrence',['restricted','non-veg']);
            })
          ->where(['sms.status'=>1,'categories.status'=>1])
          ->groupBy('sms.id')
          ->orderBy('sms.id', 'DESC')
          ->skip($skip)
          ->take($limit)
          ->get()->toArray();
   }

}
