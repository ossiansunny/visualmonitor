<?php
error_reporting(E_ALL & ~E_WARNING);
///-------SNMP プライベートMIBにset---------
function snmptcpportset($host,$community,$tcpport) {
  $snmparray = array();
  $snmparray = snmp2_set($host,$community,".1.3.6.1.4.1.999999.1.2.0","s",$tcpport,1000000,1);
  if (! $snmparray){
    return 1;
  }else{
    return 0;
  }
}
///-------SNMP プライベートMIBをget---------未使用
function snmptcpportget($host,$community) {
  $getvalue = snmp2_get($host,$community,".1.3.6.1.4.1.999999.1.3.0",1000000,1);
  if (! $getvalue){
    $rtnval="error";
  }else{
    if ($getvalue=="\"\""){					/// ""
      $rtnval="allok";      
    }else{
      $rtnarr=explode(':',$getvalue);				//STRING: "80" 分離
      $rtnvalsp=ltrim(rtrim(trim($rtnarr[1]),'"'),'"');         //"80"のDoubleQuoteを削除
      //$rtnval=str_replace(' ',';',$rtnvalsp);                 //これは不要
      
    }
  }
  return $rtnval;  
}
/*
$rtncde=snmptcpportset("192.168.1.22","remote","80;443;22");

$rtnval=snmptcpportget("192.168.1.22","remote");
echo PHP_EOL.'---------var_dump $rtnval<br>'.PHP_EOL;
var_dump($rtnval);
*/
?>

