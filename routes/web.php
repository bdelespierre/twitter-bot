<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/schedule', function () {
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }

    Artisan::call('schedule:run');
});

Route::get('/users', [
    'as'   => 'users.index',
    'uses' => 'UserController@index'
]);

Route::get('/users/{user}', [
    'as'   => 'users.view',
    'uses' => 'UserController@view'
]);

Route::get('/buffer', [
    'as'   => 'buffer.index',
    'uses' => 'BufferController@index'
]);

Route::post('/buffer', [
    'as'   => 'buffer.add',
    'uses' => 'BufferController@add'
]);

Route::get('/buffer/pixel', [
    'as'   => 'buffer.pixel',
    'uses' => 'BufferController@pixel'
]);

Route::get('/buffer/bookmarklet', [
    'as'   => 'buffer.bookmarklet',
    'uses' => 'BufferController@bookmarklet'
]);

Route::get('/buffer/{item}', [
    'as'   => 'buffer.view',
    'uses' => 'BufferController@view'
]);

Route::get('/buffer/{item}/refresh', [
    'as'   => 'buffer.refresh',
    'uses' => 'BufferController@refresh'
]);

Route::get('/buffer/{item}/tweet', [
    'as'   => 'buffer.tweet',
    'uses' => 'BufferController@tweet'
]);

Route::get('/buffer/{item}/delete', [
    'as'   => 'buffer.delete',
    'uses' => 'BufferController@delete'
]);

Route::get('/pool', [
    'as'   => 'pool.index',
    'uses' => 'PoolController@index',
]);

Route::get('/pool/{item}/accept', [
    'as' => 'pool.accept',
    'uses' => 'PoolController@accept',
]);

Route::get('/pool/{item}/reject', [
    'as' => 'pool.reject',
    'uses' => 'PoolController@reject',
]);

Auth::routes();
