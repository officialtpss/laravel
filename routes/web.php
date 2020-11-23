<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->group(['prefix' => 'api/v1/newsms'], function () use ($router) {

    $controller = 'SmsController@getSmsByPermalink';
    $router->get('/{route:.*}/', $controller);

});


$router->group(['prefix' => 'api/v1','middleware'=>'decodeurl'], function () use ($router) {
	
    $router->get('pages/all', 'HomeController@getListPages');
    $router->get('pages/{slug}', 'HomeController@pages');

    $router->group(['prefix' => 'jokes'], function () use ($router) {
        $router->get('/{parent_slug}/{id}', 'JokesController@getJokesByParentId');
        $router->get('/latest/{lang}', 'JokesController@index');
        $router->get('{joke_id}', 'JokesController@show');
    });

});