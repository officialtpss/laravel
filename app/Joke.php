<?php 
namespace App;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Category;
 
class Joke extends Model
{ 
   use SoftDeletes;
  
  protected $casts = [
    'updated_at' => 'datetime:Y-m-d',
    'lastmod' => 'datetime:Y-m-d',
  ];

   /*
      To get the lastest jokes by language like Hindi or English.
   */
   public function getLatestJokes($language)
   {
      if($language == 'hindi'){
        $language = 'hi';
      }else if($language == 'hinglish'){
        $language = 'he';
      }else{
        $language = 'en';
      }
   		return Joke::with(['categories:category_id,name,slug,parent_id,breadcrumb','favourites:id,device_id,item_id','tags:tag_id,name,slug'])->select('jokes.id','jokes.title','jokes.content','jokes.language','jokes.image')
        ->join('category_joke', 'jokes.id', '=', 'category_joke.joke_id')
        ->join('categories', 'categories.id', '=', 'category_joke.category_id')  
        ->where(['jokes.language'=>$language,'jokes.status'=>1,'categories.status'=>1])
        ->whereNotIn('categories.viewing_prefrence',['restricted','non-veg'])
        ->orderBy('jokes.id', 'DESC')
        ->groupBy('jokes.id')
        ->paginate(18)
        ->toArray();
   }

   /*
      To show the relation jokes belongs to multiple categories.
   */

   public function categories()
   {
   		return $this->belongsToMany('App\Category');

   }

   /*
        To show the belonging relation with Favourite.
   */
   public function favourites()
   {
      return $this->hasMany('App\Favourite','item_id');

   }

   /*
        To show the belonging relation with tags.
   */
   public function tags()
   {
      return $this->belongsToMany('App\Tag');
   }

   /*
      To get the jokes data by category id and slug.
   */

   public function getJokesByCategoryId($slug,$id)
   {
      $jokes =  Joke::with(['categories:category_id,name,slug,parent_id,breadcrumb','favourites:id,device_id,item_id','tags:tag_id,name,slug'])->select('jokes.id','jokes.title','jokes.language','jokes.author_id','jokes.content','jokes.image')->leftjoin('category_joke', 'jokes.id', '=', 'category_joke.joke_id')
          ->join('categories', 'categories.id', '=', 'category_joke.category_id')
          ->where(['categories.old_cat_id'=>$id,'categories.type'=>'jokes','jokes.status'=>1,'categories.status'=>1])
           /* ->when($slug, function($query) use ($slug){
                  return $query->where('categories.slug',$slug);
              })*/
          ->orderBy('jokes.id', 'DESC')
          ->paginate(18)->toArray();
       return $jokes;   
   }
   /*
      To get the jokes data by parent id and slug.
   */

   public function getJokesByParentId($slug,$id)
   {  
      $jokes =  Joke::with(['categories:category_id,name,slug,parent_id,breadcrumb','favourites:id,device_id,item_id','tags:tag_id,name,slug'])->select('jokes.id','jokes.title','jokes.language','jokes.author_id','jokes.content','jokes.image')->leftjoin('category_joke', 'jokes.id', '=', 'category_joke.joke_id')
          ->join('categories', 'categories.id', '=', 'category_joke.category_id')
          ->where(['categories.parent_id'=>$id,'categories.type'=>'jokes','jokes.status'=>1,'categories.status'=>1])
          ->groupBy('jokes.id')
          ->orderBy('jokes.id', 'DESC')
          ->paginate(18)->toArray();
      
       return $jokes;   
   }

    /*
      To get the jokes data by parent slug.
   */
   public function getJokesByParentSlug($slug)
   {
      $category = new Category();
      $parent_id = $category->getParentIdBySlug($slug);
      $jokes =  Joke::with('categories:category_id,name,slug,parent_id,breadcrumb')->select('jokes.id','jokes.title','jokes.language','jokes.author_id','jokes.content','jokes.image')->leftjoin('category_joke', 'jokes.id', '=', 'category_joke.joke_id')
          ->join('categories', 'categories.id', '=', 'category_joke.category_id')
          ->where(['categories.parent_id'=>$parent_id,'categories.type'=>'jokes','jokes.status'=>1,'categories.status'=>1])
          ->orderBy('jokes.id', 'DESC')
          ->paginate(18)->toArray();
       return $jokes;   
   }

    /*
      To get the parent id by slug.
   */

   public function getParentIDBySlug($slug){
    return Category::select('parent_id')->where('slug',$slug)->first()['parent_id'];
   }

    /*
      To get the jokes data by joke id.
   */

   public function getJokesId($joke_id)
   {

   		$joke =  Joke::with(['categories:category_id,name,slug,parent_id,breadcrumb','favourites:id,device_id,item_id','tags:tag_id,name,slug'])
          ->select('jokes.id','jokes.title','jokes.language','jokes.content','jokes.image','jokes.old_joke_id','jokes.meta_title','jokes.meta_description','jokes.meta_keywords')
          ->join('category_joke', 'jokes.id', '=', 'category_joke.joke_id')
          ->leftjoin('categories', 'categories.id', '=', 'category_joke.category_id')
          ->where('jokes.id',$joke_id)
          ->first();
       return $joke;
   }

  /*
        To get the sms by category id and slug.
   */

   public function getJokesByPermalink($slug)
   {
      return Joke::with(['categories:category_id,name,slug,parent_id,breadcrumb','favourites:id,device_id,item_id','tags:tag_id,name,slug'])->select('jokes.id','jokes.title','jokes.language','jokes.author_id','jokes.content','jokes.image')
      ->join('category_joke', 'jokes.id', '=', 'category_joke.joke_id')
      ->leftjoin('categories', 'categories.id', '=', 'category_joke.category_id')
      ->leftjoin('authors', 'jokes.author_id', '=', 'authors.id')  
     ->where(['categories.type'=>'jokes','categories.slug'=>$slug,'jokes.status'=>1,'categories.status'=>1])
      ->orderBy('jokes.id', 'DESC')
      ->paginate(18)->toArray();
   }
    
  /*
        To get latest jokes for mobile app home.
   */ 
   public function getLatestJokesByLanguageForHome($language,$limit,$skip)
   {
      if($language == 'hindi'){
        $language = 'hi';
      }else if($language == 'hinglish'){
        $language = 'he';
      }else{
        $language = 'en';
      }

      return Joke::with(['categories:category_id,name,slug,parent_id,breadcrumb','favourites:id,device_id,item_id','tags:tag_id,name,slug'])->select('jokes.id','jokes.title','jokes.content','jokes.image','jokes.created_at')
        ->join('category_joke', 'jokes.id', '=', 'category_joke.joke_id')
        ->join('categories', 'categories.id', '=', 'category_joke.category_id')  
        ->where(['jokes.language'=>$language,'jokes.status'=>1,'categories.status'=>1])
        ->whereNotIn('categories.viewing_prefrence',['restricted','non-veg'])
        ->orderBy('id', 'DESC')->skip($skip)
        ->take($limit)
        ->groupBy('jokes.id')
        ->get()->toArray();
   }

   public function getJokesCount()
   {
      return Joke::where('jokes.status',1)->count();
   }

}
