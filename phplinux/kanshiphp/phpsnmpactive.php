<?php
error_reporting(E_ALL & ~E_WARNING);
/// SNMP sysLocation.0 をチェック
function snmpactive($host,$community) {
  $snmparray = array();
  $snmparray = snmpget($host, $community, ".1.3.6.1.2.1.1.6.0",1000000,1);
  if (! $snmparray){
    return 1;
  }else{
    return 0;
  }
}
?>

