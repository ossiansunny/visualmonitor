<?php
error_reporting(E_ALL & ~E_WARNING);
/// SNMP�G�[�W�F���g��sysLocation.0 �Ŏ����`�F�b�N
function snmpactive($host,$community) {
  $snmparray = array();
  $snmparray = snmpget($host, $community, ".1.3.6.1.2.1.1.6.0",1000000,1);
  if (! $snmparray){
    return 1;
  }else{
    return 0;
  }
}
/*
/// �f�o�b�O�p
$host='192.168.1.19';
$community='public';
$rtn=snmpactive($host,$community);
if ($rtn==0){
  echo 'OK '.strval($rtn);
}else{
  echo 'NG '.strval($rtn);
}
*/
?>