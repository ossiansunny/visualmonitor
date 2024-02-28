<?php
date_default_timezone_set('Asia/Tokyo');
require_once 'mysqlkanshi.php';
require_once 'BaseFunction.php';
require_once 'phpsendmailAt.php';
$pgm = "graphmailsend.php";
function bodyformat($_from,$_status,$_msg,&$bodystr){
  $_body = array();
  $dte=date('Y-m-d H:i:s');
  $_body[0]='***** VisualMonitor *****';
  $_body[1]='From: ' . $_from;
  $_body[2]='Date: ' .$dte;
  $_body[3]='State: ' .$_status; // 
  $_body[4]='Messages:';
  $_body[5]=$_msg; /// message
  $bodystr='';
  foreach ($_body as $_bodyrec){
    $bodystr=$bodystr.$_bodyrec."\r\n";
  }
}
///
$graphstr=$_GET['graph'];
$grapharray=explode(',',$graphstr);
$host=$_GET['host'];
$user=$_GET['user'];

///
$bodystr = "";
$sql='select * from header';
$hdata=getdata($sql);
$hdarr=explode(',',$hdata[0]);
$header=$hdarr[0];
$sub1=$header; 
$sub2='Attached';
$sub3='graph';
$ttl='**'.$sub1.' '.$sub2. ' ' .$sub3. '**';
$msg='Host '.$host;
bodyformat($header,$ttl,$msg,$bodystr);
$sql="select * from admintb";
$kdata=getdata($sql);
$sdata=explode(',',$kdata[0]);
$toaddr=$sdata[3]; /// mail to addr
$fromaddr=$sdata[4]; /// mail from addr
///
///
$flg=phpsendmailat("", "", $fromaddr, $toaddr, $ttl, $bodystr,$grapharray);
///
///
if (strpos($grapharray[0],'.svg') !== false){
  $nextpage='GraphListPlotPage.php';
}else{
  $nextpage='GraphListPage.php';
}
if($flg==0){
  $mmsg='success '.$bodystr.' '.$toaddr.' '.$fromaddr;
  writelogd($pgm,$mmsg);
  $msg="#notic#".$user."#ホスト".$host."のグラフ添付メール送信完了";
  branch($nextpage,$msg);
}else{
  $mmsg='failed '.$bodystr.' '.$toaddr.' '.$fromaddr;
  writeloge($pgm,$mmsg);
  $msg="#error#".$user."#ホスト".$host."のグラフ添付メール送信失敗";
  branch($nextpage,$msg);
}

?>

