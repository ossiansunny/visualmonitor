<?php
date_default_timezone_set('Asia/Tokyo');
require_once 'mysqlkanshi.php';
require_once 'BaseFunction.php';
require_once 'phpsendmailAt.php';
$pgm = "graphmailsend.php";
///
function bodyformat($_from,$_status,$_msg,&$bodyStr){
  $_body = array();
  $dte=date('Y-m-d H:i:s');
  $_body[0]='***** VisualMonitor *****';
  $_body[1]='From: ' . $_from;
  $_body[2]='Date: ' .$dte;
  $_body[3]='State: ' .$_status; // 
  $_body[4]='Messages:';
  $_body[5]=$_msg; /// message
  $bodyStr='';
  foreach ($_body as $_bodyRec){
    $bodyStr=$bodyStr.$_bodyRec."\r\n";
  }
}
///
$graphStr=$_GET['graph'];
$graphArr=explode(',',$graphStr);
$host=$_GET['host'];
$user=$_GET['user'];

///
$bodyStr = "";
$header_sql='select * from header';
$headerRows=getdata($header_sql);
$headerArr=explode(',',$headerRows[0]);
$header=$headerArr[0];
$subj1='Email'; 
$subj2='Attachment';
$subj3='Graph';
$title='**'.$subj1.' '.$subj2. ' ' .$subj3. '**';
$msg='Host '.$host;
///
bodyformat($user,$title,$msg,$bodyStr);
writeloge($pgm,$bodystr);
///
$admin_sql="select * from admintb";
$adminRows=getdata($admin_sql);
$adminArr=explode(',',$adminRows[0]);
$mailToAddr=$adminArr[3]; /// mail to addr
$mailFromAddr=$adminArr[4]; /// mail from addr
///
//echo 'test3';
$mailFlag=phpsendmailat("", "", $mailFromAddr, $mailToAddr, $title, $bodyStr,$graphArr);
//echo 'test4';
///
if (strpos($graphArr[0],'.svg') !== false){
  $nextpage='GraphListPlotPage.php';
}else{
  $nextpage='GraphListPage.php';
}
if($mailFlag==0){
  $mmsg='success '.$bodyStr.' '.$mailToAddr.' '.$mailFromAddr;
  writelogd($pgm,$mmsg);
  $msg="#notic#".$user."#ホスト".$host."のグラフ添付メール送信完了";
  branch($nextpage,$msg);
}else{
  $mmsg='failed '.$bodyStr.' '.$mailToAddr.' '.$mailFromAddr;
  writeloge($pgm,$mmsg);
  $msg="#error#".$user."#ホスト".$host."のグラフ添付メール送信失敗";
  branch($nextpage,$msg);
}

?>

