<?php

require_once "hostping.php";
error_reporting(E_ALL);
///----------------Unix/Windows---------------
function snmposcheck($host,$community) {
  $pingsw=hostping($host);
  if ($pingsw==0){
    
    $resstr = snmpget($host, $community, ".1.3.6.1.2.1.1.1.0");  
    if(strpos($resstr,"Windows") != false){
      $ostype="Windows";
    }elseif(strpos($resstr,"Linux") != false){
      $ostype="Linux";
    }else{
      $ostype="Other";
    }
  }else{
    $ostype="NoRes";
  }
  return $ostype;
  
}

?>

