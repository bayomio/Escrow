<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::namespace('Api')->group(function () {
    Route::post('auth/login', 'AuthController@login');
    Route::get('auth/confirmAccount/{token}', 'AuthController@confirmAccount')->name("confirm");

    Route::group([
        'prefix' => 'password'
    ], function() {

        Route::post('create', 'PasswordResetController@create');
        Route::post('reset', 'PasswordResetController@reset');
    });


    Route::post('auth/register', 'AuthController@register');
    Route::group(['middleware' => 'auth:api'], function () {

        Route::group(['prefix' => 'profile'], function () {
            Route::get('index', 'ProfileController@index');
            Route::get('getAllUsers', 'ProfileController@getAllUsers');
            Route::get('search', 'ProfileController@search');

            Route::put('update', 'ProfileController@userProfile');
            Route::post('adminStatus', 'ProfileController@setAdminStatus');
            Route::post('notificationStatus', 'ProfileController@setNotificationStatus');
            Route::post('userStatus', 'ProfileController@setUserStatus');
        });

        Route::group(['prefix' => 'setting'], function () {
            Route::get('index', 'SettingController@index');
            Route::put('update', 'SettingController@update');
        });


        Route::post('event/score', 'EventController@setEventScore');
        Route::get('event/vote/score/{event_id}', 'EventController@getEventVoteScore');
        Route::post('event/vote', 'EventController@eventVote');
        Route::resource('event', 'EventController');

        Route::group(['prefix' => 'payment'], function () {
            Route::get('banks', 'PaymentController@allBanks');
            Route::get('balance', 'PaymentController@balance');
            Route::post('deposit', 'PaymentController@depositFund');
            Route::post('withdraw', 'PaymentController@withdrawFund');
            Route::post('approveWithdrawFund', 'PaymentController@approveWithdrawFund');
            Route::post('bankAccountResolve', 'PaymentController@accountResolve');
            Route::get('getAllTransactions', 'PaymentController@getAllTransactions');
        });

    });
});

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
