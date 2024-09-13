<?php

date_default_timezone_set('Asia/Tokyo');
require_once 'mysqlkanshi.php';
require_once 'phpsendmail.php';
///
function mailsendevent($_eventLog,$_eventAdmin,$_eventCloseNo,$_eventConfClose,$_message){
  /// 管理DB展開
  $admin_sql="select * from admintb";
  $adminRows=getdata($admin_sql);
  $adminArr=explode(',',$adminRows[0]);
  $mailToAddr=$adminArr[3];
  $mailFromAddr=$adminArr[4];
  $adjSubject=$adminArr[5]; /// subject 
  $adminBody=$adminArr[6]; /// body 
  /// 引数展開 edata 
  /// (host,eventtime,eventtype,snmptype,snmpvalue,kanrisha,kanrino,mailsend,message)
  $eventArr=explode(',',$_eventLog);
  $host=$eventArr[0];
  $eventTime=$eventArr[1];
  //$evenType=$eventArr[2];
  $eventSnmpType=$eventArr[3];
  //$evenSnmpValue=$eventArr[4];
  //$kanrisha=$eventArr[5];
  //$kanrino=$eventArr[6];
  //$mailsend=$eventArr[8];
  //$emsg=$eventArr[9];
  /*
  $snmpType="";
  switch ($eventSnmpType) { ///snmptype
    case '1': $snmpType ="CPU limit exceeded"; break;
    case '2': $snmpType ="Memory limit exceeded"; break;
    case '3': $snmpType ="HDD limit exceeded"; break;
    case '4': $snmpType ="PORT closed"; break;
    case '5': $snmpType ="Process not running"; break;
    default: $snmpType = "イベント管理"; break;
  }
  */
  $cnfCls="";
  switch ($_eventConfClose) { ///confclose
    case '1': $cnfCls ="障害確認"; break;
    case '2': $cnfCls ="処理クローズ"; break;
    case '3': $cnfCls ="メール送信"; break;
    case '4': $cnfCls ="ログ削除"; break;
    case '5': $cnfCls ="メモ通知"; break;
    default: $cnfCls= "その他"; break;
  }     
  $body = array();
  $timeStamp=date('Y-m-d H:i:s');
  $header_sql="select * from header";
  $reaerRows=getdata($header_sql);
  $headerArr=explode(',',$headerRows[0]);
  $body[0]='***** VisualMonitor (event) *****';
  $body[1]='From: ' . $headerArr[0];
  $body[2]='Date: ' .$timeStamp;
  $body[3]='User code: '.$_eventAdmin; 
  $body[4]='Managed number: ' .$_eventCloseNo; 
  $body[5]='Service: イベント管理('.$cnfCls.')'; 
  $body[6]='Address: ' .$host; 
  $body[7]='TimeStamp: '.$eventTime;
  $body[8]='State: 処理完了'; 
  $body[9]='Additional Info:';
  $body[10]=$_message; 
  if($adminBody!=''){
    $body[11]='Message:';
    $body[12]=$adminBody;
  } 
  $bodyStr='';
  $bodyCount=count($body);
  for($index=0;$index<$bodyCount;$index++){
    $bodyStr=$bodyStr.$body[$index]."\r\n";
  }    
  $title='';
  if(1 === preg_match('/</', $adjSubject)){
    $adjSubject=str_replace('<host>',$host,$adjSubject);
    $adjSubject=str_replace('<status>',$cnfCls,$adjSubject);
    $adjSubject=str_replace('<title>',$headerArr[0],$adjSubject);
    $title=$adjSubject;
  }else{
    $subj1='イベント管理'; ///Action Complete
    $subj2=$cnfCls;
    $subj3=$host;
    $title='** '.$subj1.' '.$subj2. '/' .$subj3. ' **'; 
  }
  $flag=phpsendmail("", "", $mailFromAddr, $mailToAddr, $title, $bodyStr);
  if($flag==0){
    $mmsg='success '.$bodyStr.' '.$mailToAddr.' '.$mailFromAddr."\r\n";
    writelogd('sendmailevent debug',$mmsg);
  }else{
    $mmsg='failed '.$bodyStr.' '.$mailToAddr.' '.$mailFromAddr."\r\n";
    writeloge('sendmailevent debug',$mmsg);
  }
  return $flag;
}
?>

