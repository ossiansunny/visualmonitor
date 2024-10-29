<?php
date_default_timezone_set('Asia/Tokyo');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
$kanshi_host="localhost";
$kanshi_user="kanshiadmin";
$kanshi_pass="kanshipass";
$kanshi_db="kanshi";
$kanshiDir=__DIR__;
//-------------------------------------------------
//---------- データベースへ接続 -------------------
//-------------------------------------------------
function openconnect(){
  global $kanshi_host, $kanshi_user, $kanshi_pass,$kanshi_db;
  $dbc = mysqli_connect($kanshi_host,$kanshi_user,$kanshi_pass);
  if ($dbc) {
    $db_sel = mysqli_select_db($dbc,$kanshi_db);
    /// $db_selは、bool(true) または bool(false)
    if($db_sel){
      return $dbc; // 正常の場合、オブジェクトを戻す
    }else{
      return $db_sel; // 異常の場合、falseを戻す
    }
  }else{
    return $dbc; // 接続エラーで　falseを返す
  }
  
}  
//
function default_str(String $raw_str = null, String $default = "") : String{
  if(isset($raw_str) === true){
      return $raw_str;
  }     
  return $default;
}
//-----------------------------------------------------
// readlog関数
//-----------------------------------------------------
function readlog(){
  global $kanshiDir;
  $tstamp = date("ymdHis");
  $ymd=substr($tstamp,0,6);
  $fp = fopen($kanshiDir."\logs\kanshi_".$ymd.".log","r");
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

//-----------------------------------------------------
// writeloge関数（無条件にログを出力）
//-----------------------------------------------------
function writeloge($_pgm,$_msg) {
  global $kanshiDir;
  $timeStamp = date("ymdHis");
  $ymd=substr($timeStamp,0,6);
  $fp = fopen($kanshiDir."\logs\kanshi_".$ymd.".log","a");
  //$tstamp = date("ymdHis");
  $data = $timeStamp . ": " . $_pgm . ": " . $_msg . "\n";
  fwrite($fp,$data);
  fclose($fp);
}
//-----------------------------------------------------
// writelogd関数（管理情報のデバッグ有りの場合ログを出力）
//-----------------------------------------------------
function writelogd($_pgm,$_msg) {
  $adminRows=getdata("select debug from admintb");
  $debug=$adminRows[0];
  if ($debug=="1" || $debug=="2") {
    writeloge($_pgm,$_msg);
  }  
}
//-----------------------------------------------------
// writelog関数（管理情報のデバッグDBの場合ログ出力）
//-----------------------------------------------------
function writelog($_pgm,$_msg) {
  $adminRows=getdata("select debug from admintb");
  $debug=$adminRows[0];
  if ($debug=="2") {
    writeloge($_pgm,$_msg);
  }
}
//---------------------------------------
//----- sql selectでデータを読む---------
//---------------------------------------
function getdata($_sql) {
  $dbg = debug_backtrace();
  $pgm = $dbg[0]["file"];
  $rtable = array();
  $dbc=openconnect();
  if(!$dbc){
    $msg="mysql db connection error";
    writeloge($pgm,$msg);
    $rtable[0]="error";
    return $rtable;
  }
  $res = mysqli_query($dbc,$_sql);
  if (mysqli_error($dbc)) {
    $msg="mysql query error: ".$sql;
    writeloge($pgm,$msg);
    $rtable[0] = "error";
    return $rtable;
  }
  $c = 0;
  while ($row = mysqli_fetch_row($res)) {
    $cc=0;
    $rc=count($row);
    $rtables="";
    for($cc=0;$cc<$rc;$cc++){
      $ajstr=default_str($row[$cc]," ");
      $rtables = $rtables . "," . $ajstr;  
    }
    $rtablex=substr($rtables,1);
    $rtable[$c]=$rtablex;
    $c++;
  }
  mysqli_close($dbc);
  return $rtable;
}

// ----------------------------------------
// -----SQL insert, update, deleteを実行---
//-----------------------------------------
function putdata($_sql) {  
  $dbg = debug_backtrace();
  $pgm = $dbg[0]["file"];
  $rtn = 0;
  $dbc=openconnect();
  if(!$dbc){
    $msg="mysql db connection error"; // rtn=-1
    writeloge($pgm,$msg);
    $rtn=-1;
  }else{
    $res = mysqli_query($dbc,$_sql);
    if (mysqli_error($dbc)) {
      $msg="mysql query parse error: ".$sql; //文法の間違い rtn=-1
      writeloge($pgm,$msg);
      $rtn = -1;
    } else {
      $msg="mysql debug: ".$_sql;
      writelogd($pgm,$msg);
      mysqli_close($dbc);
    }
  }
  return $rtn; // whereの該当なしも 0で帰る
}
//-------------------------------------------------------------------
//---  Writelogなしのcreate table,insert, update, delete を実行---
//--------------------------------------------------------------------
function create($_sql) {
  $dbc=openconnect();
  $rtn=0;
  if(!$dbc){
    $msg="mysql db connection error"; // rtn=-1
    $rtn=-1;
  }else{
    $res = mysqli_query($dbc,$_sql);
    if (mysqli_error($dbc)) {
      $rtn = -1;
    } else {
      mysqli_close($dbc);
    }
  }
  return $rtn; // whereの該当なしも 0で帰る
}

/*
$pgm='mysqlkanshi.php';
$sql="select * from user where userid='aadmin'";
$rtn=getdata($sql);
//writeloge($pgm,"mysql pgm test ".$sql);
var_dump($rtn);
if (empty($rtn)){
  echo 'none';
}
*/
?>
