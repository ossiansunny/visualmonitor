<?php
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
  $tstamp = $now;
  $adminsql='select * from admintb';
  $arows=getdata($adminsql);
  $adata=explode(',',$arows[0]);
  
  $to=$adata[3];
  $from=$adata[4];
  $logname='LOGOUT_'.$user;
  /// Write Event Log
  $insql = "insert into eventlog (host,eventtime,eventtype,kanrisha) values('".$logname."','".$tstamp."','9','".$user."')";
  putdata($insql); 
  $msg = $logname . " Eventlog Insert sql: " . $insql;
  writeloge($pgm,$msg); 
  /// 終了メール
  $message=$user.' Logged out';
  /// "進行中・・・";
  $logname="LOGOUT ".$user;
  $sub=$logname;
  $rtn=mailsendany('loginlogout',$from,$to,$sub,$message);
  $selsql='select authority from user where userid="'.$user.'"';
  $udata=getdata($selsql);
  $sdata=explode(',',$udata[0]);
  $auth=$sdata[0];
  if ($auth=='1'){   
    $upsql='update admintb set authority="0"';
    putdata($upsql);
  }
  print '<!DOCTYPE html>';
  print '<html>';
  print '<head>';
  print '<meta charset="utf-8">';
  print '<title>サンプル</title>';
  print '<link rel="stylesheet" href="login.css">';
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

