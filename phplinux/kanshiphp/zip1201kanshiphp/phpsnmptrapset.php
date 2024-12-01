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
/*
function snmptrapget($host) {
  $trapsql="select * from trapstatistics where host='".$host."'";
  $rows=getdata($trapsql);
  if (empty($rows) or $rows[0]=="error"){
    return 1;
  }else{
    $procs=explode(',',$rows[0]);
    $rtnval=$procs[7];					/// ""
  }
  return $rtnval;  
}
*/
/*
snmptrapset("192.168.1.21","remote","httpd oracle master sshd");
$rtn=snmptrapget("192.168.1.18");
var_dump($rtn);
*/
?>

