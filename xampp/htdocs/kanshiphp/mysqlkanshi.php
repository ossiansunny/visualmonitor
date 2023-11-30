<?php
date_default_timezone_set('Asia/Tokyo');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
$kanshi_host="localhost";
$kanshi_user="kanshiuser";
$kanshi_pass="kanshipass";
$kanshi_db="kanshi";

//-------------------------------------------------
//---------- �f�[�^�x�[�X�֐ڑ� -------------------
//-------------------------------------------------
function openconnect(){
  global $kanshi_host, $kanshi_user, $kanshi_pass,$kanshi_db;
  $dbc = mysqli_connect($kanshi_host,$kanshi_user,$kanshi_pass);
  if ($dbc) {
    $db_sel = mysqli_select_db($dbc,$kanshi_db);
    // $db_sel�́Abool(true) �܂��� bool(false)
    if($db_sel){
      return $dbc; // ����̏ꍇ�A�I�u�W�F�N�g��߂�
    }else{
      return $db_sel; // �ُ�̏ꍇ�Afalse��߂�
    }
  }else{
    //echo "error connect";
    return $dbc; // �ڑ��G���[�Ł@false��Ԃ�
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
// readlog�֐�
//-----------------------------------------------------
function readlog(){
  $fp = fopen("kanshi.log","r");
  $rtable = array();
  $c=0;
  if($fp){
    while ($line = fgets($fp)) {
      $rtable[$c] = $line;
      $c++;
    }
  }
  fclose($fp);
  return $rtable;
}
//-----------------------------------------------------
// writeloge�֐��i�������Ƀ��O���o�́j
//-----------------------------------------------------
function writeloge($pgm,$msg) {
  $fp = fopen("kanshi.log","a");
  $tstamp = date("ymdHis");
  $data = $tstamp . ": " . $pgm . ": " . $msg . "\n";
  fwrite($fp,$data);
  fclose($fp);
}
//-----------------------------------------------------
// writelogd�֐��i�Ǘ����̃f�o�b�O�L��̏ꍇ���O���o�́j
//-----------------------------------------------------
function writelogd($pgm,$msg) {
  $rows=getdata("select debug from admintb");
  $debug=$rows[0];
  if ($debug=="1" || $debug=="2") {
    $fp = fopen("kanshi.log","a");
    $tstamp = date("ymdHis");
    $data = $tstamp . ": " . $pgm . ": " . $msg . "\n";
    fwrite($fp,$data);
    fclose($fp);
  }  
}
//-----------------------------------------------------
// writelog�֐��i�Ǘ����̃f�o�b�ODB�̏ꍇ���O�o�́j
//-----------------------------------------------------
function writelog($pgm,$msg) {
  $rows=getdata("select debug from admintb");
  $debug=$rows[0];
  if ($debug=="2") {
    $fp = fopen("kanshi.log","a");
    $tstamp = date("ymdHis");
    $data = $tstamp . ": " . $pgm . ": " . $msg . "\n";
    fwrite($fp,$data);
    fclose($fp);
  }
}
//---------------------------------------
//----- sql select�Ńf�[�^��ǂ�---------
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
// -----SQL insert, update, delete�����s---
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
      $msg="mysql query parse error: ".$sql; //���@�̊ԈႢ rtn=-1
      writeloge($pgm,$msg);
      $rtn = -1;
    } else {
      $msg="mysql debug: ".$sql;
      writelogd($pgm,$msg);
      mysqli_close($dbc);
    }
  }
  return $rtn; // where�̊Y���Ȃ��� 0�ŋA��
}
//-------------------------------------------------------------------
//---  Writelog�Ȃ���create table,insert, update, delete �����s---
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
  return $rtn; // where�̊Y���Ȃ��� 0�ŋA��
}
/*
$sql="select  from mailserverx";
$mrows=getdata($sql);
if(empty($mrow)){
  echo "no record";
}elseif($mrows[0]=="error"){
  echo "DB Access Error";
}else{
  echo "ok ",$mrows[0];
}


*/
/*

$sql="update statistics set gtype=9 where host=192.168.1.111";
$rtn=putdata($sql);
var_dump($rtn);
if (!empty($rtn)){ // is not ok
  echo "not ok";
}else {
  echo "OK";
}
*/
/*
$rc=array();
$rc=getstatus();
echo "\nget status".$rc;

$rc=setstatus("b","msg2b");
if ($rc==1){
  echo "\nno saved";
}else if($rc==2){
  echo "\nalready saved";
}else{ 
  echo "\nsaved";
}
*/
?>
