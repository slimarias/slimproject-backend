<?php

use Illuminate\Http\Request;
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

$router->group(['prefix' => '/auth'], function (Router $router) {

      /** @var Router $router */
      $router->post('login', [
        'as' => 'api.auth.login',
        'uses' => 'AuthController@login',
      ]);

      /** @var Router $router */
      $router->post('reset', [
        'as' => 'api.auth.reset',
        'uses' => 'AuthController@reset',
        'middleware' => ['captcha']
      ]);
      /** @var Router $router */
      $router->post('reset-complete', [
        'as' => 'api.auth.reset-complete',
        'uses' => 'AuthController@resetComplete',
      ]);
      /** @var Router $router */
      $router->get('me', [
        'as' => 'api.auth.me',
        'uses' => 'AuthController@me',
        'middleware' => ['auth:api']
      ]);

      /** @var Router $router */
      $router->get('logout', [
        'as' => 'api.auth.logout',
        'uses' => 'AuthController@logout',
      ]);

      /** @var Router $router */
      $router->get('logout-all', [
        'as' => 'api.auth.logout.all',
        'uses' => 'AuthController@logoutAllSessions',
      ]);

      /** @var Router $router */
      $router->get('must-change-password', [
        'as' => 'api.auth.me.must.change.password',
        'uses' => 'AuthController@mustChangePassword',
        'middleware' => ['auth:api']
      ]);

      /** @var Router $router */
      $router->get('impersonate', [
        'as' => 'api.auth.impersonate',
        'uses' => 'AuthController@impersonate',
      ]);

      /** @var Router $router */
      $router->get('refresh-token', [
        'as' => 'api.auth.refresh.token',
        'uses' => 'AuthController@refreshToken',
        'middleware' => ['auth:api']
      ]);

      #==================================================== Social
      $router->post('social/{provider}', [
        'as' => 'api.auth.social.auth',
        'uses' => 'AuthController@getSocialAuth'
      ]);

      $router->get('social/callback/{provider}', [
        'as' =>  'api.auth.social.callback',
        'uses' => 'AuthController@getSocialAuthCallback'
      ]);
});

$router->group(['prefix' => '/users'], function (Router $router) {
    $router->post('/register', [
        'as' => 'api.user.users.register',
        'uses' => 'UserController@register',
        'middleware' => ['captcha']
      ]);
      $router->post('/', [
        'as' => 'api.user.users.create',
        'uses' => 'UserController@create',
        'middleware' => ['auth:api']
      ]);
      $router->get('/', [
        'as' => 'api.user.users.index',
        'uses' => 'UserController@index',
        'middleware' => ['auth:api']
      ]);
      $router->put('change-password', [
        'as' => 'api.iprofile.change.password',
        'uses' => 'UserController@changePassword',
        //'middleware' => ['auth:api']
      ]);
      $router->get('/birthday', [
        'as' => 'api.user.users.birthday',
        'uses' => 'UserController@usersBirthday',
        'middleware' => ['auth:api']
      ]);
      $router->put('/{criteria}', [
        'as' => 'api.user.users.update',
        'uses' => 'UserController@update',
        'middleware' => ['auth:api']
      ]);
      $router->delete('/{criteria}', [
        'as' => 'api.user.users.delete',
        'uses' => 'UserController@delete',
        'middleware' => ['auth:api']
      ]);
      $router->get('/{criteria}', [
        'as' => 'api.user.users.show',
        'uses' => 'UserController@show',
        'middleware' => ['auth:api']
      ]);
      $router->post('/media/upload', [
        'as' => 'api.profile.users.media.upload',
        'uses' => 'UserController@mediaUpload',
        'middleware' => ['auth:api']
      ]);
      $router->post('/media/delete', [
        'as' => 'api.profile.users.media.delete',
        'uses' => 'UserController@mediaDelete',
        'middleware' => ['auth:api']
      ]);
});
