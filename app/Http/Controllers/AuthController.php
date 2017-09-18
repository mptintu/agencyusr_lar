<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Http\Requests;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;

//use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\userLoginController;
use JWTAuth;
use JWTAuthException;

class AuthController extends Controller
{  
    public function __construct()
    {
        global $userlogin; 
        $userlogin = new userLoginController();
    }
    
    public function Login(Request $request)
    {  
        global $userlogin;
        //$userlogin->userLogin();
        
        
        // get user credentials: email, password
        $credentials = $request->only('username', 'password'); 
        
        $username = $request->username;
        $password = $request->password;
        if($username !="" and $password !="")
        {    
            //echo "<br>". $user = User::select('map_user_id','map_username')->where("map_username",$username)->first();
            $userlogin->authenticate_map_agencydetails($username,$password,$request);
        }
        else
        {
            //return response()->json(['error' => 'invalid_credentials'], 401);
            header('Location:'.HTTP_PATH.'myportal.php?er_msg=incorrect');
            exit;
        }
            
    } 
    
    
    public Function Logout()
    { 
        Auth::logout(); 
        return 'logged out'; 
    } 
}