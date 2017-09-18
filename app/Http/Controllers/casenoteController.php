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



class casenoteController extends Controller
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

    
    
    
    
    public function casenoteListing(Request $request)
    {         
        $payload            = JWTAuth::parseToken()->getPayload();
        $logged_userid      = $payload['login_db_userid'];  
        $c_account_key      = $payload['c_acc_key'];
         $db_name           = $payload['db_name'];
        $agencyid           = $payload['agencyid'];
      
        $connId         = $request->ConnId;
        $connectionId   = $request->ConnectionId;
        $userid         = $request->userID;
        $selectedGroupID = $request->groupID;
            
        $start_pos      =   0;
        $count          =   20;
        $filter_Val     =   'NULL';
        $order_Val      =   '';
        $direct         =   "DESC";
        
        //&userID=22703&ConnId=-98287&ConnectionId=351487&groupID=2038
        //22919&ConnId=-99639&ConnectionId=353428&groupID=1015        
        
        $set_ansi = "SET ANSI_NULLS ON SET ANSI_PADDING ON SET QUOTED_IDENTIFIER ON SET ANSI_WARNINGS ON SET CONCAT_NULL_YIELDS_NULL ON  SET ARITHABORT ON SET ANSI_NULL_DFLT_ON ON ";
        $cn_query = $set_ansi. "EXEC [$db_name].[dbo].CaseNote_Details_new_new '$connId', '$agencyid', $count ,$start_pos,$filter_Val, '$connectionId', '$logged_userid', '$order_Val', '$direct', '$selectedGroupID'";  
        
        
        //$casnote_list = DB::connection()->getPdo()->exec($cn_query);
        
        $casnote_list = DB::select($cn_query);
        $result = array('status' => 'success', 'casenotelisting' =>  $casnote_list, "error_status" => 0, "error" => "");
        
        $casenotelist_jsonvalues = json_encode($result);
        dd($casenotelist_jsonvalues);  
        

        // original map file - MAP11.3/airs/xml/case_new.php
        
        exit;
        
        
                
    }
    
    
    
    
    
} // class end



			
	
									
              
               
					