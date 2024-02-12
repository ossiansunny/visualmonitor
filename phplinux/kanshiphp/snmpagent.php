<?php
error_reporting(E_ALL);
///----------------Unix/Windows---------------
function getagent($host,$community) {
  /// sysLocation.0にok サイト内全てok ng サイト内にpingエラーあり 
  $resstr = snmpget($host, $community, ".1.3.6.1.2.1.1.6.0");
  $str=explode(':',$resstr);
  $strval=trim($str[1]);
  return $strval;
}

function putagent($host,$community,$value) {
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
}
/*
$rtnv=getagent('192.168.1.139','public');
var_dump($rtnv);
*/
?>

