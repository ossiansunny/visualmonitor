﻿<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "mailsendany.php";

date_default_timezone_set('Asia/Tokyo');
$pgm = "logout.php";
$user="";
$brcode="";
$brmsg="";
///
if(!isset($_GET['param'])){
  paramGet($pgm);
}else{
  paramSet();  
  /// 終了メール
  $now=date('ymdHis');
  $timeStamp = $now;
  $admin_sql='select * from admintb';
  $adminRows=getdata($admin_sql);
  $adminArr=explode(',',$adminRows[0]);  
  $admin_Toaddr=$adminArr[3];
  $admin_Fromaddr=$adminArr[4];
  $logName='LOGOUT_'.$user;
  /// Write Event Log
  $event_sql = "insert into eventlog (host,eventtime,eventtype,snmpvalue,kanrisha,kanrino,message) values('".$logName."','".$timeStamp."','9',' ','".$user."','0',' ')";
  putdata($event_sql); 
  $msg = $logName . " Eventlog Insert sql: " . $event_sql;
  writeloge($pgm,$msg); 
  /// 終了メール
  $message=$user.' Logged out';
  /// "進行中・・・";
  $logName="LOGOUT ".$user;
  $subject=$logName;
  $rtn=mailsendany('loginlogout',$admin_Fromaddr,$admin_Toaddr,$subject,$message);
  $user_sql='select authority from user where userid="'.$user.'"';
  $userRows=getdata($user_sql);
  $userArr=explode(',',$userRows[0]);
  $user_Auth=$userArr[0];
  if ($user_Auth=='1'){   
    $admin_sql='update admintb set authority="0"';
    putdata($admin_sql);
  }
  print '<!DOCTYPE html>';
  print '<html>';
  print '<head>';
  print '<meta charset="utf-8">';
  print '<title>サンプル</title>';
  print '<link rel="stylesheet" href="css/login.css">';
  print '</head>';
  print '<body>';
  print '<div class="login">';
  print '<div class="login-triangle"></div>';
  print '<h2 class="login-header"><img src="header/php.jpg" width="70" height="70">&emsp;&emsp;ログアウト</h2>';
  print '<p><font color="white">ブラウザの閉じる「X」でクローズして下さい</font></p>';
  print '</div>';
  print '<div class="login">';
  print '</div>';
  print '</body>';
  print '</html>';
}
?>


