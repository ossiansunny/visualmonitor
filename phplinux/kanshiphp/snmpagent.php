<?php
require_once "mysqlkanshi.php";
require_once "hostping.php";
error_reporting(E_ALL);
///----------------Unix/Windows---------------
function getagent($host,$community) {
  $pingsw=hostping($host);
  if ($pingsw==0){
    /// sysLocation.0にok サイト内全てok ng サイト内にpingエラーあり 
    $resstr = snmpget($host, $community, ".1.3.6.1.2.1.1.6.0");  
    if($resstr){
      $str=explode(':',$resstr);
      $strval=trim($str[1]);    
      return $strval;
    }else{
      writelogd('snmpagent.php','snmpget failed '.$host.' '.$community);
      return '';  /// return empty
    }
  }else{
    return '';
  }
}

function putagent($host,$community,$value) {
  $pingsw=hostping($host);
  if ($pingsw==0){
    if($value=='ok'){
      $resstr = snmpset($host, $community, ".1.3.6.1.2.1.1.6.0" , "s", "ok");
    }else{
      $resstr = snmpset($host, $community, ".1.3.6.1.2.1.1.6.0" , "s", "ng");
    }
    if($resstr){
      return true;
    }else{
      return false;
    }
  }else{
    return false;
  }
}
?>

