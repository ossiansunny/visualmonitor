<?php
date_default_timezone_set('Asia/Tokyo');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
$kanshi_host="localhost";
$kanshi_user="kanshiadmin";
$kanshi_pass="kanshipass";
$kanshi_db="kanshi";
$kanshiDir=__DIR__;
///-------------------------------------------------
///---------- データベースへ接続 -------------------
///-------------------------------------------------
function openconnect(){
  global $kanshi_host, $kanshi_user, $kanshi_pass,$kanshi_db;
  $dbc = mysqli_connect($kanshi_host,$kanshi_user,$kanshi_pass);
  if ($dbc) {
    $db_sel = mysqli_select_db($dbc,$kanshi_db);
    /// $db_selは、bool(true) または bool(false)
    if($db_sel){
      return $dbc; /// 正常の場合、オブジェクトを戻す
    }else{
      return $db_sel; /// 異常の場合、falseを戻す
    }
  }else{
    return $dbc; /// 接続エラーで　falseを返す
  }
  
}  
///
/*
function default_str(String $raw_str = null, String $default = "") : String{
  if(isset($raw_str) === true){
      return $raw_str;
  }     
  return $default;
}
*/
///-----------------------------------------------------
/// readlog関数
///-----------------------------------------------------
function readlog(){
  global $kanshiDir;
  $tstamp = date("ymdHis");
  $ymd=substr($tstamp,0,6);
  $fp = fopen($kanshiDir."/logs/kanshi_".$ymd.".log","r");
  $rtable = array();
  $c=0;
  if($fp){
    while ($line = fgets($fp)) {
      $rtable[$c] = $line;
      $c++;
    }
    fclose($fp);
  }
  return $rtable;
}

