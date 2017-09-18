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

 
use App\ExternalClasses\fileServerManager;

class dashboardController extends Controller
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

        $payload = JWTAuth::parseToken()->getPayload();
        $logged_userid      = $payload['login_db_userid'];  
        $c_account_key      = $payload['c_acc_key'];
        $db_name            = $payload['db_name'];
        $agencyid           = $payload['agencyid'];

        define('AGENCYID',$agencyid);
                    

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
            echo "dashboard username =".$payload->get('username');
            echo "<br>dashboard password =".$payload['password'];
            echo "<br>user type =".$payload['usertype'];
            echo "<br>agencyid =".$payload['agencyid'];
            echo "<br>map userid (irriscentral) =".$payload['mapuserid']; // IC.userid
            echo "<br>db_name =".$payload['db_name'];
            echo "<br>login_db_userid =".$payload['login_db_userid'];

        //$data = $request->session()->all();                  
        //    $d = Session::get('mapusername'); 
        //    dd($data);
        //    print_r(Session::all());            
        //    $d = Session::get('_token');  
           
        return view('dashboard');
    }




    public function agencyDetails_view()
    {
        $fileServerLocationObj = new FileServerManager();

        $agencydetails_list = array();
        $payload            = JWTAuth::parseToken()->getPayload();
        $logged_userid      = $payload['login_db_userid'];  
        $c_account_key      = $payload['c_acc_key'];
        $db_name            = $payload['db_name'];
        $agencyid           = $payload['agencyid'];

        


        $query = "select agency_name, address_line_1, address_line_2, city, state, zip,logo
  from [$db_name].[dbo].[user_agencies] where user_agencies_id = ".$agencyid;
        $agencydetails_list = DB::select($query);
        
        if(count($agencydetails_list) >0)
        {
            $query2 = "select CONVERT(varchar(10), datejoined, 101) as datejoined from 
            [$db_name].[dbo].[user_accounts] where user_id =".$logged_userid;       
            $getlogtime = DB::select($query2);
            $date_joined   = $getlogtime[0]->datejoined;
            $agencydetails_list[0]->datejoined = $date_joined;


            $fileServerLocationObj->findAgency($agencyid,$db_name);
            $fileServerLocationObj->setFileType('Documents');
            $fileServerLocationObj->setDefaultStorageDir(DOC_PATH . 'userhome/agency/');
            $file_details = $fileServerLocationObj->readFile($db_name,'','agency_logo',$logged_userid);
            $file_http_path = $file_details['httppath'];

            $agencydetails_list[0]->agencylogo = $file_http_path;
        }
        else
        {
            $agencydetails_list[0]->datejoined = 0;
            $agencydetails_list[0]->agencylogo = '';
        }

/*$fileServerLocationObj->setFileType('Documents');
    $fileServerLocationObj->setDefaultStorageDir($path["serloc"] . 'userhome/agency/');
    $file_details = $fileServerLocationObj->readFile('','agency_logo',$user_id);
    $file_http_path = $file_details['httppath'];
*/



        return $agencydetails_list;
    }







} // class end
