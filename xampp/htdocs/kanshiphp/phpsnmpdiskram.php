<?php
error_reporting(E_ALL & ~E_WARNING);

///------------------------------------------------------------
///------- Windows --------------------------
///------------------------------------------------------------
function windiskload($host,$community,&$data) {
$snmpsize=snmpget($host,$community,".1.3.6.1.2.1.25.2.3.1.5.1",1000000,1);
if(! $snmpsize){
  return 1;
}
$ssize=explode(":",$snmpsize);
$snmpused=snmpget($host,$community,".1.3.6.1.2.1.25.2.3.1.6.1",1000000,1);
$sused=explode(":",$snmpused);
if($sused[1] < $ssize[1]){
  $ritsu=intval($sused[1]) / intval($ssize[1]) * 100;
  $data=strval(intval($ritsu));
}else{
  $data='0';
}
return 0;
}

///-------------------------------------------------------------
///------- Windows ----------------------------
///-------------------------------------------------------------
function winramload($host,$community,&$data) {
$snmpsize=snmpget($host,$community,".1.3.6.1.2.1.25.2.3.1.5.5",1000000,1);
if(! $snmpsize){
  return 1;
}
$ssize=explode(":",$snmpsize);
$snmpused=snmpget($host,$community,".1.3.6.1.2.1.25.2.3.1.6.5",1000000,1);
$sused=explode(":",$snmpused);
if($sused[1] < $ssize[1]){
  $ritsu=intval($sused[1]) / intval($ssize[1]) * 100;
  $data=strval(intval($ritsu));
}else{
  $data='0';
}
return 0;
}

///------------------------------------------------------------
///---------- Unix --------------------------
///------------------------------------------------------------
function unixdiskload($host,$community,&$data) {
  $dsave=array();
  $snmparray = snmprealwalk($host, $community, ".1.3.6.1.2.1.25.2.3.1.3",1000000,1);
  if(! $snmparray){
    return 1;
  }
  $oiddisk="0";
  foreach($snmparray as $key => $value){
    $reg=preg_match('/STRING: \/$/',$value);
    if($reg==1){
      $sval=explode('.',$key);
      $oiddisk=$sval[1];
    }
  }
  if($oiddisk=="0"){
    $data=0;
  } else {
  ///---------linux size------------------------------------------
    $oidsize=".1.3.6.1.2.1.25.2.3.1.5.".$oiddisk;
    $snmparray = snmpget($host, $community, $oidsize,1000000,1);
    $dsv=explode(':',$snmparray);
    $dsvs=$dsv[1];
  ///---------linux used------------------------------------------
    $oidused=".1.3.6.1.2.1.25.2.3.1.6.".$oiddisk;
    $snmparray = snmpget($host, $community, $oidused,1000000,1);
    $dsv=explode(':',$snmparray);
    $dsvu=$dsv[1];
  ///----------linux load -------------------------------------------------------------
    if($dsvu < $dsvs) {
      $dp = $dsvu / $dsvs * 100;
    } else {
      $dp = 0;
    }
    $data=intval($dp);    
    return 0;
  }
}
///------------------------------------------------------------
///------- Unix --------------------------
///------------------------------------------------------------
function unixramload($host,$community,&$data) {
  $snmpans = snmpget($host, $community, ".1.3.6.1.2.1.25.2.3.1.5.1",1000000,1);
  if(! $snmpans){
    return 1;
  }
  $ramp=explode(":",$snmpans);
  $snmpans = snmpget($host, $community, ".1.3.6.1.2.1.25.2.3.1.6.1",1000000,1);
  $ramu=explode(":",$snmpans);
  if($ramp[1] > $ramu[1] ) {
    $ramr=$ramu[1] / $ramp[1] * 100;
  }else{
    $ramr=0;
  }
  $data=intval($ramr);
  return 0;
}
/*
//
$check="";
$ip='192.168.1.21';
$comm='public';
$rtn=unixdiskload($ip,$comm,$check);
var_dump($check);
echo $rtn;
//
*/
?>


