<?php
error_reporting(E_ERROR);
date_default_timezone_set('Asia/Tokyo');
require_once 'mysqlkanshi.php';
require_once 'phpsendmail.php';
$pgm="mailsendany.php";
///
function bodyformat($_from,$_status,$_msg,&$bodystr){
  $_body = array();
  $dte=date('Y-m-d H:i:s');
  $_body[0]='***** VisualMonitor *****';
  $_body[1]='From: ' . $_from;
  $_body[2]='Date: ' .$dte;
  $_body[3]='State: ' .$_status; // 
  $_body[4]='Messages:';
  $_body[5]=$_msg; /// message
  $bodyStr='';
  foreach ($_body as $_bodyrec){
    $bodyStr=$bodyStr.$_bodyrec."\r\n";
  }
}

function mailsendany($_mailType,$_from,$_to,$_subject,$_body){
  $bodyStr = "";
  $header_sql='select * from header';
  $headerRows=getdata($header_sql);
  $headerArr=explode(',',$headerRows[0]);
  $adjSubject=$_subject;
  if($_mailType=='adminsubject'){
    $header=$headerArr[0];
    $check='';
    $adjSubject=str_replace('<host>','any',$adjSubject);
    $adjSubject=str_replace('<status>',$_mailType,$adjSubject);
    $adjSubject=str_replace('<title>',$headerArr[0],$adjSubject);
    $title=$adjSubject;
    bodyformat($header,$title,$_body,$bodyStr);
  }elseif($_mailType=='mysql'){
    $hsarr=explode(' ',$_body);
    $header=$hsarr[2];
    $check=' mysql起動遅延';
    $subj1=$header; 
    $subj2='デーモン';
    $subj3=$_subject;
    $title='**'.$subj1.' '.$subj2. ' ' .$subj3. '**';
    bodyformat($header,$title,$_body,$bodyStr);
  }else{
    /// loginlogout
    $header=$headerArr[0];
    $check='';
    $subj1=$headerArr[0]; 
    ///$subj2='デーモン';
    $subj3=$_subject;
    $title='**'.$subj1. ' '.$subj3.'**';
    bodyformat($header,$title,$_body,$bodyStr); 
  }
  if ($_to=="" or $_from==""){
    /// get mail from, to address
    $admin_sql="select * from admintb";
    $adminRows=getdata($admin_sql);
    $adminArr=explode(',',$adminRows[0]);
  }
  if ($_to==""){
    $toAddr=$adminArr[3]; /// mail to addr
  }else{
    $toAddr=$_to;
  }
  if ($_from==""){
    $fromAddr=$adminArr[4]; /// mail from addr
  }else{
    $fromAddr=$_from;
  }
  $rtnFlag=1;
  $rtnFlag=phpsendmail("", "", $fromAddr, $toAddr, $title, $bodyStr);
  //print("phpsendmail done\n");
  $mmsg='';
  if($rtnFlag==0){
    $mmsg='送信完了 '.$bodyStr.' '.$toAddr.' '.$fromAddr."\r\n";
    writelogd($pgm,'phpsendmailから通知 ',$mmsg);
  }else if($rtnFlag==1){
    $mmsg='送信失敗 '.$bodyStr.' '.$toAddr.' '.$fromAddr."\r\n";
    writeloge($pgm,'phpsendmailから通知 ',$mmsg);
  }
  return $rtnFlag;

}
//mailsendany("hostupdate","vmadmin@mydomain.jp","mailuser@mydomain.jp","subject","boidy")
?>

