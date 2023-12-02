<?php
error_reporting(E_ALL);
///----------------Unix/Windows---------------
function getagent($host,$community) {
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

?>
