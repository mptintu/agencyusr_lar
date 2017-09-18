<?php

function logdetails($query)
{
    global $path;
    
    $ip = $_SERVER['REMOTE_ADDR']; 
    $log = fopen($path['log4php']."authenticate".date('Y-m-d').".txt", 'a+');
    fwrite($log, "\n ipAddress of my machine: ".$ip. " /* =============================*/".$query."\n"."/* ========================== */ \n");
    fclose($log);
}

?>