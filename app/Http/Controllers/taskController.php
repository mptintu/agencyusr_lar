<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use JWTAuth;
use JWTFactory;

use App\Form;
use App\User;
use App\LibraryMaster;
use App\ReportMaster;
use App\ReportFieldMaster;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

use Illuminate\Http\Response;
use Illuminate\Database\Schema\Blueprint;
use Session;
use SoapBox\Formatter\Formatter;
use DB;

use \Tymon\JWTAuth\Exceptions\TokenExpiredException;
use \Tymon\JWTAuth\Exceptions\TokenInvalidException;
use \Tymon\JWTAuth\Exceptions\JWTException;



class taskController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    { 

        $queries = DB::getQueryLog();                     
        log_Qry($queries);
                    

    }

    
        
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       
        $payload = JWTAuth::parseToken()->getPayload();
        // dd($payload);
        // then either of
        echo "Client page";
            echo "<br> username =".$payload->get('username');
            echo "<br> password =".$payload['password'];
            echo "<br>user type =".$payload['usertype'];
            echo "<br>agencyid =".$payload['agencyid'];
            echo "<br>map userid (irriscentral) =".$payload['mapuserid']; // IC.userid
            echo "<br>db_name =".$payload['db_name'];
            echo "<br>login_db_userid =".$payload['login_db_userid'];
        //
            //$data = $request->session()->all();
            
       
        //    $d = Session::get('mapusername'); 
        //    //dd($data);
        //    print_r(Session::all());            
        //    $d = Session::get('_token');  
           
        return view('welcome');
    }
    
       
    
    
    
    
    

    public function getTaskCountDash()
    {
        $payload            = JWTAuth::parseToken()->getPayload();
        $logged_userid      = $payload['login_db_userid'];  
        $c_account_key      = $payload['c_acc_key'];
        $db_name            = $payload['db_name'];
        $agency_id          = $payload['agencyid'];
        $qry_profpic = DB::select("SELECT * FROM [$db_name].[dbo].[user_accounts] 
            WHERE user_id = ".$logged_userid);

        $cw_agency_id = $qry_profpic[0]->agency_id;
        $userid = $qry_profpic[0]->user_id;
        $cw_Puserid = $qry_profpic[0]->case_worker_parent_user_id;
        
        $dashtaskarr= [
        array('duetoday' => $cw_Puserid,
              'overdue' => $userid,
              'duetomrw' => $cw_agency_id )];

        return $dashtaskarr;
    }

    
    
} // class end
