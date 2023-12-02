<?php
error_reporting(E_ALL & ~E_WARNING);
///-------SNMPエージェントへチェックすべきプロセスをセット---------
function snmpprocessset($host,$community,$process) {
  $snmparray = array();
  $snmparray = snmp2_set($host,$community,".1.3.6.1.4.1.9999.1.2.0","s",$process,1000000,1);
  if (! $snmparray){
    return 1;
  }else{
    return 0;
  }
}

?>
