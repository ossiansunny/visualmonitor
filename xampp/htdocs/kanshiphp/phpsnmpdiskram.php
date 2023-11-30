<?php
error_reporting(E_ALL & ~E_WARNING);

function ping($host){
  /// OSのpingコマンド
  exec("ping -w 1 {$host}", $output, $result);
  if ($result){
    /// ping Failed
    return 1;
    
  }else{
    /// ping Success
    return 0;
  }  
}    
///------------------------------------------------------------
///------- Windows ディスク使用率取得--------------------------
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
///------- Windows メモリ使用率取得 ----------------------------
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
///---------- Unix ディスク使用率取得--------------------------
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
///------- Windows メモリ使用率取得--------------------------
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

///------------------------------------------
///------------- デバッグ用------------------
///------------------------------------------
/*
$data=array();
$rtn=windiskload('192.168.1.53','public',$data);
if ($rtn==1){
  echo "Disk unknown\r\n";
}else{
  echo 'Disk: '.$data."<br>\r\n";
}
var_dump($data);
$data=array();
$rtn=winramload('192.168.1.53','public',$data);
if ($rtn==1){
  echo "RAM unknown\r\n";
}else{
  echo 'RAM: '.$data."<br>\r\n";
}
var_dump($data);
*/
/*
echo "--- windows ---<br>";
$data = array();
$host = "192.168.1.155";
$diskname="C";
$comm = "public";
windiskramload($host,$comm,$data);
var_dump($data);
$ac = count($data);
for($i=0;$i<$ac;$i++) {
  $sdata=explode(':',$data[$i]); 
  if($sdata[0]==$diskname){
    //echo "Disk: " . $sdata[0] ."Load: " . $sdata[1] . "%<br>";
    $diskval = $sdata[1];
  }elseif($sdata[0]=="P"){
    //echo "RAM Load: " . $sdata[1]:
    $ramval=$sdata[1];
  }
}
echo "diskload: " . $diskval . "%, ramload: " . $ramval . "%<br>";
*/
//---------------------------------------------

/*
echo "--- unix disk ---<br>\r\n";
$host = "192.168.1.18";
$comm = "public";
$data1 = array();  //再定義で前のデータが消える
$rtn=unixdiskload($host,$comm,$data1);
if ($rtn==1){
  echo "unknown\r\n";
}else{
  echo "disk usage: ".$data1 . "<br>\r\n";
}
var_dump($rtn);
*/
//-----------------------------------------------

/*
echo "--- unix ram ---<br>\r\n";
$host = "192.168.1.18";
$comm = "public";
$data = array();
$rtn=unixramload($host,$comm,$data);
if ($rtn==1){
  echo "unknown\r\n";
}else{
  echo "RAM Usage: " . $data. "%<br>\r\n";
}
var_dump($rtn)
*/
/*
ping('192.168.1.155');
*/

?>

