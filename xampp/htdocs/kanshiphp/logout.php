<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "mailsend.php";
require_once "snmpagent.php";

/*
function setSession($_sessvalue){
  print '<script type="text/javascript">';
  print 'sessionStorage.setItem("user","'.$_sessvalue.'");';
  print '</script>';
}
*/
function clearSession(){
  print '<script type="text/javascript">';
  print 'sessionStorage.clear();';
  print '</script>';
}
function closeWindow(){
  print '<script type="text/javascript">';
  print 'window.close();';
  print '</script>';
}
//setSession("admin");
date_default_timezone_set('Asia/Tokyo');
$pgm = "logout.php";
$user=""; ///BaseFunctionでセットされる
$brcode="";
$brmsg="";
$kanrisha="";
$mailMsg='';
$closeMsg='';
$logoutMsg='';
$user_bgColor='';
$bgColor='';
///
if(!isset($_GET['param'])){
  paramGet($pgm);
  
}else{
  paramSet();
  ///　ユーザー判定
  if($user=='unknown' or $user==''){
    $bgColor='bgred';
    $closeMsg='ユーザーを見失いました、ブラウザの閉じる「X」でクローズし、再ログインして下さい';
    $msg='ユーザーがデータベースに見つかりません';
    writeloge($pgm,$msg);
    $user_bgColor='bgstand';
    $logoutMsg="&emsp;ログアウト失敗";
    $mailMsg="Logout failed";
    $kanrisha='uknon';
  }else{
    $user_sql='select authority,bgcolor from user where userid="'.$user.'"';
    $userRows=getdata($user_sql);
    if(empty($userRows)){
      $bgColor='bgred';
      $closeMsg=$user.'　ユーザーがデータベースにありません、ブラウザの閉じる「X」でクローズし、再ログインして下さい';
      $msg='ユーザーがデータベースに見つかりません';
      writeloge($pgm,$msg);
      $user_bgColor="bgstand";  
      $logoutMsg="&emsp;ログアウト失敗";
      $mailMsg="Logout failed";
    }else{
      $userArr=explode(',',$userRows[0]);
      $user_Auth=$userArr[0];
      $user_bgColor=$userArr[1];
      $bgColor='bggreen';
      $logoutMsg="&emsp;ログアウト成功";
      $closeMsg='ブラウザの閉じる「X」でクローズして下さい';
      if ($user_Auth=='1'){ 
        /// 
        /// 2024/11/9 ログイン前にコア(SnmpAutoScan.php)が実行されるため 
        /// 127.0.0.1, snmp agentへ'sb' および admintbへ'2' セット
        ///
        $stat_sql="update statistics set agent='sb' where host='127.0.0.1'";
        $statRows=putdata($stat_sql);
        ///putagent('127.0.0.1'.'private','sb'); ///実行するとログアウトに時間がかかる
        ///$admin_sql="update admintb set authority='0', snmpintval=30, standby='2', saveintval='".$snmpintval."'";
        ///putdata($admin_sql);
        ///
        $adminSql="update admintb set logout='1'";
        putdata($adminSql);
      }
      $mailMsg=$user.' Logout success';
      $kanrisha=substr($user,0,5);
    }
  }
  /// メール送信
  $now=date('ymdHis');
  $timeStamp = $now;
  $logName='LOGOUT_'.$user;  
  $subject=$logName; 
  mailsend('',$user,'7','ログアウト','','',$mailMsg);
  /// イベントログ
  
  $event_sql = "insert into eventlog (host,eventtime,eventtype,snmpvalue,kanrisha,kanrino,message) values('".$logName."','".$timeStamp."','9',' ','".$kanrisha."','0',' ')";
  putdata($event_sql); 
  $msg = $logName . " Eventlog Insert sql: " . $event_sql;
  writelogd($pgm,$msg); 
  
  print '<!DOCTYPE html>';
  print '<html>';
  print '<head>';
  print '<meta charset="utf-8">';
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';
  print '<link rel="stylesheet" href="css/login.css">';
  print '</head>';
  print "<body class={$user_bgColor}>";
  print '<div class="login">';
  print '<div class="login-triangle"></div>';
  print "<h2 class='login-header'><img src='header/php.jpg' width='70' height='70'>{$logoutMsg}</h2>";
  $userid='ユーザID:&emsp;'.$user;
  print '<form class="login-container">';
  print "<input type='text' name='user' value={$userid} readonly><br>";
  print "<p class={$bgColor}><font color=white>{$closeMsg}</font></p>";
  print '</form>';
  print "</div>";
  print '</body>';
  print '</html>';
  //echo "<script>sessionStorage.clear();</script>";
  //echo "<script>alert('test');</script>";
  //session_destroy();
}
?>


