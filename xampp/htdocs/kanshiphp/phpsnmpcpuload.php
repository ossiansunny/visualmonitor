<?php
error_reporting(E_ALL & ~E_WARNING);
///----------------------------------------------------
///----------------Windowsエージェント用---------------
///----------------------------------------------------
function wincpuload($host,$community,&$data) {
$snmparray = array();
/// 全CPU coreでの最大値取得
$snmparray = snmp2_walk($host, $community, ".1.3.6.1.2.1.25.3.3.1.2",1000000,1);
if (! $snmparray){
  $data='0';
  return 1;
}
$c = count($snmparray);
$item = array();
$load = 0;
for($i=0;$i<$c;$i++) {
  $item = explode(' ',$snmparray[$i]);
  if ($item[0] == 'INTEGER:') {
    if (intval($item[1]) > $load){
      $load=intval($item[1]);
    }
  }
}
$data=strval($load);
return 0;
}

//-----------------------------------------------
//----------------- Linuxエージェント用----------
///----------------------------------------------
function unixcpuload($host,$community,&$data) {
/// １分、５分、１０分の最大値を取得
$snmparray = snmpwalk($host,$community, ".1.3.6.1.4.1.2021.10.1.5");
if(!$snmparray){
  $data='0';
  return 1;
}
$item=array();
$c=count($snmparray);
$load = 0;
for($i=0;$i<$c;$i++) {
  $item = explode(' ',$snmparray[$i]);
  if ($item[0] == 'INTEGER:') {
    if (intval($item[1]) > $load){
      $load=intval($item[1]);
    }
  }
}
$data = strval($load);
return 0;
}

?>

