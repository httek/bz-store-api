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

// 支付通知
$router->post(
    'event/wp/{id}',
    'PaymentController@notify'
);

$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('code2session',  'AuthController@code2session');
});

$router->group(['prefix' => 'util', 'middleware' => 'auth'], function () use ($router) {
    $router->post('upload', 'UtilController@upload');
});

$router->group(['prefix' => '/', 'middleware' => 'auth'], function () use ($router) {
    $router->get('categories', 'DefaultController@categories');
    $router->get('categories/{id}', 'DefaultController@childCategories');
    $router->get('swipers', 'DefaultController@swipers');
    $router->get('navs', 'DefaultController@navs');
    $router->get('blocks', 'DefaultController@blocks');
    $router->get('blocks/{id:[\d]+}', 'DefaultController@blockItems');

    $router->get('goods', 'GoodsController@index');
    $router->get('goods/{id:[\d]+}', 'GoodsController@show');
    $router->post('goods/{id:[\d]+}/review', 'GoodsController@review');
});


$router->group(['prefix' => 'me', 'middleware' => 'auth'], function () use ($router) {
    $router->get('profile', 'UserController@profile');
    $router->group(['prefix' => 'cart'], function () use ($router) {
        $router->get('', 'UserCartController@index');
        $router->get('calc/{id}', 'UserCartController@calc');
        $router->post('', 'UserCartController@store');
        $router->delete('{id}', 'UserCartController@destroy');
    });

    $router->group(['prefix' => 'address'], function () use ($router) {
        $router->get('', 'UserAddressController@index');
        $router->get('{id}', 'UserAddressController@show');
        $router->post('', 'UserAddressController@store');
        $router->post('{id}', 'UserAddressController@update');
        $router->delete('{id}', 'UserAddressController@destroy');
    });

    $router->group(['prefix' => 'transaction'], function () use ($router) {
        $router->get('', 'UserTransactionController@index');
        $router->get('{id:[\d]+}', 'UserTransactionController@show');
        $router->post('', 'UserTransactionController@store');
        $router->get('pre', 'UserTransactionController@prePost');
        $router->delete('{id:[\d]+}', 'UserTransactionController@destroy');
        $router->post('{id:[\d]+}', 'UserTransactionController@update');
        $router->get('{id:[\d]+}/pay', 'UserTransactionController@toPay');
        $router->get('{id:[\d]+}/review', 'UserTransactionController@review');
    });

    $router->group(['prefix' => 'favorite'], function () use ($router) {
        $router->get('', 'UserFavoriteController@index');
        $router->post('nice', 'UserFavoriteController@store');
        $router->delete('{id}', 'UserFavoriteController@destroy');
    });
});