///-----------------------------------------------------
/// writeloge関数（無条件にログを出力）
///-----------------------------------------------------
function writeloge($_pgm,$_msg) {
  global $kanshiDir;
  $timeStamp = date("ymdHis");
  $ymd=substr($timeStamp,0,6);
  $fp = fopen($kanshiDir."/logs/kanshi_".$ymd.".log","a");
  $data = $timeStamp . ": " . $_pgm . ": " . $_msg . "\n";
  fwrite($fp,$data);
  fclose($fp);
}
///-----------------------------------------------------
/// writelogd関数（管理情報のデバッグ有りの場合ログを出力）
///-----------------------------------------------------
function writelogd($_pgm,$_msg) {
  $adminRows=getdata("select debug from admintb");
  $debug=$adminRows[0];
  if ($debug=="1" || $debug=="2") {
    writeloge($_pgm,$_msg);
  }  
}
///-----------------------------------------------------
/// writelog関数（管理情報のデバッグDBの場合ログ出力）
///-----------------------------------------------------
function writelog($_pgm,$_msg) {
  $adminRows=getdata("select debug from admintb");
  $debug=$adminRows[0];
  if ($debug=="2") {
    writeloge($_pgm,$_msg);
  }
}
///---------------------------------------
///----- sql selectでデータを読む---------
///---------------------------------------
function getdata2($_sql) {
  try{
    $dbg = debug_backtrace();
    $pgm = $dbg[0]["file"];
    $rtable = array();
    $dbc=openconnect();
    if(!$dbc){
      $msg="mysql db connection error";
      writeloge($pgm,$msg);
      //$rtable[0]="error";
      return null;
    }
    $res = mysqli_query($dbc,$_sql);
    if (mysqli_error($dbc)) {
      $msg="mysql query error: ".$_sql;
      writeloge($pgm,$msg);
      //$rtable[0] = "error";
      return null;
    }
    $c = 0;
    while ($row = mysqli_fetch_row($res)) {
      $cc=0;
      $rc=count($row);
      $rtables="";
      for($cc=0;$cc<$rc;$cc++){
        $rtables = $rtables . "," . $row[$cc];  
      }
      $rtablex=substr($rtables,1);
      $rtable[$c]=$rtablex;
      $c++;
    }
    mysqli_close($dbc);
    return $rtable;
  }catch(Exception $e){
    writeloge("mysqlkanshi.php","Exception:".$_sql);
    return null;
  }  
}
function getdata($_sql) {
  try{
    $dbg = debug_backtrace();
    $pgm = $dbg[0]["file"];
    $rtable = array();
    $dbc=openconnect();
    if(!$dbc){
      $msg="mysql db connection error";
      writeloge($pgm,$msg);
      //$rtable[0]="error";
      return null;
    }
    mysqli_query($dbc,'begin');
    $sqltop=explode(' ',$_sql);
    if($sqltop[0]=='select'){
      $res = mysqli_query($dbc,$_sql.' for update');
    }else{
      $res = mysqli_query($dbc,$_sql);
    }
    if (mysqli_error($dbc)) {
      $msg="mysql query error: ".$_sql;
      writeloge($pgm,$msg);
      //$rtable[0] = "error";
      return null;
    }
    $c = 0;
    while ($row = mysqli_fetch_row($res)) {
      $cc=0;
      $rc=count($row);
      $rtables="";
      for($cc=0;$cc<$rc;$cc++){
        $rtables = $rtables . "," . $row[$cc];  
      }
      $rtablex=substr($rtables,1);
      $rtable[$c]=$rtablex;
      $c++;
    }
    mysqli_query($dbc,'commit');
    mysqli_close($dbc);
    
    return $rtable;
  }catch(Exception $e){
    writeloge("mysqlkanshi.php","Exception:".$_sql);
    return null;
  }  
}
/// ----------------------------------------
/// -----SQL insert, update, deleteを実行---
///-----------------------------------------
function putdata($_sql) {  
  $dbg = debug_backtrace();
  $pgm = $dbg[0]["file"];
  $rtn = 0;
  $dbc=openconnect();
  if(!$dbc){
    $msg="mysql db connection error"; /// rtn=-1
    writeloge($pgm,$msg);
    $rtn=-1;
  }else{
    mysqli_query($dbc,'begin');
    $res = mysqli_query($dbc,$_sql);
    if (mysqli_error($dbc)) {
      $msg="mysql query parse error: ".$_sql; ///文法の間違い rtn=-1
      writeloge($pgm,$msg);
      $rtn = -1;
    } else {
      $msg="mysql debug: ".$_sql;
      if(substr($_sql,0,15)=='insert into host'){
        writeloge($pgm,$msg);
      }
      mysqli_query($dbc,'commit');
      mysqli_close($dbc);
    }
  }
  return $rtn; /// whereの該当なしも 0で帰る
}
///-------------------------------------------------------------------
///---  Writelogなしのcreate table,insert, update, delete を実行---
///--------------------------------------------------------------------
function create($_sql) {
  $dbc=openconnect();
  $rtn=0;
  if(!$dbc){
    $msg="mysql db connection error"; /// rtn=-1
    $rtn=-1;
  }else{
    $res = mysqli_query($dbc,$_sql);
    if (mysqli_error($dbc)) {
      $rtn = -1;
    } else {
      mysqli_close($dbc);
    }
  }
  return $rtn; /// whereの該当なしも 0で返る
}
/*
$sql="insert into host (host,groupname,ostype,result,action,viewname,mailopt,tcpport,cpulim,ramlim,disklim,process,image,snmpcomm,agenthost,eventlog,standby) values('192.168.1.22','unkown','1','0','2','Rocky9522','0','','80:90','80:90','80:90','','server.png','public','','0','1')";
$rtn=putdata($sql);
var_dump($rtn);
$sql="select * from host where host='192.168.1.22'";
$rtn=getdata($sql);
var_dump($rtn);
*/
/*
$sql="show tables";
$rtn=getdata($sql);
var_dump($rtn);
if(empty($rtn)){
echo "empty";
}else{
echo "not empty";
}
*/
?>
