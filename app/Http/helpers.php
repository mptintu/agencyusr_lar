<?php


function logdetails_qry($query)
{
    $ip = $_SERVER['REMOTE_ADDR']; 
    //$log = fopen($path['log4php']."authenticate".date('Y-m-d').".txt", 'a+');
    return $ip;
//    global $path;
//    
//    
//    
//    fwrite($log, "\n ipAddress of my machine: ".$ip. " /* =============================*/".$query."\n"."/* ========================== */ \n");
//    fclose($log);
}



function pwdencryption($string)
{   
    $key = 'myadoptionportal';
    $result = '';
    for($i=0; $i<strlen($string); $i++)
    {
        $char = substr($string, $i, 1);
        $keychar = substr($key, ($i % strlen($key))-1, 1);
        $char = chr(ord($char)+ord($keychar));
        $result.=$char;
    }
    return base64_encode($result);
}




   function log_Qry($queries)
    { 
    
        $logData = '';
        for ($i=0;$i<count($queries);$i++) 
        {
            $query =  $queries[$i]['query'].'';
            $time =  date('Y-m-d H:i:s', time());
            //loop through all bindings
            for($j=0; $j<sizeof($queries[$i]['bindings']); $j++)
            {
                $queries[$i]['bindings'][$j] = $queries[$i]['bindings'][$j] == '' ? "''" : $queries[$i]['bindings'][$j];
                //replace ? with actual value
                $query .= str_replace_first($query,'?',$queries[$i]['bindings'][$j]);
            }

            //remove all new lines
            $query = trim(preg_replace( "/\r\n|\n/", "", $query));
            $newArr = array(date('Y-m-d H:i:s'), $query);
            $logData .= implode("\t",$newArr) . "\n";
        }
       
       $logData .= "from helpers";
        //write if any new data
        if($logData != ''){ 
                //open logs file if exists or create a new one
            $logFile = fopen(storage_path('logs/query_logs/'.date('Y-m-d').'_query.log'), 'a+');
            //write log to file
            fwrite($logFile, $logData);
            fclose($logFile);
        }
 
        
    }



?>