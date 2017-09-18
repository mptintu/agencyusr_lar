<?php
/*
All user relates files are finnaly their documents
A file can be COPIED/MOVED either from $_FILES to a dest or
from one location to another
A file is always READ from a location
*/
namespace App\ExternalClasses;
use Illuminate\Support\Facades\DB;

class FileServerManager{
    
    private $fileType = null;
    private $basePath = null;
    private $StorageDir = null;
    private $default_StorageDir = null;
    private $connId  = null;
    private $agencyId = null;
    private $connectionId = null;
    private $loggedInUserId = null;
    private $destination_file = null;
    private $source_file = null;
    private $sub_storage_dir = null;
    private $sub_storage_dir_params = null;
    
    
    public function __construct($agencyId=NULL)
    {        
        //global $path; 
       
       $this->agencyId = AGENCYID;
            
        /*$userOption =   $agencyId."fileserverpath";
        $getAgencyFilePathQry = "select * from [$db_name].[dbo].[system_settings] where useroption = '$userOption'";
        $agencydetails_list = DB::select($query);
        
        $getAgencyFilePathQry = "select * from system_settings where useroption = '$userOption'"; //if the agency decides another path
        $getAgencyFilePathExe = mssql_query($getAgencyFilePathQry);
        if(mssql_num_rows($getAgencyFilePathExe) >0){
            $getAgencyFilePathRs =   mssql_fetch_row($getAgencyFilePathExe);
            $this->basePath = $getAgencyFilePathRs[0];
        }else{
            $this->basePath = $path["fileLocation"];
        } 
        */       
    }

    public function findAgency($agencyId,$db_name)
    {
        
        $userOption =   $agencyId."fileserverpath";
        $getAgencyFilePathQry = "select * from [$db_name].[dbo].[system_settings] where useroption = '$userOption'";
        $getAgencyFilePathExe = DB::select($getAgencyFilePathQry);
        if(count($getAgencyFilePathExe) >0)
        {
             $this->basePath = $getAgencyFilePathExe[0]->system_settings;
        }
        else
        {
            $this->basePath = FILELOCATION;
        }
        
    }

    public function setDefaultStorageDir($default_storage_dir=null){
        global $logClassObj;  

        if($this->fileType){
            if($default_storage_dir){
                 $this->default_StorageDir = $default_storage_dir; 
            }
            else{
             switch($this->fileType){
                  case 'Templates':   
                        $this->default_StorageDir = $this->basePath."map_shared/userhome/printcontent/";                                 
                    break;

                  case "TempFiles":
                        $this->default_StorageDir = $this->basePath."userhome/temp/";
                    break;

                  case 'Messages':
                        $this->default_StorageDir = $this->basePath."userhome/messages/";
                    break; 

                  case 'UserSignatures':
                        $this->default_StorageDir = $this->basePath."payment/onlinesignature/";
                    break;

                  case 'Documents': 
                        $this->default_StorageDir = $this->basePath."map_shared/userhome/users/";
                    break;

                  case 'PDFImages':                  
                        $this->default_StorageDir = $this->basePath."userhome/PDFImages/";    
                    break;           
                }    
            }
             
        }       
    /*$logClassObj->setModule('MAP');
    $logClassObj->setSubmodule('FileServerManager');
    $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."---------------------------Method: setDefaultStorageDir-----------------------------");
    $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."file Type :: ".$this->fileType,"INFO");
    $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Connid property of Class :: ".$this->connId,"INFO");
    $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Storage Directory :: ".$this->StorageDir,"INFO"); 
    */
    } 

