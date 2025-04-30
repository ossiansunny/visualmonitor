<?php
error_reporting(E_ALL & ~E_WARNING);
///-------SNMP プライベートMIBにset---------
function snmpprocessset($host,$community,$process) {
  $snmparray = array();
  $snmparray = snmp2_set($host,$community,".1.3.6.1.4.1.999999.1.4.0","s",$process,1000000,1);
  if (! $snmparray){
    return 1;
  }else{
    return 0;
  }
}
///-------SNMP プライベートMIBをget---------未使用
function snmpprocessget($host,$community) {
$getvalue = snmp2_get($host,$community,".1.3.6.1.4.1.999999.1.5.0",1000000,1);
  if (! $getvalue){
    $rtnval="error";
  }else{
    if ($getvalue=="\"\""){					/// ""
      $rtnval="allok";
      
    }else{
      $rtnval=$getvalue;					/// STRING: "oracle master"
      $rtnarr=explode(':',$getvalue);				/// oracle master
      $rtnvalsp=ltrim(rtrim(trim($rtnarr[1]),'"'),'"'); 	/// oracle;master
      $rtnval=str_replace(' ',';',$rtnvalsp);
      
    }
  }
  return $rtnval;  
}
/*
snmpprocessset("192.168.1.18","remote","httpd oracle master sshd");
snmpprocessget("192.168.1.21","remote");
*/
?>

