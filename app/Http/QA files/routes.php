<?php

//use Auth;

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

define('HTTP_PATH','https://qaroles.myadoptionportal.com/');
define('DOC_PATH','/var/www/html/');

$fileLocation = DOC_PATH."map_shared/";
define('FILELOCATION',$fileLocation);


Route::get('/', function () {
    return view('welcome');
});



Route::group(['prefix' => 'agencyuser_lar/','middleware'=>['BeforeMiddleware','AfterMiddleware']], function () {

Route::get('usrlogn/token', 'userLoginController@login_lar_tokenGeneration');

Route::get('dashboard', 'dashboardController@index');

   Route::patch('login/auth', 'AuthController@Login');
   
   Route::get('client', 'clientController@clientFullListing');
   Route::get('casenote', 'casenoteController@casenoteListing');
   
   Route::get('dashboard/agencyDetails_view', 'dashboardController@agencyDetails_view');
   Route::get('client/getProfilePicture', 'clientController@getProfilePicture');
   Route::get('client/getClientCountDash', 'clientController@getClientCountDash');
   
   Route::get('task/getTaskCountDash', 'taskController@getTaskCountDash');
   


});