    public function setFileType($file_type){
        /*if (userGetUserType($_SESSION['session_logged_user_id'])=="agency" || userGetUserType($_SESSION['session_logged_user_id'])=="agency_user"){
            $this->connId = null;
            }
            */
        $this->fileType = $file_type;
        $this->setStorageDir();
        $this->setDefaultStorageDir();
    }    
    public function getFileType(){
        return $this->fileType;
    }
    public function setBasePath($filePath){
        $this->basePath = $filePath;
    }
    public function getBasePath(){
        return $this->basePath ;
    }
    public function setConnId($connId){ 
        global $logClassObj;
        if($connId>0) {
            $connId = $connId * -1;
            }
        $user_id =  get_UserId_from_ConnId($connId);
        if (userGetUserType($user_id) =='agency' || userGetUserType($user_id) =='agency_user'){
             $this->connId = null;   
            }
         
        else{
            $this->connId = abs($connId);
        }
           
        $this->setStorageDir();

        /*$logClassObj->setModule('MAP');
        $logClassObj->setSubmodule('FileServerManager');
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."---------------------------Method: setConnid-----------------------------");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Input Connid  :: ".$connId,"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."UserId  :: ".$user_id,"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."UserType  :: ".userGetUserType($user_id),"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Class Connid  :: ".$this->connId ,"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Storage Dir :: ".$this->StorageDir,"INFO");  

        */
    }
    public function getConnId(){
        return abs($this->connId);
    }
    public function setConnectionId($connectionId){
        $this->connectionId = $connectionId;
    }
    public function getConnectionId(){
        return $this->connectionId;
    }
    public function setLoggedInUserId($loggedInUserId){
        $this->loggedInUserId = $loggedInUserId;
    }
    public function getLoggedInUserId(){
        return $this->loggedInUserId;
    }
    public function setAgencyId($agencyId='')
    {   
        if(trim($agencyId) != ''  )
            $this->agencyId = $agencyId;
        else
            $this->agencyId = 'common';
    } 
    public function getAgencyId()
    {
        return $this->agencyId;
    }   
    public function setDestinationFile($dest)
    {        
        $this->destination_file = $dest;
    }
    public function getDestinationFile()
    {  
        return $this->destination_file;
    }

      public function setSourceFile($source)
    {        
        $this->source_file = $source;
    }
    public function getSourceFile()
    {  
        return $this->source_file;
    }

    public function setSubStorageDirParams($params = null){
         $this->sub_storage_dir_params = $params;
         $this->setSubStorageDir();
    }

    public function setSubStorageDir()
    {  
        global $logClassObj;    
        if($this->fileType && $this->StorageDir){
            
            switch($this->fileType){

                  case 'UserSignatures':                             

                   if($this->connId){
                        $this->sub_storage_dir = $this->folderCreation($this->StorageDir.'Ref_'.$this->sub_storage_dir_params);                          
                        }
                 
                break;           
                }  
        }
       
 /*       $logClassObj->setModule('MAP');
        $logClassObj->setSubmodule('FileServerManager');
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."---------------------------Method: setSubStorageDir-----------------------------");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."file Type :: ".$this->fileType,"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Connid property of Class :: ".$this->connId,"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Storage Directory :: ".$this->StorageDir,"INFO");
*/
        return $this->sub_storage_dir;
    }

