<?php
date_default_timezone_set('Asia/Tokyo');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
$kanshi_host="localhost";
$kanshi_user="kanshiadmin";
$kanshi_pass="kanshipass";
$kanshi_db="kanshi";

//-------------------------------------------------
//---------- -----------------
//-------------------------------------------------
function openconnect(){
  global $kanshi_host, $kanshi_user, $kanshi_pass,$kanshi_db;
  $dbc = mysqli_connect($kanshi_host,$kanshi_user,$kanshi_pass);
  if ($dbc) {
    $db_sel = mysqli_select_db($dbc,$kanshi_db);
    // $db_sel bool(true) bool(false)
    if($db_sel){
      return $dbc; //
    }else{
      return $db_sel; //
    }
  }else{
    //echo "error connect";
    return $dbc; //
  }
  
}  
//
/*
function default_str(String $raw_str = null, String $default = "") : String{
  if(isset($raw_str) === true){
      return $raw_str;
  }     
  return $default;
}
*/
//-----------------------------------------------------
// readlog
//-----------------------------------------------------
function readlog(){
  $tstamp = date("ymdHis");
  $ymd=substr($tstamp,0,6);
  $fp = fopen("logs/kanshi_".$ymd.".log","r");
  $rtable = array();
  $c=0;
  if($fp){
    while ($line = fgets($fp)) {
      $rtable[$c] = $line;
      $c++;
    }
    fclose($fp);
  }
  //fclose($fp);
  return $rtable;
}

//-----------------------------------------------------
// writeloge
//-----------------------------------------------------
function writeloge($pgm,$msg) {
  $tstamp = date("ymdHis");
  $ymd=substr($tstamp,0,6);
  $fp = fopen("logs/kanshi_".$ymd.".log","a");
  $tstamp = date("ymdHis");
  $data = $tstamp . ": " . $pgm . ": " . $msg . "\n";
  fwrite($fp,$data);
  fclose($fp);
}
//-----------------------------------------------------
// writelogd
//-----------------------------------------------------
function writelogd($pgm,$msg) {
  $rows=getdata("select debug from admintb");
  $debug=$rows[0];
  if ($debug=="1" || $debug=="2") {
    writeloge($pgm,$msg);
//    $fp = fopen("logs/kanshi.log","a");
//    $tstamp = date("ymdHis");
//    $data = $tstamp . ": " . $pgm . ": " . $msg . "\n";
//    fwrite($fp,$data);
//    fclose($fp);
  }  
}
//-----------------------------------------------------
// writelog
//-----------------------------------------------------
function writelog($pgm,$msg) {
  $rows=getdata("select debug from admintb");
  $debug=$rows[0];
  if ($debug=="2") {
    writeloge($pgm,$msg);
//    $fp = fopen("logs/kanshi.log","a");
//    $tstamp = date("ymdHis");
//    $data = $tstamp . ": " . $pgm . ": " . $msg . "\n";
//    fwrite($fp,$data);
//    fclose($fp);
  }
}
//---------------------------------------
//----- sql select---------
//---------------------------------------
function getdata($sql) {
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
  $res = mysqli_query($dbc,$sql);
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
      $ajstr=$row[$cc];
      //$ajstr=default_str($row[$cc]," ");
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
// -----SQL insert, update, delete---
//-----------------------------------------
function putdata($sql) {
  $dbg = debug_backtrace();
  $pgm = $dbg[0]["file"];
  $rtn = 0;
  $dbc=openconnect();
  if(!$dbc){
    $msg="mysql db connection error"; // rtn=-1
    writeloge($pgm,$msg);
    $rtn=-1;
  }else{
    $res = mysqli_query($dbc,$sql);
    if (mysqli_error($dbc)) {
      $msg="mysql query parse error: ".$sql; //
      writeloge($pgm,$msg);
      $rtn = -1;
    } else {
      $msg="mysql debug: ".$sql;
      writelogd($pgm,$msg);
      mysqli_close($dbc);
    }
  }
  return $rtn; // 
}
//-------------------------------------------------------------------
//---  Writelog create table,insert, update, delete---
//--------------------------------------------------------------------
function create($sql) {
  $dbc=openconnect();
  $rtn=0;
  if(!$dbc){
    $msg="mysql db connection error"; // rtn=-1
    $rtn=-1;
  }else{
    $res = mysqli_query($dbc,$sql);
    if (mysqli_error($dbc)) {
      $rtn = -1;
    } else {
      mysqli_close($dbc);
    }
  }
  return $rtn; // 
}
/*
writeloge('aaa','test');
$sql="select * from user where userid='admin'";
$rtn=getdata($sql);
var_dump($rtn);
if (empty($rtn)){
  echo 'none';
}
*/
?>