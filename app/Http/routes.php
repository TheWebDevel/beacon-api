<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Public routes
Route::get('/info',  ['uses' => 'AuthenticationController@index', 'as' => 'api.info']);
Route::post('/auth/signup', ['uses' => 'AuthenticationController@signup', 'as' => 'auth.signup']);
Route::post('/auth/verify', ['uses' => 'AuthenticationController@verify', 'as' => 'auth.verify']);
Route::post('/auth/cookie', ['uses' => 'AuthenticationController@cookie', 'as' => 'auth.verify']);
Route::post('/auth/login', ['uses' => 'UsersController@login', 'as' => 'users.login']);
//Route::post('/fcm', ['uses' => 'GroupsController@fcm', 'as' => 'groups.guzzle']);

// Authenticated Routes
Route::group(['middleware' => ['api', 'auth.api']], function ()
{
    Route::post('/store_details', ['uses' => 'UsersController@store_details', 'as' => 'users.details']);
    Route::post('/store', ['uses' => 'FriendsController@store', 'as' => 'friends.store']);
    Route::get('/index', ['uses' => 'FriendsController@index', 'as' => 'friends.index']);
    Route::get('/location/{slug}', ['uses' => 'FriendsController@location', 'as' => 'friends.location']);
    Route::post('/allow_location/{slug}', ['uses' => 'FriendsController@allow_location', 'as' => 'friends.allow_location']);
    Route::post('/location', ['uses' => 'UsersController@store_location', 'as' => 'users.store_location']);
    Route::post('/group_store', ['uses' => 'GroupsController@group_store', 'as' => 'groups.store']);
    Route::get('/group_index/{group_slug}', ['uses' => 'GroupsController@group_index', 'as' => 'groups.index']);
    Route::post('/add_users/{group_slug}', ['uses' => 'GroupsController@add_users', 'as' => 'groups.add']);
    Route::post('/group_location/{group_slug}', ['uses' => 'GroupsController@user_isin', 'as' => 'groups.allow_location']);

});
