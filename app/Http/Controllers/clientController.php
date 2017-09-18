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



class clientController extends Controller
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
    
    
    
    
    public function clientFullListing(Request $request)
    {
             
                
        $payload            = JWTAuth::parseToken()->getPayload();
//echo date('d M Y H:i:s');

       //echo "----exp==". $expires_at = date('d M Y H:i:s', $payload->get('exp')); 

        $logged_userid      = $payload['login_db_userid'];  
        $c_account_key      = $payload['c_acc_key'];
        $db_name            = $payload['db_name'];
        $start_qrycnt       = $request->start_qrycnt;
        $endcnt                = $request->end;
        $startRow   = $start_qrycnt;
        $endRow     = $endcnt;
        $program=''; $role='';$group='';$usersStatus='';$contacttype='';$nameSearch='';$usernameSearch='';$contact='';
        $caseworker='';
        
        
        $set_ansi = "SET ANSI_NULLS ON SET ANSI_PADDING ON SET QUOTED_IDENTIFIER ON SET ANSI_WARNINGS ON SET CONCAT_NULL_YIELDS_NULL "
                . "ON SET ARITHABORT ON SET ANSI_NULL_DFLT_ON ON";
        $query =  " EXEC [$db_name].[dbo].usp_ClientSearch '$c_account_key','$startRow','$endRow', '$program' ,"
               . "'$role', '$group', '' ,'$usersStatus' ,'$contacttype' ,'$nameSearch' , '', '$usernameSearch' ,'$contact',"
               . "'$caseworker','$logged_userid'";
    
        //echo $query;
        $clientListing = DB::select($query);
        
       
        foreach ($clientListing as $row=>$cl)
        { 
            $ConnId         = $cl->ConnId;
            $ConnectionId   = $cl->ConnectionId; 
            $groupid        = $cl->uniq_g_id;
            
            $clientListing[$row]->casenote_link = '<a href="casenote?userID='.$cl->user_id.'&ConnId='.$ConnId.'&ConnectionId='.$ConnectionId.'&groupID='.$groupid.'&">CASE NOTE</a>';
        
            
        }
       

        return $clientListing;

        
    }
    
    
    public function getProfilePicture(Request $request)
    {

        $payload            = JWTAuth::parseToken()->getPayload();
        $logged_userid      = $payload['login_db_userid'];  
        $c_account_key      = $payload['c_acc_key'];
        $db_name            = $payload['db_name'];
        $agency_id          = $payload['agencyid'];
        
        $qry_profpic = DB::select("SELECT photo FROM [$db_name].[dbo].[user_accounts] 
            WHERE user_id = ".$logged_userid);
                
        $pht_path = HTTP_PATH.'map_shared/'.$agency_id.'/Documents/Agency/'.$qry_profpic[0]->photo;
        $doc_pht_path = DOC_PATH.'map_shared/'.$agency_id.'/Documents/Agency/'.$qry_profpic[0]->photo;
       

        if(file_exists($doc_pht_path))
        { 
            $logged_user_profpic_storagepath = $pht_path;
        }
        else
            $logged_user_profpic_storagepath = HTTP_PATH.'images/no-image.png';


        $imagearr= [array('profileimage' => $logged_user_profpic_storagepath )];
        return $imagearr;

        //return $logged_user_profpic_storagepath;
                    
    }


    // get count in dashboard
    public function getClientCountInfo()
    {
        $payload            = JWTAuth::parseToken()->getPayload();
        $logged_userid      = $payload['login_db_userid'];  
        $c_account_key      = $payload['c_acc_key'];
        $db_name            = $payload['db_name'];
        $agency_id          = $payload['agencyid'];
        $qry_profpic = DB::select("SELECT * FROM [$db_name].[dbo].[user_accounts] 
            WHERE user_id = ".$logged_userid);
        $userid = $qry_profpic[0]->user_id;
        $cw_Puserid = $qry_profpic[0]->case_worker_parent_user_id;
        $cw_agency_id = $qry_profpic[0]->agency_id;
        
        $dashclientarr= [array('responsecount' => $userid,'msgcount' => $cw_agency_id,'opencasecount' => $cw_Puserid )];
        
        return $dashclientarr;
    }

    

    public function getCasesChart()
    {
        global $payload;
        echo "<br> dfdfd =".$payload->get('username');
    }


    
} // class end
