<?php
error_reporting(E_ALL & ~E_WARNING);
require_once "mysqlkanshi.php";
///-------SNMP プライベートMIBにset---------
function snmptrapset($host,$community,$process) {
  ///$processx=str_replace(';',' ',$process);
  ///$processy='"'.$processx.'"'; // snmp2_setが"を付与してくれる
  ///print($processx);
  $snmparray = array();
  $snmparray = snmp2_set($host,$community,".1.3.6.1.2.1.1.5.0","s",$process,1000000,1); /// sysName
  if (! $snmparray){
    return 1;
  }else{
    return 0;
  }
}

?>

