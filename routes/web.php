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

$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('code2session',  'AuthController@code2session');
});

$router->group(['prefix' => '/'], function () use ($router) {
    $router->get('categories', 'DefaultController@categories');
    $router->get('swipers', 'DefaultController@swipers');
    $router->get('navs', 'DefaultController@navs');
    $router->get('blocks', 'DefaultController@blocks');
});
