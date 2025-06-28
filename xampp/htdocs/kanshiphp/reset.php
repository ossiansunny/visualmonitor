<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "mailsend.php";
require_once "graphlogadjust.php";
require_once "varread.php";
/*
function clearSession(){
  print '<script type="text/javascript">';
  print 'sessionStorage.clear();';
  print '</script>';
}
*/
date_default_timezone_set('Asia/Tokyo');
$pgm = "reset.php";
$user=""; ///BaseFunctionでセットされる
$brcode="";
$brmsg="";
$kanrisha="";
$mailMdg='';
$closeMsg='';
$logoutMsg='';
$user_bGcolor='';
$bgColor='';
///
if(!isset($_GET['param'])){
  paramGet($pgm);
}else{
  paramSet();
  if($user=='unknown' or $user==''){
    $bgColor='bgred';
    $closeMsg='ユーザーを見失いました、ブラウザの閉じる「X」でクローズし、再ログインして下さい';
    $msg='ユーザーがデータベースに見つかりません';
    writeloge($pgm,$msg);
    $user_bgColor='bgstand';
    $logoutMsg="&emsp;リセット失敗";
    $mailMsg="Reset failed";
    $kanrisha='uknon';
  }else{
    /// get background color
    $user_sql='select authority,bgcolor from user where userid="'.$user.'"';
    $userRows=getdata($user_sql);
    if(empty($userRows)){
      $user='unknown';
      $bgColor='bgred';
      $closeMsg=$user.'　ユーザーがデータベースにありません、ブラウザの閉じる「X」でクローズし、再ログインして下さい';
      $msg='ユーザーがデータベースに見つかりません';
      $user_bgColor="bgstand";
      writeloge($pgm,$msg);
      $logoutMsg="&emsp;リセット失敗";
      $mailMsg="Reset failed";
      $kanrisha='uknon';
    }else{
      $bgColor='bggreen';
      $logoutMsg="&emsp;リセット成功";
      $closeMsg='ブラウザの閉じる「X」でクローズして下さい';
      $userArr=explode(',',$userRows[0]);
      $user_Auth=$userArr[0];
      $user_bgColor=$userArr[1];
      if ($user_Auth=='1'){ 
        /// 次回ログインまでRefresh中止
        $adminSql="update admintb set logout='1'";
        putdata($adminSql);
        $mailMsg=' Reset success';
        $kanrisha=substr($user,0,5);
      }
      /// 対象ホストデータのresultを強制監視チェックをさせるため正常にする
      /// statisticsデータはリセットする
      $layoutSql="select host from layout";
      $layoutRows=getdata($layoutSql);
      foreach($layoutRows as $layoutHost){
        if($layoutHost=="NoAssign" or empty($layoutHost)){
          continue;
        }else{
          $hostSql="update host set result='1' where host='{$layoutHost}'";
          $rtn=putdata($hostSql);
          if($rtn!=0){
            writeloge($pgm,'layout host not in host table:'.$hostSql);
          }  
          $statSql="update statistics set tstamp='000000000000',gtype='9',cpuval='',ramval='',diskval='',process='',tcpport='',status='0' where host='{$layoutHost}'";
          $rtn=putdata($statSql);
          if($rtn!=0){
            writeloge($pgm,'statistics update error:'.$statSql);
          }          
        }
      }

      /// mrtgログおよびplotログのデータを調整する
      $vpParam=array('vpath_mrtghome');
      $vpRows=pathget($vpParam);
      graphlogadjust($vpRows[0]);
    }  
  }  
  /// メール送信    
  $timeStamp=date('ymdHis');
  $logName='RESET_'.$user;
  mailsend('',$user,'7','リセット','','',$mailMsg);
  /// Write Event Log
  $event_sql = "insert into eventlog (host,eventtime,eventtype,snmpvalue,kanrisha,kanrimei,kanrino,message) values('".$logName."','".$timeStamp."','9',' ','".$kanrisha."',' ','0',' ')";
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
  print '<body class="'.$user_bgColor.'">';
  print '<div class="login">';
  print '<div class="login-triangle"></div>';
  print '<h2 class="login-header"><img src="header/php.jpg" width="70" height="70">'.$logoutMsg.'</h2>';
  $userid='ユーザID:&emsp;'.$user;
  print '<form class="login-container">';
  print "<input type='text' name='user' value={$userid} readonly><br>";
  print "<p class={$bgColor}><font color=white>{$closeMsg}</font></p>";
  print '</form>';
  print '</div>';
  print '</body>';
  print '</html>';
  
  session_destroy();
}
?>