    public function getSubStorageDir()
    {  
        return $this->sub_storage_dir;
    }


public function getStorageDir($file_type = null,$connId = null){

    global $logClassObj;

    if($file_type == null)
        $file_type = $this->fileType;

    if($connId == null)
        $connId = $this->connId;
 

    switch($file_type){
        case 'Templates': 

        case "TempFiles":

        case 'Messages':

            $this->StorageDir = $this->basePath.$this->agencyId."/".$file_type."/";      
                
        break;         

        case 'UserSignatures':

        case 'Documents':

        case 'PDFImages':        

        if($connId){
            $index = floor($connId/10000);
            $this->StorageDir = $this->basePath.$this->agencyId."/".$file_type."/Client/".$index."/".$connId."/";  

           // $this->StorageDir = $this->basePath.$this->agencyId."/".$file_type."/".$connId."/";      
        }
            
        else
            $this->StorageDir = $this->basePath.$this->agencyId."/".$file_type."/Agency/"; 
        break;     
        }

  /*      $logClassObj->setModule('MAP');
        $logClassObj->setSubmodule('FileServerManager');
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."---------------------------Method: getStorageDir-----------------------------");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."file Type :: ".$file_type,"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Connid property of Class :: ".$connId,"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Storage Directory :: ".$this->StorageDir,"INFO");      
*/
   return $this->StorageDir;      
}    
public function setStorageDir($otherPath=null)
        { 
        global $logClassObj;    
        if($this->fileType){
            $this->StorageDir = $this->folderCreation($this->basePath.$this->agencyId);
            $this->StorageDir = $this->folderCreation($this->StorageDir.$this->fileType);

            switch($this->fileType){
                  case 'Templates':                                    
                  
                  case "TempFiles":

                  case 'Messages':

                  break; 

                  case 'UserSignatures':

                  case 'Documents': 

                  case 'PDFImages':                  

                   if($this->connId){
                        $index = floor($this->connId/10000);     
                        $this->StorageDir = $this->folderCreation($this->StorageDir.'Client');    
                        $this->StorageDir = $this->folderCreation($this->StorageDir.$index);  
                        $this->StorageDir = $this->folderCreation($this->StorageDir.$this->connId); 
                        }
                          
                   else
                        $this->StorageDir = $this->folderCreation($this->StorageDir.'Agency'); 
                break;           
                }  
        }

       
        /*$logClassObj->setModule('MAP');
        $logClassObj->setSubmodule('FileServerManager');
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."---------------------------Method: setStorageDir-----------------------------");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."file Type :: ".$this->fileType,"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Connid property of Class :: ".$this->connId,"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Storage Directory :: ".$this->StorageDir,"INFO");
      
      */
        /*$connId = $this->getConnId();
            if(abs($connId))
                $this->StorageDir = $this->folderCreation($this->StorageDir.abs($connId));
                */
        /*
        if(!empty($otherPath) && count($otherPath)>0){
            for($i=0;$i<count($otherPath);$i++){
                $this->StorageDir = $this->folderCreation($this->basePath.$otherPath[$i]);
                }
            } */            
        }

public function copyFile($source_file_name,$dest_file_name,$source_file_type='',$source_file_id='',$source_file_path='')
    {
    global $Data,$path,$logClassObj;
    $flag = 0;

    switch($this->fileType){
        case 'Documents':
   
         if($source_file_type == 'Templates' || $source_file_type =='Documents'|| $source_file_type =='TempFiles'){  
                    
            $connId = $this->connId; 

            $this->setDestinationFile($this->StorageDir.$dest_file_name); 

            if(trim($source_file_path) == ''){
                $source_file_path = $this->getStorageDir($source_file_type,$connId);
                $this->setSourceFile($source_file_path.$source_file_name); 
                }
            else
                $this->setSourceFile($source_file_path.$source_file_name);   

            if(!file_exists($this->destination_file))
            $flag = copy($this->source_file, $this->destination_file);
            }


        else if($source_file_type == 'upload'){
            $this->setDestinationFile($this->StorageDir.$dest_file_name); 
            $flag = move_uploaded_file($source_file_name, $this->destination_file);
            }

        else if($source_file_type == 'vault_upload'){
            $this->setDestinationFile($this->StorageDir.$dest_file_name); 
            $this->setSourceFile($source_file_path.$source_file_name); 
            $flag = copy($this->source_file, $this->destination_file);            
            }
        break;

        case 'Templates': 
    
        $agencyid = $this->agencyId;
        $user_id = $this->getLoggedInUserId();
 
        $currentTemplateDate = date("Y-m-d");                        
        $dest_file_name = str_replace('\'','',$dest_file_name);
      
        $Data->columns = array("agency_id", "form_type","template_name",  "print_content", "created_by", "created_on" );
        $Data->values = array($agencyid, "P", $dest_file_name, $agencyid, $user_id, $currentTemplateDate);
  
        $Data->updateData(formmaker_printform,INSERT);
   
        $Data->data = array("print_id");
        $Data->where="agency_id = '$agencyid' and created_by= '$user_id'";
        $Data->order = "";
        $qry_obj=$Data->getData(formmaker_printform);
        $seqNumber = 0;

        while($qry_res=mssql_fetch_row($qry_obj)) {
             $templateFormId = $qry_res[0];
             if($dest_file_name == $qry_res[3]){
                 ++$seqNumber;
                }
            }
    
        $dest_file_name = str_replace('/', ' ', $dest_file_name)."_".$agencyid."_".$currentTemplateDate."_".$seqNumber.".pdf";     
        $Data->data= array("filename");
        $Data->value= array($dest_file_name);
        $Data->where = "print_id='$templateFormId'";
        $Data->updateData(formmaker_printform,UPDATE);
                 
        $this->setDestinationFile($this->StorageDir.$dest_file_name);
            
        $flag = move_uploaded_file($source_file_name, $this->destination_file); 

        break; 


        case "PDFImages" :

         break;

         case "TempFiles":
            if($source_file_type == 'Messages' ){
                $this->setDestinationFile($this->StorageDir.$dest_file_name);
                $flag = move_uploaded_file($source_file_name, $this->destination_file); 
                }
                
         break;

         case 'Messages':
            if($source_file_type == 'TempFiles'){

                $this->setFileType($source_file_type);
                $this->setSourceFile($this->StorageDir.$source_file_name);             

                $this->setFileType('Messages');           
                $this->setDestinationFile($this->StorageDir.$dest_file_name); 

               copy($this->source_file, $this->destination_file);
            }
         break;
        }

      /*  $logClassObj->setModule('MAP');
        $logClassObj->setSubmodule('FileServerManager');
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."/------------Method : copyFile-----------------/","INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Source File Type :: ".$source_file_type,"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Destination File Type :: ".$this->fileType,"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Source File :: ".$this->source_file,"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Source File as parm to function :: ".$source_file_name,"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Destination File :: ".$this->destination_file,"INFO");   
  */
    return $flag;        
    }


public function deleteFile($fileid,$source_file_type,$task_type){

    global $Data,$System,$path,$RolesParams,$logClassObj;
    $filedetails = array();

    switch($this->fileType){
          case 'Templates':                                    
          
          case "TempFiles":

          case 'Messages':                 

          case 'UserSignatures':                 

          case 'PDFImages':                  

            break; 

          case 'Documents': 

            if($source_file_type == 'document'){

                $filedetails = $this->readFile($fileid);                 
                $source_file_path = $filedetails['filefullpath'];  
                if($task_type!="delete2" && file_exists($source_file_path)){
                    unlink($source_file_path);                             
                    }
                //update or delete from upload table                        
                $Data->data = array("file_name");
                $Data->where = "upload_id='$fileid'";
                $Data->order = "";

                if($task_type == "remove"){
                    $Data->value = array("");
                    $Data->updateData(upload, UPDATE);
                    }
                else {
                    $Data->deleteData(upload);
                    } 
                 
                }                                           
          
          break;             
        }

   /*     $logClassObj->setModule('MAP');
        $logClassObj->setSubmodule('FileServerManager');
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."/------------Method : deleteFile-----------------/","INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Source File Type :: ".$source_file_type,"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Destination File Type :: ".$this->fileType,"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Source File path:: ".$source_file_path,"INFO");        
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Task type :: ".$task_type,"INFO"); 
*/
    }    

public function readFile($db_name,$fileid='',$source_file_type='template',$user_id='',$role_conn_id_val='',$role_case_id_val='')
    { 
    global $Data,$System,$path,$RolesParams,$logClassObj,$logClassObj_fileservercleanup;
    $file_prop_arr = array();
    $agencyid = $this->agencyId;
    switch($this->fileType){
        case 'Templates': 
           $Data->data = array("*");
            $Data->where="print_id = '$fileid'";
            $Data->order = "";
            $templateFileQuery=$Data->getData(formmaker_printform);
            while($templateFileResult = mssql_fetch_array($templateFileQuery)){
                $file_prop_arr['filename'] = $templateFileResult[7];
                $file_prop_arr['filetype'] = $templateFileResult[2];
                }

        break;

        case 'Documents':
            /*
            if(!$role_case_id_val)
                $role_case_id_val     = $RolesParams->getConnectionIdValue();
            if(!$role_conn_id_val)
                $role_conn_id_val     = $RolesParams->getConnIdValue();

            $this->setConnId($role_conn_id_val); 

            */
            if($source_file_type == 'form' || $source_file_type == 'template'){
                
                $tablename = $source_file_type == 'form' ? 'signature_client_form' : 'signature_client_doc';
                $sqlQuery = "SELECT docname,ConnId from $tablename WHERE user_id=$user_id and template_id = $fileid and connId='$role_conn_id_val' and connectionId='$role_case_id_val'  order by id desc";
                $result = mssql_query($sqlQuery);

                while ($myrow = mssql_fetch_row($result)) {
                    $file_prop_arr['filename'] = $myrow[0];
                    $this->setConnId($myrow[1]); 
                    }
                }
            else if($source_file_type == 'document'){  

                $Data->data = array("file_name,user_id");
                $Data->where = "upload_id='$fileid'";
                $Data->order = "";
                $result = $Data->getData(upload);
                if($myrow = mssql_fetch_row($result))
                    {                   
                     $file_prop_arr['filename'] = $myrow[0];
                     $this->setConnId(UserConnid($myrow[1])); 
                    }
                mssql_free_result($result);
                }  
             else if($source_file_type == 'profile_image'){  

                $Data->data = array("photo,ConnId");
                $Data->where = "user_id='$user_id'";
                $Data->order = "";
                $result = $Data->getData(user_accounts);
                if($myrow = mssql_fetch_row($result))
                    {  
                     if (strlen(trim($myrow[0])) == 0) {
                         $file_prop_arr['filename'] = 'nophoto.jpg';
                         }   
                     else {             
                        $file_prop_arr['filename'] = $myrow[0];
                        }
                        $this->setConnId($myrow[1]); 
                    }
                mssql_free_result($result);
                }

                else if($source_file_type == 'agency_logo')
                {  

                    $getAgencyLogo = "select logo from [$db_name].[dbo].[user_agencies] 
                    where agency_id = '$agencyid'";
                    $getAgencylog = DB::select($getAgencyLogo);
                    if(count($getAgencylog) >0)
                    {
                         $file_prop_arr['filename'] = $getAgencylog[0]->logo;
                    }
                    else
                        $file_prop_arr['filename'] = 'blank.jpg';

                   

/*
                $Data->data = array("logo");
                $Data->where = "agency_id='$agencyid'";
                $Data->order = "";
                $result = $Data->getData(user_agencies);
                if($myrow = mssql_fetch_row($result))
                    {  
                     if (!$myrow[0] || $myrow[0]=='') {
                         $file_prop_arr['filename'] = 'blank.jpg';
                         }   
                     else {             
                        $file_prop_arr['filename'] = stripslashes($myrow[0]);
                        }
                       
                    }
                mssql_free_result($result);

                */
                }

            else if($source_file_type == 'upload'){
               $sqlQuery = "select                               
                               file_name,
                               ConnId, 
                               ConnectionId,
                               user_id,                               
                               description
                               from upload where upload_id= '$fileid'";
               $result = mssql_query($sqlQuery);

                while ($myrow = mssql_fetch_row($result)) {
                    $file_prop_arr['filename'] = $myrow[0];
                    $this->setConnId($myrow[1]); 
                    }
               mssql_free_result($result);      
            }
            else if($source_file_type == 'upload_version'){
               $sqlQuery = "select 
                                ua.upload_id,   
                                uv.versionname,
                                ua.ConnId
                            from upload_version uv 
                            LEFT JOIN 
                                upload ua 
                            on uv.filename = ua.file_name
                                where 
                                 uv.versionname IS NOT NULL 
                                 AND LEN(uv.versionname)>0
                                 AND uv.versionname =  '$fileid'";
               $result = mssql_query($sqlQuery);
               if(mssql_num_rows($result)>0){
                while ($myrow = mssql_fetch_row($result)) {
                    $file_prop_arr['filename'] = $myrow[1];
                    $this->setConnId($myrow[2]); 
                    }
                }
                else{

                    mssql_free_result($result);   
                     $sqlQuery = "select 
                                upload_id,   
                                file_name,
                                ConnId
                            from upload  
                                where 
                                 file_name IS NOT NULL 
                                 AND LEN(file_name)>0
                                 AND file_name =  '$fileid'";
                   $result = mssql_query($sqlQuery);
                   if(mssql_num_rows($result)>0){
                    while ($myrow = mssql_fetch_row($result)) {
                        $file_prop_arr['filename'] = $myrow[1];
                        $this->setConnId($myrow[2]); 
                            }
                        }
                }
                
                mssql_free_result($result);    
            }  

        break;            
        }
    $this->getStorageDir();

    
    //$logClassObj_fileservercleanup->commonWriteLogInOne(date('Y-m-d h:i:s')."---------------------Start--------------------------");

    if($file_prop_arr['filename'] == 'nophoto.jpg' || $file_prop_arr['filename'] == 'blank.jpg'){            
            $file_prop_arr['filepath'] =  $this->basePath.'Default/';
            $file_prop_arr['filefullpath'] =  $file_prop_arr['filepath'].$file_prop_arr['filename'];
            $file_prop_arr['httppath'] =  str_replace($path['serloc'],$path['wwwloc'],$file_prop_arr['filefullpath']);
            }
        else{
             if(file_exists($this->StorageDir.$file_prop_arr['filename'])){
                //$logClassObj_fileservercleanup->commonWriteLogInOne(date('Y-m-d h:i:s')."FILE found in Current Path","INFO");
                //$logClassObj_fileservercleanup->commonWriteLogInOne(date('Y-m-d h:i:s')."--------------","INFO"); 
                }
            else if(file_exists($this->default_StorageDir.$file_prop_arr['filename'])){

                //$logClassObj_fileservercleanup->commonWriteLogInOne(date('Y-m-d h:i:s')."FILE found in Default Storage Path","INFO");
               // $logClassObj_fileservercleanup->commonWriteLogInOne(date('Y-m-d h:i:s')."--------------","INFO"); 
               
                copy($this->default_StorageDir.$file_prop_arr['filename'],$this->StorageDir.$file_prop_arr['filename']);      
                }
            else if(file_exists($path['serloc'].'map_shared/userhome/users/'.$file_prop_arr['filename'])){

                //$logClassObj_fileservercleanup->commonWriteLogInOne(date('Y-m-d h:i:s')."FILE found in userhome/users","INFO");
                //$logClassObj_fileservercleanup->commonWriteLogInOne(date('Y-m-d h:i:s')."-----------------","INFO"); 

                copy($path['serloc'].'map_shared/userhome/users/'.$file_prop_arr['filename'],$this->StorageDir.$file_prop_arr['filename']);
                
                }
            else{
                //$logClassObj_fileservercleanup->commonWriteLogInOne(date('Y-m-d h:i:s')."FILE not found","INFO");
                //$logClassObj_fileservercleanup->commonWriteLogInOne(date('Y-m-d h:i:s')."--------------","INFO");    
                }

 /*           $logClassObj_fileservercleanup->commonWriteLogInOne(date('Y-m-d h:i:s')."Source File Type :: ".$source_file_type,"INFO");
            $logClassObj_fileservercleanup->commonWriteLogInOne(date('Y-m-d h:i:s')."File Type :: ".$this->fileType,"INFO");    
            $logClassObj_fileservercleanup->commonWriteLogInOne(date('Y-m-d h:i:s')."File Name:: ".$file_prop_arr['filename'],"INFO");
            $logClassObj_fileservercleanup->commonWriteLogInOne(date('Y-m-d h:i:s')."Current Path:: ".$this->StorageDir,"INFO");
            $logClassObj_fileservercleanup->commonWriteLogInOne(date('Y-m-d h:i:s')."Default Path:: ".$this->default_StorageDir,"INFO");
            $logClassObj_fileservercleanup->commonWriteLogInOne(date('Y-m-d h:i:s')."Userhome Path:: ".$path['serloc'].'userhome/users/',"INFO");
            $logClassObj_fileservercleanup->commonWriteLogInOne(date('Y-m-d h:i:s')."Connid:: ".$this->getConnId(),"INFO");    
*/
            $file_prop_arr['filepath'] =  $this->StorageDir;
            $file_prop_arr['filefullpath'] =  $file_prop_arr['filepath'].$file_prop_arr['filename'];
            $file_prop_arr['httppath'] =  str_replace(DOC_PATH,HTTP_PATH,$file_prop_arr['filefullpath']);

            }
        $file_prop_arr['ConnId'] =  $this->getConnId();
        
        //$logClassObj_fileservercleanup->commonWriteLogInOne(date('Y-m-d h:i:s')."---------------------End--------------------------");

   /*     $logClassObj->setModule('MAP');
        $logClassObj->setSubmodule('FileServerManager');
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."/------------Method : readFile-----------------/","INFO");
        
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Query :: ".$sqlQuery,"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Connid :: ".$file_prop_arr['ConnId'],"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Source File Type :: ".$source_file_type,"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."File Type :: ".$this->fileType,"INFO");  
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."File Name:: ".$file_prop_arr['filename'],"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."File Path:: ".$file_prop_arr['filepath'],"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."File Full Path:: ".$file_prop_arr['filefullpath'],"INFO");
        $logClassObj->commonWriteLogInOne(date('Y-m-d h:i:s')."Http Path:: ".$file_prop_arr['httppath'],"INFO");              
*/
    return $file_prop_arr;        
    }
   
    
    public function folderCreation($name){
        if(!file_exists($name))
        {
            mkdir($name, 0777);
            chmod($name, 0777);
        }
        return $name."/";
    }
    public function getFileBasePath($file_type=NULL){
        if($file_type == NULL)
            $file_type = $this->fileType;
        return $this->basePath.$this->agencyId."/".$file_type."/";
    }
        
}
 
