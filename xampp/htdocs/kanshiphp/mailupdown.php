<?php
date_default_timezone_set('Asia/Tokyo');
require_once 'mysqlkanshi.php';
require_once 'phpsendmail.php';
///
$pgm="mailupdown.php";
///
function mailupdown($_hostRow,$_noticeType){
  /// 管理DB展開 mailアドレスで必要
  global $pgm;
  $admin_sql="select receiver,sender,subject,body from admintb";
  $adminRows=getdata($admin_sql);
  $adminArr=explode(',',$adminRows[0]);
  $toMailAddr=$adminArr[0];
  $fromMailAddr=$adminArr[1];
  $adjSubject=$adminArr[2]; /// subject admintb
  $adjBody=$adminArr[3]; /// body admintb
  /// 引数展開
  $hostArr=explode(',',$_hostRow); ///host data
  $host=$hostArr[0];
  $viewn=$hostArr[5];
  $action=$hostArr[4];
  $subj0='';
  $subj1='';
  $stat='';
  $info='';
  $snmpt='';
  $prsn='';
  if($action=='1' || $action=='4'){
    $prsn='PING';
  }elseif($action=='2' || $action=='3'){
    $prsn='PING(SNMP)';
    $snmpt='snmp';
  } 
  
  if($_noticeType=='PROBLEM'){
    $stat='DOWN';
    $subj0='Problem';
    $subj1='Alert';
    $info='PING Status - Packet loss/ Timed out';
  }elseif($_noticeType=='RECOVERY'){
    $stat='UP';
    $subj0='正常';
    $subj1='復旧';
    $info='PING Status - Packet loss = 0%';
  }else{
    $stat='UNKNOWN';
  }  
 
  $body = array();
  $dte=date('Y-m-d H:i:s');
  $header_sql="select title from header";
  $headerRows=getdata($header_sql);
  $headTitle=$headerRows[0];
  $body[0]='***** VisualMonitor (ping) *****';
  $body[1]='From: ' .$headTitle;
  $body[2]='Notification Type: '.$_noticeType;
  $body[3]='Date: ' .$dte;
  $body[4]='Service: ' .$prsn. ":" . $snmpt;
  $body[5]='HOST: ' .$viewn;
  $body[6]='Address: ' .$host;
  $body[7]='State: ' .$stat;
  $body[8]='Additional Info:';
  $body[9]=$info; 
  if($adjBody!=''){
    $body[10]='Message:';
    $body[11]=$adjBody;
  }
  $bodyStr='';
  $cc=count($body);
  for($cs=0;$cs<$cc;$cs++){
    $bodyStr=$bodyStr.$body[$cs]."\r\n";
  }
  $subj2=$viewn;
  $subj3=$prsn; /// PING|SERVICE
  $subj4=$stat; ///$subj4='WARNING|Down|UP|UNKNOWN|CRITICAL|RECOVERY';
  $title='**'.$subj0.' ' .$subj1. ' ' .$subj2. '/' .$subj3. ' is ' .$subj4. '**'; 
  $flg=phpsendmail("", "", $fromMailAddr, $toMailAddr, $title, $bodyStr);
  if($flg==0){
    $msg="phpsendmailにより送信完了しました";
    writelogd($pgm,$msg);
  }else{
    $msg="phpsendmailが送信に失敗しました";
    writeloge($pgm,$msg);
  }
  return $flg;
  
}
?>

