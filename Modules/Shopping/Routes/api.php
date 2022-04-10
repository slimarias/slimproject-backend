<?php
use Illuminate\Routing\Router;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$router->group(['prefix' => '/products'], function (Router $router) {
    //get many items
    $router->get('/', [
        'as' =>'api.shopping.products.index',
        'uses' => 'ProductController@index',
    ]);
    //add one item
    $router->post('/', [
        'as' =>'api.shopping.products.create',
        'uses' => 'ProductController@create',
        'middleware' => ['auth:api'],
    ]);
    //get one item
    $router->get('/{criteria}', [
        'as' => 'api.shopping.products.show',
        'uses' => 'ProductController@show',
    ]);
    //edit one item
    $router->put('/{criteria}', [
        'as' => 'api.shopping.products.update',
        'uses' => 'ProductController@update',
        'middleware' => ['auth:api'],
    ]);
    //delete one item
    $router->delete('/{criteria}', [
        'as' => 'api.shopping.products.delete',
        'uses' => 'ProductController@delete',
        'middleware' => ['auth:api'],
    ]);
});

