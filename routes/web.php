<?php

use Illuminate\Support\Facades\Route;

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


Route::group(['prefix'=>'/api/', 'middleware' => ['auth']], function () {
    Route::get('/canvas', "CanvasController@index");
    Route::get('/canvas/validateCourse/{course}', "CanvasController@validateUserCanMigrateCourse");
    Route::get('/template', "TemplateController@index");
    Route::get('/terms', "TermsController@index");
    Route::post('/canvas', "CanvasController@createMigration");
});


if (config('shibboleth.emulate_idp') ) {
    Route::name('login')->get("login", '\StudentAffairsUwm\Shibboleth\Controllers\ShibbolethController@emulateLogin');
    Route::group(['middleware' => 'web'], function () {
        Route::get('emulated/idp', '\StudentAffairsUwm\Shibboleth\Controllers\ShibbolethController@emulateIdp');
        Route::post('emulated/idp', '\StudentAffairsUwm\Shibboleth\Controllers\ShibbolethController@emulateIdp');
        Route::get('emulated/login', '\StudentAffairsUwm\Shibboleth\Controllers\ShibbolethController@emulateLogin');
        Route::get('emulated/logout', '\StudentAffairsUwm\Shibboleth\Controllers\ShibbolethController@emulateLogout');
    });
} else {
    Route::name('login')->get("login", '\StudentAffairsUwm\Shibboleth\Controllers\ShibbolethController@login');
    Route::group(['middleware' => 'web'], function () {
        Route::name('shibboleth-login')->get('/shibboleth-login', '\StudentAffairsUwm\Shibboleth\Controllers\ShibbolethController@login');
        Route::name('shibboleth-authenticate')->get('/shibboleth-authenticate', '\StudentAffairsUwm\Shibboleth\Controllers\ShibbolethController@idpAuthenticate');
        Route::name('shibboleth-logout')->get('/shibboleth-logout', '\StudentAffairsUwm\Shibboleth\Controllers\ShibbolethController@destroy');
    });
}


Route::group(['middleware' => 'auth'], function () {
    Route::get('/', 'HomeController@index');
    Route::get('/go', 'HomeController@go')->name("go");
    Route::any('{all}','HomeController@index')->where(['all' => '.*']);
});
