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

define('HTTP_PATH','http://devroleslocal.myadoptionportal.com/');
define('DOC_PATH','E:/xampp/htdocs/MAP11.3/');

$fileLocation = DOC_PATH."map_shared/";
define('FILELOCATION',$fileLocation);


Route::get('/', function () {
    return view('welcome');
});





//\Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) {
//    echo'<pre>';
//    var_dump($query->sql);
//    var_dump($query->bindings);
//    var_dump($query->time);
//    echo'</pre>';
//});
//
//Event::listen('illuminate.query', function($query)
//{
//    var_dump($query);
//});

//Route::get('login', array('uses' => 'userLoginController@userLogin'));






Route::group(['prefix' => 'agencyuser_lar/','middleware'=>['BeforeMiddleware','AfterMiddleware']], function () {

Route::get('usrlogn/token', 'userLoginController@login_lar_tokenGeneration');

/*Route::get('usrlogn/token',function(){
      return redirect('/cw-angular2/ng2-admin/dist/');

});*/

   Route::patch('login/auth', 'AuthController@Login');
   Route::get('dashboard', 'dashboardController@index');
   Route::get('client', 'clientController@clientFullListing');
   Route::get('casenote', 'casenoteController@casenoteListing');
   
   Route::get('dashboard/agencyDetails_view', 'dashboardController@agencyDetails_view');
   Route::get('client/getProfilePicture', 'clientController@getProfilePicture');
   Route::get('client/getClientCountInfo', 'clientController@getClientCountInfo');
   Route::get('client/getCasesChart', 'clientController@getCasesChart');
   
   Route::get('task/getTaskCountDash', 'taskController@getTaskCountDash');
   

  // 

});









Route::group(['prefix' => 'agencyUser_larv/'], function () {
    
    //Route::resource('userlogin', 'userLoginController@userLogin');
    
    //Route::resource('login/auth','AuthController@Login');  
  
    //Route::get('login/destroy','AuthController@Logout');
    
    
//   Route::resource('login/auth',[
//    'middleware' => [
//        'BeforeMiddleware',
//        'AfterMiddleware'
//    ],
//    'uses' => 'AuthController@Login'
//    ]);
          
   //Route::auth();
    
    
   Route::patch('login/auth', ['middleware' => ['BeforeMiddleware', 'AfterMiddleware'], 'uses' => 'AuthController@Login'] );
   Route::get('dashboard', ['middleware' => ['BeforeMiddleware', 'AfterMiddleware'], 'uses' => 'dashboardController@index'] );
   
   

    //Route::get('/home', 'HomeController@index');

});

 //  Route::post('/auth', 'AuthenticateController@authenticate');
//   Route::resource('login', 'loginController@userLogin');
        
//Route::group(['middleware' => 'auth'], function () 
//{
//    Route::group(['namespace' => 'Login'], function()
//    {
//        // Controllers Within The "App\Http\Controllers\Login" Namespace
//        
//        Route::get('login', array('uses' => 'loginController@userLogin'));
//
////        Route::group(['namespace' => 'User'], function()
////        {
////            // Controllers Within The "App\Http\Controllers\Admin\User" Namespace
////        });
//    });

   
//});







//Route::post('login', array('uses' => 'Login/loginController@userLogin'));