//Object Creation
/*
$fileServerLocationObj = new MAPFileServerManager($_SESSION['agencyId']);
if(!isset($_SESSION['agencyId'])){      
    $subDomain = $_SERVER[SERVER_NAME];

    $agencydetails = "select agency_id from user_agencies where c_account_key =
        (SELECT top 1 SiteID FROM [IrrisCentral].[dbo].[lutPrimaryAgency] where [subDomain]='$subDomain')";
    $result_lutagencydetails = mssql_query($agencydetails);
    if (mssql_num_rows($result_lutagencydetails)) {
        $agencyRow = mssql_fetch_array($result_lutagencydetails);
        $_SESSION['agencyId'] = $agencyRow['agency_id'];
    }
}$logClassObj_fileservercleanup = new log4PhpClass();
$logClassObj_fileservercleanup->setAgencyID($_SESSION['agencyId']);

    $fileServerLocationObj->setAgencyId($_SESSION['agencyId']);   
    $fileServerLocationObj->setLoggedInUserId($_SESSION['session_logged_user_id']); 
    if (userGetUserType($_SESSION['session_logged_user_id'])=="adoptive_parent" || userGetUserType($_SESSION['session_logged_user_id'])=="birth_parent"){
         $fileServerLocationObj->setConnId(UserConnid($_SESSION['session_logged_user_id']));
        } 

   // $logClassObj_fileservercleanup = new log4PhpClass();
   // $logClassObj_fileservercleanup->setAgencyID($_SESSION['agencyId']);
    $logClassObj_fileservercleanup->setLevelLimit("TRACE");
    $logClassObj_fileservercleanup->setLevel("DEBUG");    
    $logClassObj_fileservercleanup->setModule("FileServerManager");
    $logClassObj_fileservercleanup->setSubmodule("missedfiles");       
    
    */

//$fileServerLocationObj = new FileServerManager();

?>