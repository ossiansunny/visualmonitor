<?php
date_default_timezone_set('Asia/Tokyo');
require_once 'mysqlkanshi.php';
require_once 'BaseFunction.php';
require_once 'phpsendmailAt.php';
$pgm = "graphmailsend.php";
///
///
$graphStr=$_GET['graph'];
$graphArr=explode(',',$graphStr); /// xxxxx.png,yyyyy.svg
$host=$_GET['host'];
$user=$_GET['user'];
$hostSql="select host,groupname,ostype,result,action,viewname from host where host='".$host."'";
$hostRows=getdata($hostSql);
$hostArr=explode(',',$hostRows[0]);
$viewname=$hostArr[5];
$event='グラフ添付メール';
///
$timeStamp=date('Y-m-d H:i:s');
$body = array();
$headerSql="select title,subtitle from header";
$headerRows=getdata($headerSql);
$headArr=explode(',',$headerRows[0]);
$headTitle=$headArr[0];
$headSubTitle=$headArr[1];
$body[0]='***** VisualMonitor (通知) *****';
$body[1]='From: ' .$headTitle.' '.$headSubTitle.' '.$user;
$body[2]='Date: ' .$timeStamp;
$body[3]='Event: '.$event;
$body[4]='Origin: '.'MrtgLog:'.'CPU,Ram,Disk使用率';
$body[5]='Source: ' .$viewName.' '.$host;
$body[6]='Additional Info:';
$body[7]=$graphStr;    
$bodyStr="";
foreach($body as $item){
  $bodyStr=$bodyStr.$item.PHP_EOL;
}
///
$admin_sql="select receiver,sender from admintb";
$adminRows=getdata($admin_sql);
$adminArr=explode(',',$adminRows[0]);
$mailToAddr=$adminArr[0]; /// mail to addr
$mailFromAddr=$adminArr[1]; /// mail from addr
///
$mailFlag=phpsendmailat("", "", $mailFromAddr, $mailToAddr, $event, $bodyStr,$graphArr);
///
if (strpos($graphArr[0],'.svg') !== false){
  $nextpage='GraphListPlotPage.php';
}else{
  $nextpage='GraphListPage.php';
}
///
if($mailFlag==0){
  $mmsg='グラフ送信完了 '.$bodyStr.' '.$mailToAddr.' '.$mailFromAddr;
  writelogd($pgm,$mmsg);
  $msg="#notic#".$user."#ホスト".$host."のグラフ添付メール送信完了";
  branch($nextpage,$msg);
}else if($mailFlag==2){
  $mmsg='グラフ送信不可 '.$bodyStr.' '.$mailToAddr.' '.$mailFromAddr;
  writelogd($pgm,$mmsg);
  $msg='#alert#'.$user.'#送信不可、送信可能phpsendmailAt.php.sendを置き換え>て下さい';
  branch($nextpage,$msg);
}else{
  $mmsg='グラフ送信失敗 '.$bodyStr.' '.$mailToAddr.' '.$mailFromAddr;
  writeloge($pgm,$mmsg);
  $msg="#error#".$user."#ホスト".$host."のグラフ添付メール送信失敗";
  branch($nextpage,$msg);
}
?>

