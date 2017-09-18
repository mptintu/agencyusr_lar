<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\User;
use App\Http\Requests;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;


//use Illuminate\Foundation\Bus\DispatchesJobs;
//use Illuminate\Routing\Controller as BaseController;
//use Illuminate\Foundation\Validation\ValidatesRequests;
//use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
//use Illuminate\Foundation\Auth\Access\AuthorizesResources;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use JWTAuth;
use JWTFactory;
use Session;


class userLoginController extends Controller
{
   
    
    function authenticate_map_agencydetails($username,$password,$request)
    {
              
        $users = DB::table('map_user_accounts')->select('map_username', 'map_user_id','map_encryption')->where("map_username",$username)->get();
        if(count($users) >0)
        {
            // username exists.
            
            /*
             *   enabled querry logging to true in Illuminate\Database\connection. 
             *   Created middlewares - BeforeMiddleware.php and AfterMiddleware.php and logged the queries to agencyUser_lar/storage/logs/query_logs/
             *
             *   get logged queries
             *   $queries = DB::getQueryLog(); 
             *   echo $queries[0]['query'];
             * 
             *   get executed query
             *   DB::table('map_user_accounts')->toSql() ;
             * 
             *    helper
             *    logdetails_qry($queries);
            */
        
            $mapencryption = $users[0]->map_encryption;
            if($mapencryption == 'Y')
            {
                $hashedpass = pwdencryption($username.$password);
            }
            else
            {
                $hashedpass = sha1($username.$password);
            }
            
                
            $userlogin = DB::table('map_user_accounts')
                            ->join('map_user_agencies', 'map_user_accounts.map_user_id', '=', 'map_user_agencies.map_user_id')
                            ->select('map_user_accounts.map_user_id', 'map_user_agencies.status',
                                    'map_user_agencies.map_agency_id','map_user_agencies.user_id')
                            ->where([
                                        ['map_user_accounts.map_username', '=',$username],
                                        ['map_user_accounts.map_password', '=', $hashedpass],
                                        ['map_user_agencies.status', '=', 'Active'],
                                    ])      
                            ->get();

            //dd($userlogin);
            $innerFlag = 0;
            $adminFlag = 0;
            if(count($userlogin) >0)
            { 
                $mapuserid = $userlogin[0]->map_user_id;                    
                if($mapuserid !='' && $mapuserid !=0)
                {
                    $innerFlag = 1;
                    $map_agency_id  = $userlogin[0]->map_agency_id;
                    $user_id        = $userlogin[0]->user_id;
                    if($user_id == 1){
                        $adminFlag = 1;
                    }
                    else
                    { 
                        $hashedpass1 = sha1($username.$password);

                        /* ------- FIND THE AGENCY AND USER DETAILS USING LARAVEL QUERY BUILDER -- START -------------  */
                        /*
                         * Find all user ids with hashed password and email
                         */

//                            $all_map_userid = DB::table('map_user_accounts')
//                                    ->select('map_user_id')
//                                    ->where('map_username', '=',$username)
//                                    ->whereIn('map_password', [$hashedpass,$hashedpass1])                                       
//                                    ->get();
//                            foreach ($all_map_userid as $user) {
//                                $map_usrid_arr[] =  $user->map_user_id;
//                            }

                        /*
                         * Find the agency ids of above map user ids
                         */

//                            $mp_agncy_id = DB::table('map_user_agencies')
//                                    ->select('map_agency_id')
//                                    ->where('status','=','Active')
//                                    ->whereIn('map_user_id', $map_usrid_arr )
//                                    ->get();
//
//                            foreach ($mp_agncy_id as $mpag) {
//                                $map_agncyid_arr[] =  $mpag->map_agency_id;
//                            }

                        /*
                         * Find the agencies                             
                         */

//                            $agencydetails = DB::table('lutPrimaryAgency')
//                                            ->whereIn('SiteID', $map_agncyid_arr)
//                                            ->get();


                        /* ------- FIND THE AGENCY AND USER DETAILS USING LARAVEL QUERY BUILDER -- END -------------  */

                        $agencydetails = DB::select("SELECT * FROM [IrrisCentral].[dbo].lutPrimaryAgency where SiteID IN
                                (SELECT map_agency_id FROM [IrrisCentral].[dbo].[map_user_agencies] where status = 'Active'  
                                and map_user_id IN
                                (SELECT [map_user_id] FROM [IrrisCentral].[dbo].[map_user_accounts] 
                                where [map_username]='$username' and map_password in ('$hashedpass','$hashedpass1')))");

                        //dd($agencydetails);
                         foreach ($agencydetails as $agencyrow) {
                           $userDetails[] =  $agencyrow;
                         }


                       /* Check how many agencies the user is registered with the same username 
                        * If only in Single agency(SDC) then don't allow to login and show error message
                        * else allow login and Lock the Agency in agency selection window
                        *  users having same usernam & password as multi user
                        */
                       /*
                        * map_user_accounts login_time & login_attempts
                        * user_accounts acc_lock
                        * if correct login update login_time & login_attempts
                        * else
                        * foreach agency get settings & lock
                        */

                        //$d = DB::select(DB::raw("select * from lutPrimaryAgency"));
                        //print_r($d);                            

                        $agencyId = 0;$lock = 0;
                        $getDetails = DB::select("SELECT acc.[map_username],acc.[map_user_id],ag.[map_agency_id],ag.[user_id],
                                    acc.[map_login_attempts]
                                    FROM [IrrisCentral].[dbo].[map_user_accounts] acc 
                                    INNER JOIN [IrrisCentral].[dbo].[map_user_agencies] ag 
                                    ON acc.[map_user_id]= ag.[map_user_id] 
                                    WHERE acc.[map_username]='$username'");
                        if(count($getDetails) >0)
                        { 
                            foreach ($getDetails as $resultArray) 
                            {
                                $details[] = $resultArray; 
                                $loginCount =  $resultArray->map_login_attempts;
                            }
                            if($loginCount == null)
                               $loginCount = 0;

                            if(!$innerFlag){
                                $loginCount++;
                            }
                            if(count($details) == 1){
                                $singleUser = 1;  
                            }else{
                                $singleUser = 0;
                            }

                            foreach ($details as $key => $value) 
                            {
                                $agencyId   = $value->map_agency_id;                                      
                                $map_user_id    = $value->map_user_id; // irriscentral user_id
                                $db_user_id = $value->user_id;
                                //get db name

                                $mp_agncy_id = DB::table('lutPrimaryAgency')
                                                ->select('DBName','PrimaryAgencyName')
                                                ->where('SiteID','=',$agencyId)                                   
                                                ->first();                                               
                                $dbName         = $mp_agncy_id->DBName;
                                $agencyName     = $mp_agncy_id->PrimaryAgencyName; 

                                //get agency configured login attempts
                                //if agency admin should be locked 
                                //if cw should be locked

                                $getSettingsQry = DB::table("$dbName.dbo.system_settings")
                                                ->select('setting','useroption')
                                                ->whereIn('useroption', ['pwd_incorrect_login','pwd_lock_ag','pwd_lock_cw'])                               
                                                ->get();  
                                //dd($getSettingsQry);
                                foreach ($getSettingsQry as $setings)
                                {
                                   $usroptn = $setings->useroption;
                                    if($usroptn == 'pwd_incorrect_login')
                                        $attempts = $setings->setting;
                                    else if($usroptn == 'pwd_lock_ag')
                                        $AgLock = $setings->setting;
                                    else if($usroptn == 'pwd_lock_cw')
                                        $CwLock = $setings->setting;
                                }

                                //check if account is locked
                                $getAccLockQry = DB::table("$dbName.dbo.user_accounts")
                                                ->select('acc_lock')
                                                ->where('user_id','=',$db_user_id)                                   
                                                ->first();          
                                $accLock = $getAccLockQry->acc_lock;                                                               

                                if($accLock == 1){
                                    $agencies .= $agencyName." ";
                                    if(!$lock)
                                    $lock = 1;
                                }
                                else
                                { 
                                    if($attempts > 0) // agency configured login attempt count
                                    {
                                        //get login attempts for a user
                                        $getLoginAttempts = DB::table('IrrisCentral.dbo.map_user_accounts')
                                                ->select('map_login_attempts','map_login_time')
                                                ->where('map_user_id','=',$map_user_id)                                   
                                                ->first();         
                                        if(count($getLoginAttempts)>0)
                                        {
                                            //login attempted same as that configured
                                            if($loginCount >= $attempts)
                                            {
                                                //check if the user logged-in is a agency or cw
                                                if($db_user_id > 0)
                                                {
                                                    $getUserTypeQry = DB::table("$dbName.dbo.user_accounts")
                                                                        ->select('user_type')
                                                                        ->where('user_id','=',$db_user_id)                                   
                                                                        ->first();                                                             
                                                    $userType = $getUserTypeQry->user_type;

                                                    if($userType == 'agency')
                                                    {
                                                        if($AgLock == 1)
                                                        {//if lock is configured for agency only then lock else update attempts to 0

                                                            $updateLoginAttempts = DB::table("$dbName.dbo.user_accounts")
                                                                                    ->where('user_id', $db_user_id)
                                                                                    ->update(['acc_lock' => 1]);                                                                    
                                                            if(!$lock)
                                                                $lock = 1;
                                                        }
                                                        else{
                                                            $loginCount = 0;
                                                        }
                                                    }
                                                    else if($userType == 'agency_user')
                                                    {
                                                        if($CwLock == 1)
                                                        {   //if lock is configured for agency only then lock else update attempts to 0
                                                            $updateLoginAttempts = DB::table("$dbName.dbo.user_accounts")
                                                                                    ->where('user_id', $db_user_id)
                                                                                    ->update(['acc_lock' => 1]);                                                                     
                                                            $agencies .= $agencyName." ";
                                                            if(!$lock)
                                                                $lock = 1;
                                                        }else{
                                                            $loginCount = 0;
                                                        }
                                                    }
                                                    else if($userType == 'admin')
                                                    {
                                                    }
                                                    else
                                                    {
                                                        $updateLoginAttempts = DB::table("$dbName.dbo.user_accounts")
                                                                                ->where('user_id', $db_user_id)
                                                                                ->update(['acc_lock' => 1]);  
                                                        $agencies .= $agencyName." ";
                                                        if(!$lock)
                                                            $lock = 1;
                                                    }
                                                }
                                            }

                                            $loginTime = $getLoginAttempts->map_login_time;
                                            $loginTime = date('Y-m-d H:i:s',strtotime($loginTime));

                                            if(!$innerFlag)
                                            {
                                                if($loginTime != null)
                                                {                                                            
                                                    $diffQry = DB::select("select DATEDIFF(minute, '$loginTime', GETDATE()) AS MinuteDiff");                                     
                                                    $minutes = $diffQry[0]->MinuteDiff;  
                                                    if($minutes < 30){
                                                        $updateQry = DB::update("update IrrisCentral.dbo.map_user_accounts set map_login_attempts = $loginCount, map_login_time = GETDATE() where map_user_id = ?", [$map_user_id]);                                        
                                                   }else{
                                                       $updateQry = DB::update("update IrrisCentral.dbo.map_user_accounts set map_login_attempts = 1, map_login_time = GETDATE() where map_user_id = ?", [$map_user_id]);                                        

                                                    } 
                                                }
                                                else
                                                {
                                                    $updateQry = DB::update("update IrrisCentral.dbo.map_user_accounts set map_login_attempts = 1, map_login_time = GETDATE() where map_user_id = ?", [$map_user_id]);                                                                                 
                                                }                                                        
                                            } 
                                        }
                                    }
                                }              
                            }
                        }
                        else                                    
                        {
                            header('Location:'.HTTP_PATH.'myportal.php?er_msg=incorrect');
                            exit;
                        }
                        if($lock && $innerFlag != 1)
                        {
                            $loginsuccess = "no";
                            $badlogin = "true";//diff msg for agency and users
                            if($userType == 'agency')
                            {
                                $loginmessage = "<font color=ff0000>Your account is locked.</font> Please contact your Portal admin.<br/>";
                            }
                            else{
                                $loginmessage = "<font color=ff0000>Your account is locked for ".$agencies.".</font> Please contact your Agency to unlock account.<br/>";
                            }
                        }
                        else if($lock && $singleUser == 1)
                        {
                            $loginsuccess = "no";
                            $badlogin = "true";//diff msg for agency and users
                            if($userType == 'agency'){
                                $loginmessage = "<font color=ff0000>Your account is locked.</font> Please contact your Portal admin.<br/>";
                            }
                            else{
                                $loginmessage = "<font color=ff0000>Your account is locked for ".$agencies.".</font> Please contact your Agency to unlock account.<br/>";
                            }    
                        }
                        else if($innerFlag)
                        {
                            
                            /******************  update login attempts to 0 on successful login  ******************/
                            $updateQry = DB::update("update IrrisCentral.dbo.map_user_accounts set map_login_attempts = 0, map_login_time = GETDATE() where map_user_id = ?", [$map_user_id]);                                        
                            
                            /*$array = ['mapusername' => '1234567',
                                      'mappassword' => '123'];
                           
                            $request->session()->put('data', $array);
                             * 
                             */
                                //$request->session()->set('data', $array);
                                //Session::put('data', $array);
                            
                                //
                                //Session::put('progress', '5%');
                            
                            $_SESSION['session_logged_user_id']  = $db_user_id;
                           
                            Session::set('session_logged_user_id', $db_user_id);          
                            $map_username   = $username;
                            Session::set('mapusername', $username);                              
                            Session::set('mappassword', $password);
                            $db_name         = trim($agencydetails[0]->MAPDBName);
                            Session::set('DBName',$db_name);
                            $subDomain    = trim($agencydetails[0]->subDomain);
                            Session::set('subDomain',$subDomain);
                            $map_agency   = trim($agencydetails[0]->PrimaryAgencyName);
                            Session::set('PrimaryAgencyName',$map_agency);
                            $DomainName   = trim($agencydetails[0]->DomainName);
                            Session::set('DomainName',$DomainName);
                            $SiteAbbrev   = trim($agencydetails[0]->SiteAbbrev);
                            Session::set('SiteAbbrev',$SiteAbbrev);
                            Session::set('adminFlag',$adminFlag);
                            
                            Session::save();
                            
                             
                            
                            // ADMIN login -> System Preferences -> Badge area. Agency ID is appended along with the URL as key ID
                            
                            //$key_id = $_REQUEST['key_id'];
                            $key_id = ''; // remove this line 
                            
                            if((count($agencydetails)>2 || $adminFlag == 1))
                            {                                 
                                if(trim($key_id))
                                { 
                                    //handle case where user has same username & different password
                                    $this->chkLock($key_id,$db_name,$map_username,$adminFlag,$request,$mapuserid);
                                }
                                else
                                {
                                    header('Location:'.$path['wwwloc'].'agencyselection.php?MapuName='.$mapuname);
                                    exit;  
                                }
                            }
                            else
                            {
            
                                $this->chkLock($key_id,$db_name,$map_username,$adminFlag,$request,$mapuserid); //irriscentral userid
                            }
                               
                        } 
                        else
                        {
                            $loginsuccess = "no";
                            $badlogin = "true";
                            $loginmessage = "<font color=ff0000>Incorrect Login Information.</font> Please try again <font color=ff0000>OR </font>select Forgot Login Info to reset your password.<br>";
                        }
                    }
                }
            }
            else
            { 
                // username correct but incorrect password
                 //header('Location:'.env('MY_GLOBAL_VAR').'myportal.php?er_msg=incorrect');
                header('Location:'.HTTP_PATH.'myportal.php?er_msg=incorrect');
                exit;
            } 
        }
        else
        { 
            // username incorrect 
             //header('Location:'.env('MY_GLOBAL_VAR').'myportal.php?er_msg=incorrect');
            header('Location:'.HTTP_PATH.'myportal.php?er_msg=incorrect');
            exit;
        } 
    } // fn authenticate_map_agencydetails() end
    
    
    
    
    function chkLock($key_id='',$db_name,$map_username,$adminFlag,$request,$mapuserid)
    {        
        if(trim($key_id))
        { 
            $DomdetailsRS = DB::select("SELECT subDomain, SiteAbbrev FROM [IrrisCentral].[dbo].[lutPrimaryAgency] where [SiteID]= (SELECT [c_account_key] FROM user_agencies WHERE agency_id =$key_id AND mainRecord = 1)");                                                               
            $subDomain    = $DomdetailsRS[0]->subDomain;
            $SiteAbbrev   = $DomdetailsRS[0]->SiteAbbrev;
                        
            Session::set('subDomain',$subDomain);
            Session::set('SiteAbbrev',$SiteAbbrev);  
            Session::save();
        }
        
        $getLockQry = DB::select("SELECT acc_lock,user_type FROM [$db_name].[dbo].[user_accounts] WHERE [username]='$map_username'");                                    
        $acc_Lock   = $getLockQry[0]->acc_lock;
        $user_type  = $getLockQry[0]->user_type;
        if($acc_Lock == 1)
        {
            $loginsuccess = "no";
            $badlogin = "true";//diff msg for agency and users
            if($user_type == 'agency'){
                $loginmessage = "<font color=ff0000>Your account is locked.</font> Please contact your Portal admin.<br/>";
            }
            else{
                $loginmessage = "<font color=ff0000>Your account is locked for ".$map_agency.".</font> Please contact your Agency to unlock account.<br/>";
            }
            session_unset();    
            session_destroy(); 
            return;
        }
        else
        {      
            //echo "authenticate session";
            $this->authenticate_session($map_username,$key_id,$adminFlag,$db_name,$request,$mapuserid);
        }
    } // fn chkLock() end
    
   
    
    
    
    function authenticate_session($map_username,$key_id,$adminFlag,$db_name,$request,$mapuserid)
    {
        if($adminFlag ==1)
           $this->userLogin('login', '','',$key_id,$map_username,$db_name,$request,$mapuserid);
        else { 
               if($this->checkExpiry($map_username,$db_name) == true)
                   $this->userLogin('login', '','',$key_id,$map_username,$db_name,$request,$mapuserid);
           }
        if(trim($key_id))
        {
             header("'Location: ".HTTP_PATH."myportal.php?key_id=$key_id&agrement=true'");
             exit;
        }
    } // fn authenticate_session() end
    
    
    
    
    function checkExpiry($map_username,$db_name)
    { 
        //need to check the last login date first & then proceed to pwd check
        //check the last login date & force reset password if user not logged for long time        
        
        $acc_expiry_info = DB::table("$db_name.dbo.system_settings")
                            ->select('setting')
                            ->where('useroption','=','acc_expiry_days')
                            ->first();          
        $acc_expiry_days = $acc_expiry_info->setting;   
                
        $newUser = 0;
        if($acc_expiry_days > 0)
        {
            $validate_acc = DB::select("SELECT last_login FROM [$db_name].[dbo].[user_accounts] WHERE [username]='$map_username'");
            if(count($validate_acc) >0)
            {
                $lastLoginDate   = date('Y-m-d',strtotime($validate_acc[0]->last_login));
                if($lastLoginDate != null)
                {

                    $diffQry = DB::select("select DATEDIFF(day, '$lastLoginDate', GETDATE()) as dayss");                                     
                    $numOfDays = $diffQry[0]->dayss;
                    if($numOfDays > $acc_expiry_days){
                        //expired
                        header('Location:passwordDetails.php');
                        exit;
                    }
                }else{
                    //new user allow login
                    $newUser = 1;
                }
            }  
        }
    
        if($newUser != 1)
        {
            //check password expiry
            $pwd_expiry_info = DB::table("$db_name.dbo.system_settings")
                            ->select('setting')
                            ->where('useroption','=','pwd_expiry_days')
                            ->first();          
            $expiry_days = $pwd_expiry_info->setting;   
                   
            if($expiry_days > 0)
            {
                $validate_pwd = DB::select("SELECT user_id,passwordchangedate,datejoined FROM [$db_name].[dbo].[user_accounts] WHERE [username]='$map_username'");
                if(count($validate_pwd)>0)
                {
                    $pwdChangeDate = date('Y-m-d',strtotime($validate_pwd[0]->passwordchangedate));                 
                    $accCreationDate = date('Y-m-d',strtotime($validate_pwd[0]->datejoined));
                     if($pwdChangeDate != null){
                        $dateDiffQry = DB::select("select DATEDIFF(day, '$pwdChangeDate', GETDATE()) as dayss");  
                    }else{
                        $dateDiffQry = DB::select("SELECT DATEDIFF(day, '$accCreationDate', GETDATE()) as dayss");
                    }
                    
                    $numDays = $diffQry[0]->dayss;
                    if($numDays > $expiry_days){//expired
                        //show password reset screen
                        echo '<script type="text/javascript">window.alert("password expired");</script>';
                        header('Location:passwordDetails.php');
                        exit;
                    }
                    else{//allow login
                        return true;
                    }
                }else{
                    //username incorrect
                }
            }else{
            //do nothing allow to login
            return true;
            }
        }

    }  // fn checkExpiry() end

    
    
    
    
    
    
    function userLogin($loginaction, $module,$videomsgs,$key_id =  '',$map_username,$db_name,$request,$mapuserid)
    {   
        $cw_home_path ='';
        switch($loginaction)
        {
            case "login" :
            {         
                $validate_pwd = DB::select("SELECT user_id, user_type, last_login, agency_group,video_msg,"
                       . " timezone, first_name, last_name, photo, ConnId, agency_id "
                       . " FROM [$db_name].[dbo].[user_accounts] WHERE [username]='$map_username' "
                       . " and status='Active' and status_mode = 'Active'");
                if(count($validate_pwd)>0)
                {
                    $logged_user_name   = $validate_pwd[0]->first_name." ".$validate_pwd[0]->last_name; 
                    $v_msg              = $validate_pwd[0]->video_msg;
                    $agency_group       = $validate_pwd[0]->agency_group;
                    $timezone           = $validate_pwd[0]->timezone;
                                       
                    //$logged_user_profpic_storagepath = DOC_PATH.'userhome/users/'.$validate_pwd[0]->agency_id.'/'.$validate_pwd[0]->user_id.'/'.$validate_pwd[0]->ConnId.'/'.$validate_pwd[0]->photo;

                    $logged_user_profpic_storagepath = DOC_PATH.'map_shared/'.$validate_pwd[0]->agency_id.'/Documents/Agency/'.$validate_pwd[0]->photo;
                    $usertype   = $validate_pwd[0]->user_type;
                    $usrid      = $validate_pwd[0]->user_id;
                    $aid = $this->getAgencyIDAnyUser($usrid,$db_name);
                    Session::set('loggedin_user',$logged_user_name);
                    Session::set('agencyId',$aid);
                    Session::set('session_is_logged',true);                    
                    Session::set('session_current_username',$map_username);
                    Session::save();
                    
                    if($usertype == 'adoptive_parent' || $usertype == 'birth_parent')
                    {
                        
                        Session::set('register',true);
                        Session::save();
                        
                        $videoFlag = 0;
                        
                        $agCount = DB::select("select count(*) as agree_cnt from [$db_name].[dbo].[agreement]");
                        $agreement_cnt = $agCount[0]->agree_cnt;
                        $agAceptCount = DB::select("select count(*) as us_agmnt from [$db_name].[dbo].[user_agreements] where user_id = ".$usrid);
                        $user_agreement_cnt = $agAceptCount[0]->us_agmnt;
                        
                        $vName = DB::select("select demo_video from [$db_name].[dbo].[user_agencies] where agency_id = '$aid' and demo_video <> NULL");
                        if(count($vName) >0)
                        {
                            if($v_msg =='N')
                                $videoFlag = 0;
                            else
                              $videoFlag = 1;  
                        }

                        if(($agreement_cnt > $user_agreement_cnt[0]) || $videoFlag == '1') // video displayed true
                        {
                            $thispath 			= pathinfo($_SERVER['PHP_SELF']);               
                            $path_temp                  = "/sw_agreement.php?task=agreement";                 

                           
                    
                    
                            Session::set('preLoginId',$usrid);
                            Session::set('preusername',$username);
                            Session::set('prepassword',$password);
                            Session::set('preagencyid',$agency_group);
                            Session::save();
                        
                            // agreement video display URL
                            $tempUrl			=	HTTP_PATH.$path_temp;
                            if(!$key_id)
                                    header("Location: ".$tempUrl);
                            else
                                    echo "<script>window.open('$tempUrl','_new','left=20,top=20,width=500,height=500,toolbar=1,resizable=1');</script>";

                            break;
                        }                       
                    }    
                    
                    $access = $this->userModAccess($usertype,$db_name);
                    
                    for ($i=0; $i < count($access); $i++)
                    {
                        Session::set('cookie_'.$access[$i].'_userid',$usrid);
                        setcookie('cookie_'.$access[$i].'_userid', $usrid);                        
                        $user_id = ${"cookie_".$access[$i]."_userid"} = $usrid;
                        if ($module == $access[$i])
                        {
                            $showlogin = "";
                        }
                        Session::set('session_'.$access[$i].'_userid',$usrid);                       
                        Session::set('timezone',$timezone);                        
                        $c_acc_key    = $this->get_c_acc_key($aid,$db_name,$usertype);
                        Session::set('c_account_key',$c_acc_key);
                        Session::save();
                    }
                

                    $lst_log = date('Y-m-d H:i:s');
                    $updateQry = DB::update("update [$db_name].[dbo].[user_accounts] set last_login = '$lst_log' where user_id = ?", [$usrid]);                                        
                    // call user checking free posting periods
                    
                    //$this->userCheckFreePosting($user_id);
                    //$this->updateSecondPersonDetails($myrow[0]);
                    //dd( Session::all());exit;
                   
                    if($usertype == 'agency')
                    { 
                        $tempUrl   = "../../index-mp.php?Flag=1".$cw_home_path;
                    }
                    else if($usertype == 'adoptive_parent' || $usertype == 'birth_parent')
                    {
//                        $roleParams                     = $this->getUserData($usrid,$db_name); 
//                        $_SESSION['user_Connid']        = $roleParams['connId'];
//                        $_SESSION['CoupleConnectionID'] = $roleParams['coupleConnId'];
//                        $_SESSION['session_caseId']     = $roleParams['connectionId'];                    
                        $tempUrl   = HTTP_PATH."/core/application/home.php";
                    }
                    else if($usertype == 'agency_user')
                    {   
                        //$request->headers->set('authorization', '11');
                        //$parameter = ['authorization' =>  '1'];
                        //$request->headers->add($parameter);
                        $token = $this->tokenGeneration($request,$usertype,$aid,$mapuserid,$db_name,$usrid,$c_acc_key);                        
                        $tempUrl   = HTTP_PATH."agencyUser_lar/dashboard?token=".$token;
                        
                    }
                    else 
                    { 
                        $tempUrl   = HTTP_PATH."/index-mp.php?Flag=1".$cw_home_path;
                    }
                    $queries = DB::getQueryLog();                     
                    log_Qry($queries);
                   
                                      
                    
                    if(!$key_id)
                    {                        
                        header("Location: ".$tempUrl);
                        exit();
                    }
                   else
                    echo "<script>window.open('$tempUrl','_new','left=20,top=20,width=500,height=500,toolbar=1,resizable=1');</script>";
                }
                else
                {
                    $loginsuccess = "no";
                    $badlogin = "true";
                    $loginmessage = "<font color=ff0000>Incorrect Login Information.</font> Please try again <font color=ff0000>OR </font>select Forgot Login Info to reset your password.<br>";
                }
            } 
            break;
            
            case "logout" :
            { 
                $access = userModAccess("all");
                header("Location: myportal.php?module=&pluginoption=userslogin");
                exit();
            } 
            break;
        
//            default:
//            { 
//                if($_SESSION['session_is_logged'] == "true" && strlen($_SESSION['session_current_username']) > 0)
//                {
//                    //re-create session data
//                    $Data->data = array("user_id", "user_type");
//                    $Data->where = "username='".$_SESSION['session_current_username']."' and status='Active'";
//                    $Data->order = "";
//                    $result = $Data->getData(user_accounts);
//
//                    if($myrow = mssql_fetch_row($result))
//                    {
//                        $access = $this->userModAccess($myrow[1]);
//
//                        for ($i=0; $i < count($access); $i++)
//                        {
//                            $_SESSION['cookie_'.$access[$i].'_userid'] = $myrow[0];
//                            setcookie('cookie_'.$access[$i].'_userid', $myrow[0]);
//                            $user_id = ${"cookie_".$access[$i]."_userid"} = $myrow[0];
//                            if ($module == $access[$i])
//                            {
//                                $showlogin = "";
//                            }
//                            $_SESSION["session_".$access[$i]."_userid"] = $myrow[0];
//                            $_SESSION['session_logged_user_id'] = $myrow[0];
//                        }
//                    }
//                    if($_SESSION['session_logged_user_id'])
//                        {
//                        $_SESSION['user_Connid']        = $this->UserConnid($_SESSION['session_logged_user_id']);
//                        $c_acc_key                      = $fileServerLocationObj->get_c_acc_key($_SESSION['agencyId']);
//                        $_SESSION['c_account_key']      = $c_acc_key;
//                        $_SESSION['CoupleConnectionID'] = $this->getCoupleConnectionID($c_acc_key,$_SESSION['user_Connid']);
//                        }
//                }
//            } 
//            break;
        }
    } // fn userLogin() end


    



    function getAgencyIDAnyUser($userID,$db_name)
    {
        $getUserDetails = DB::select("select user_type,agency_id,group_id,agency_group from [$db_name].[dbo].[user_accounts] where user_id = ".$userID);
        if(count($getUserDetails) >0)
        {
            $usr_typ    = $getUserDetails[0]->user_type;
            $ag_id      = $getUserDetails[0]->agency_id;
            $gp_id      = $getUserDetails[0]->group_id;
            $agcy_grp   = $getUserDetails[0]->agency_group;
            
            if($usr_typ == "agency")
            {
                if($ag_id =='0' || $ag_id =='')
                {
                    $sqlgetuser_agencies = DB::select("select agency_id from [$db_name].[dbo].[user_agencies] where user_id =".$userID);
                    if(count($sqlgetuser_agencies)>0)
                        $agencyID   =  $sqlgetuser_agencies[0]->agency_id;
                }
                else{
                        $agencyID   =  $ag_id;
                }
            }
            else if($usr_typ == "agency_user")
            {
                if($ag_id =='0' || $ag_id =='')
                {
                    $cw_parentid = DB::select("select case_worker_parent_user_id from [$db_name].[dbo].[user_accounts] where user_id =".$userID);
                    $cw_par_id = $cw_parentid[0]->case_worker_parent_user_id;                    
                    $sqlgetuser_agencies =  DB::select("select agency_id from [$db_name].[dbo].[user_agencies] where user_id=".$cw_par_id);
                    if(count($sqlgetuser_agencies) >0)
                    {
                        $agencyID = $sqlgetuser_agencies[0]->agency_id;
                    }
                    else
                    {
                        $group_ids  =   explode(',',$gp_id);
                        for($i = 0; $i<=count($group_ids); $i++)
                        {
                            $agencyIDs = $group_ids[$i];
                            $sqlgetuser_agencies = DB::select("select agency_id from [$db_name].[dbo].[user_groups] where group_id=".$agencyIDs); 
                            if(count($sqlgetuser_agencies) > 0)
                            {                            
                                $agencyID  =   $sqlgetuser_agencies[0]->agency_id;
                            }
                        }
                    }
                }
                else
                {
                    $agencyID  =   $ag_id;
                }
            }
            else if($usr_typ == "adoptive_parent" || $usr_typ == "birth_parent" )
            {
                if($ag_id =='0' || $ag_id =='')
                {
                    $cw_tempid = DB::select("select case_worker from [$db_name].[dbo].[user_accounts] where user_id =".$userID);
                    if(count($cw_tempid) > 0)             
                        $tempUserID = $cw_tempid[0]->case_worker;   
                    if($tempUserID != '0')
                    {
                        $cw_parentusrid = DB::select("select case_worker_parent_user_id from [$db_name].[dbo].[user_accounts] where user_id =".$tempUserID);
                        $cw_parentid = $cw_parentusrid[0]->case_worker_parent_user_id; 
                        
                        $cw_ag = DB::select("select agency_id from [$db_name].[dbo].[user_agencies] where user_id =".$cw_parentid);
                        if(count($cw_ag) >0)
                        $agencyID   = $cw_ag[0]->agency_id;
                    }
                    else
                    {
                        $group_ids  =   explode(',',$gp_id);
                        for($i = 0; $i<=count($group_ids); $i++)
                        {
                            $agencyIDs = $group_ids[$i];
                            $sqlgetuser_agencies = DB::select("select agency_id from [$db_name].[dbo].[user_groups] where group_id=".$agencyIDs); 
                            if(count($sqlgetuser_agencies) > 0)
                            { 
                                $agencyID           =   $sqlgetuser_agencies[0]->agency_id;
                            }
                        }
                    }
                }
                else{
                    $agencyID   =  $ag_id;
                }
            }            
            if(trim($agencyID) == '' || trim($agencyID) == 0)
            {
                $agencyID   =   $ag_id;
            }
        }                
        return $agencyID;
    } // fn getAgencyIDAnyUser() end
    
    
    
    
    
    
    function getUserData($user_id,$db_name)
    {   
        $this->userId = $user_id;
        $this->connId = $this->UserConnid($this->userId);
        $agencyid   =  $_SESSION["agencyId"];
        $c_account_key  =   $this->get_c_acc_key($agencyid);
        $this->coupleConnId = $this->getCoupleConnectionID($c_account_key,$this->connId);
        
        $loggedin_user_type = $this->userGetUserType($user_id);
        if( $loggedin_user_type == 'adoptive_parent' || $loggedin_user_type == 'birth_parent'){
            
        if( isset($_SESSION['session_caseId']) && $_SESSION['session_caseId'] != ''){ 
            $connectionId = $_SESSION['session_caseId'];
        }
        else{
            $dbContact  =   $this->getdb();
            $connIdSql = "SELECT [".$dbContact."].dbo.udf_FindLatestConnectionID('".$this->connId."') AS connectionId";   
            $res = mssql_query($connIdSql);
            $rs = mssql_fetch_array($res);
            $connectionId = $rs['connectionId'];
        }
        $this->connectionId = $connectionId;
        $this->groupId = $_SESSION['session_groupId'];
        $roles_params = array("userId" => $this->userId ,
                              "connId" => $this->connId,
                              "coupleConnId" => $this->coupleConnId,
                              "connectionId" => $this->connectionId ,
                              "groupId" => $this->groupId
                              );
        }
        else{

        $this->userId = $_REQUEST['view_user_id'];
        $this->connId = $this->UserConnid($this->userId);
        $this->coupleConnId = $_REQUEST['coupleconn_id'];
        $this->connectionId = $_REQUEST['case_id'];
        $this->groupId = $_REQUEST['view_user_group_id'];
        $roles_params = array("userId" => $this->userId ,
                              "connId" => $this->UserConnid($this->connId),
                              "coupleConnId" => $this->coupleConnId,
                              "connectionId" => $this->connectionId,
                              "groupId" => $this->groupId
                              );
        }
        return $roles_params;
    }
    
    
   
    
    function tokenGeneration($request,$usertype,$agencyid,$mapuserid,$db_name,$usrid,$c_acc_key) // irriscentral userid
    {
        echo $input_username = $request->username;
        return $input_username;
        exit;
        $input_passwd   = $request->password;
        
        // $token = JWTAuth::attempt($credentials);                        
        //$input = $request->all();                                
        //$payload = JWTFactory::sub(123)->aud('foo')->foo(['bar' => 'baz'])->make();
        //$token = JWTAuth::encode($payload);                               
                                                             
        $newClaims = [ 
                        'sub' => $mapuserid,           
                        'username' => $input_username,
                        'password' => $input_passwd,
                        'usertype' =>$usertype,
                        'agencyid' => $agencyid,
                        'mapuserid' => $mapuserid,
                        'db_name' => $db_name,
                        'login_db_userid' =>$usrid,
                        'c_acc_key' => $c_acc_key
                    ];

        $payLoad = JWTFactory::make($newClaims);
        $token   = JWTAuth::encode($payLoad);
          
 
//                exit;
        //$to = JWTAuth::decode($token);
        //echo "<br>".$to['sub'].",".$to['username'].",".$to['password'].",".$to['usertype'].",".$to['agencyid'];
        
        return $token;
        
    } // fn tokenGeneration() end
    
    
    
    
    
    
function userModAccess($user_type,$db_name)
{
    switch ($user_type)
    {
        case "admin":
        {
            $main_module_access = array("console","messages","users","documents","directory","Billing","eventscalendar","formmaker","tools","resources","tasks","help","logout");
        } break;
        case "attorney":
        case "agency":
        {
           $main_module_access = array("dashboard","messages","cwhome","documents","training","account_info","users","payment","eventscalendar","formmaker","resources","tasks", "Report", "help");
        } break;
        case "agency_user":
        {          
            $caseSecurityValFin = 5;
            $loginuserid   = Session::get('session_logged_user_id');//$_SESSION['session_logged_user_id'];           
            $cwIDFullValFin = $loginuserid."_cw_cwsecurityfinancial";
            $sqlcwqy = DB::select("SELECT useroption, setting FROM  [$db_name].[dbo].[system_settings] WHERE useroption='".$cwIDFullValFin."'"); 
            if(count($sqlcwqy) > 0)
            { 
                $caseSecurityValFin   =   $sqlcwqy[0]->setting;
            }
 
            $cwPermissionVal    =   $loginuserid."_cw_cwsecurity";
            $cwSecurityExe = DB::select("SELECT setting FROM [$db_name].[dbo].[system_settings] WHERE useroption='".$cwPermissionVal."'"); 
            if(count($cwSecurityExe) > 0)
            { 
                $csSecurityVal   =   $cwSecurityExe[0]->setting;
            }
                
            if ($caseSecurityValFin == 5) 
            {                
                if($csSecurityVal == '1'){
                    $main_module_access = array("cwhome","messages","documents","training","account_info","users","eventscalendar","resources", "cw_tasks", "Report","help");
                }else{
                    $main_module_access = array("cwhome","messages","documents","training","account_info","users","eventscalendar","resources", "Report","help");
                }
            }
            else 
            {                
                if($csSecurityVal == '1'){
                    $main_module_access = array("cwhome","messages","documents","training","account_info","users","payment","eventscalendar","resources", "cw_tasks", "Report","help");
                }else{
                    $main_module_access = array("cwhome","messages","documents","training","account_info","users","payment","eventscalendar","resources", "Report","help");
                }
            }
        } break;
        case "adoptive_parent":
        {
            $main_module_access = array("dashboard","messages","documents","account_info","eventscalendar","resources","ssrs_reports","help","clientdata","logout");
        } break;
        case "birth_parent":
        {
             $main_module_access = array("dashboard","messages","documents","account_info","eventscalendar","resources","ssrs_reports","help","logout");
        } break;
        case "all":
        {
            $main_module_access = array("dashboard","activity","messages","clients","agency_profile","documents","account_info","console","users","directory","eventscalendar","formmaker","tools","operation", "resources","help");
        } break;
    }
    return $main_module_access;
} // fn userModAccess() end

    
    
function get_c_acc_key($agencyID,$db_name,$usertype)
{
    /***condition from portal admin login**/
//    if($usertype == 'admin')
//    {
//        $agencyID = $_SESSION['agency_client'];
//    }
    /**************************************/
    $data_fetch          = DB::select("SELECT c_account_key FROM  [$db_name].[dbo].[user_agencies] WHERE agency_id=$agencyID");  
    $c_account_keyDrop   = $data_fetch[0]->c_account_key;
    return $c_account_keyDrop;
}
    
    

    function login_lar_tokenGeneration(Request $request) // irriscentral userid
    {
        
        $mapuserid          = $request->mapuserid;
        $input_username     = $request->input_username;
        $input_passwd       = $request->input_passwd;
        $usertype           = $request->usertype;
        $agencyid           = $request->agencyid;
        $db_name            = $request->DBName;
        $c_acc_key          = $request->c_acc_key;
        $login_db_userid    = $request->login_db_userid;
        
        // $token = JWTAuth::attempt($credentials);                        
        //$input = $request->all();                                
        //$payload = JWTFactory::sub(123)->aud('foo')->foo(['bar' => 'baz'])->make();
        //$token = JWTAuth::encode($payload);                               
                                                             
        $newClaims = [ 
                        'sub' => $mapuserid,           
                        'username' => $input_username,
                        'password' => $input_passwd,
                        'usertype' =>$usertype,
                        'agencyid' => $agencyid,
                        'mapuserid' => $mapuserid,
                        'db_name' => $db_name,
                        'login_db_userid' =>$login_db_userid,
                        'c_acc_key' => $c_acc_key
                    ];

        $payLoad = JWTFactory::make($newClaims);
        $token   = JWTAuth::encode($payLoad);
          
 
//                exit;
        //$to = JWTAuth::decode($token);
        //echo "<br>".$to['sub'].",".$to['username'].",".$to['password'].",".$to['usertype'].",".$to['agencyid'];
        
        $token_res = [ "status" => "success" , "response" => 200, "token" => "$token"];
        
        return $token_res;
       
    } // fn tokenGeneration() end
    


    
    
} // class end


?>