<?php
error_reporting(E_ERROR);
date_default_timezone_set('Asia/Tokyo');
require_once 'mysqlkanshi.php';
require_once 'phpsendmail.php';
$pgm="mailsendany.php";
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

function mailsendany($type,$from,$to,$subject,$msg){
  $bodystr = "";
  $sql='select * from header';
  $hdata=getdata($sql);
  $hdarr=explode(',',$hdata[0]);
  $csubject=$subject;
  if($type=='adminsubject'){
    $header=$hdarr[0];
    $check='';
    $csubject=str_replace('<host>','any',$csubject);
    $csubject=str_replace('<status>',$type,$csubject);
    $csubject=str_replace('<title>',$hdarr[0],$csubject);
    $ttl=$csubject;
    bodyformat($header,$ttl,$msg,$bodystr);
  }elseif($type=='mysql'){
    $hsarr=explode(' ',$msg);
    $header=$hsarr[2];
    $check=' mysql起動遅延';
    $sub1=$header; 
    $sub2='デーモン';
    $sub3=$subject;
    $ttl='**'.$sub1.' '.$sub2. ' ' .$sub3. '**';
    bodyformat($header,$ttl,$msg,$bodystr);
  }else{
    $header=$hdarr[0];
    $check='';
    $sub1=$hdarr[0]; 
    ///$sub2='デーモン';
    $sub3=$subject;
    $ttl='**'.$sub1. ' '.$sub3.'**';
    bodyformat($header,$ttl,$msg,$bodystr); 
  }
  /// get mail from, to address
  $sql="select * from admintb";
  $kdata=getdata($sql);
  $sdata=explode(',',$kdata[0]);
  $toaddr=$sdata[3]; /// mail to addr
  $fromaddr=$sdata[4]; /// mail from addr
  $flg=phpsendmail("", "", $fromaddr, $toaddr, $ttl, $bodystr);
  $mmsg='';
  if($flg==0){
    $mmsg='success '.$bodystr.' '.$toaddr.' '.$fromaddr."\r\n";
    writelogd('mailsendany debug',$mmsg);
  }else{
    $mmsg='failed '.$bodystr.' '.$toaddr.' '.$fromaddr."\r\n";
    writelogd('mailsendany debug',$mmsg);
  }
  return $flg;

}
?>
