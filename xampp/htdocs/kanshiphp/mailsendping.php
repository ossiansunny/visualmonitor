<?php
date_default_timezone_set('Asia/Tokyo');
require_once 'mysqlkanshi.php';
require_once 'phpsendmail.php';
///
$pgm="mailsendping.php";
///
function ping($mdata,$mtype){
  /// 管理DB展開 mailアドレスで必要
  global $pgm;
  $sql="select * from admintb";
  $kdata=getdata($sql);
  $sdata=explode(',',$kdata[0]);
  $toaddr=$sdata[3];
  $fromaddr=$sdata[4];
  $csubject=$sdata[5]; /// subject admintb
  $cbody=$sdata[6]; /// body admintb
  /// 引数展開
  $madata=explode(',',$mdata); ///host data
  $host=$madata[0];
  $viewn=$madata[5];
  $action=$madata[4];
  $sub0='';
  $sub1='';
  $stat='';
  $info='';
  $prorec=$mtype;
  $snmpt='';
  if($action=='1' || $action=='4'){
    $prsn='PING';
  }elseif($action=='2' || $action=='3'){
    $prsn='PING(SNMP)';
  } 
  
  if($mtype=='PROBLEM'){
    $stat='DOWN';
    $sub0='Problem';
    $sub1='Alert';
    $info='PING Status - Packet loss/ Timed out';
  }elseif($mtype=='RECOVERY'){
    $stat='UP';
    $sub0='Information';
    $sub1='Recovery';
    $info='PING Status - Packet loss = 0%';
  }else{
    $stat='UNKNOWN';
  }  
 
  $body = array();
  $dte=date('Y-m-d H:i:s');
  $sql="select * from header";
  $hdata=getdata($sql);
  $hdarr=explode(',',$hdata[0]);
  $body[0]='***** VisualMonitor (ping) *****';
  $body[1]='From: ' .$hdarr[0];
  $body[2]='Notification Type: '.$prorec;
  $body[3]='Date: ' .$dte;
  $body[4]='Service: ' .$prsn. ":" . $snmpt;
  $body[5]='HOST: ' .$viewn;
  $body[6]='Address: ' .$host;
  $body[7]='State: ' .$stat;
  $body[8]='Additional Info:';
  $body[9]=$info; 
  if($cbody!=''){
    $body[10]='Message:';
    $body[11]=$cbody;
  }
  $bodystr='';
  $cc=count($body);
  for($cs=0;$cs<$cc;$cs++){
    $bodystr=$bodystr.$body[$cs]."\r\n";
  }
  $sub2=$viewn;
  $sub3=$prsn; /// PING|SERVICE
  $sub4=$stat; ///$sub4='WARNING|Down|UP|UNKNOWN|CRITICAL|RECOVERY';
  $ttl='**'.$sub0.' Service ' .$sub1. ' ' .$sub2. '/' .$sub3. ' is ' .$sub4. '**'; 
  $flg=phpsendmail("", "", $fromaddr, $toaddr, $ttl, $bodystr);
  if($flg==0){
    $msg="send mail success by phpsendmail";
    writelogd($pgm,$msg);
  }else{
    $msg="send mail failed by phpsendmail";
    writeloge($pgm,$msg);
  }
  return $flg;
  
}
?>

