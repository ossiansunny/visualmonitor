<?php

date_default_timezone_set('Asia/Tokyo');
require_once 'mysqlkanshi.php';
require_once 'phpsendmail.php';
function mailsendevent($edata,$ekanrimei,$ekanrino,$econfclose,$message){
  // 管理DB展開
  $sql="select * from admintb";
  $kdata=getdata($sql);
  $sdata=explode(',',$kdata[0]);
  $toaddr=$sdata[3];
  $fromaddr=$sdata[4];
  $csubject=$sdata[5]; // subject admintb
  $cbody=$sdata[6]; // body admintb
  // 引数展開 edata 
  // (host,eventtime,eventtype,snmptype,snmpvalue,kanrisha,kanrino,mailsend,message)
  $eadata=explode(',',$edata);
  //var_dump($eadata);
  $host=$eadata[0];
  $etime=$eadata[1];
  $eetype=$eadata[2];
  //$estype=$eadata[3];
  $esvalue=$eadata[4];
  $kanrisha=$eadata[5];
  $kanrino=$eadata[6];
  //$confclose=$eadata[7];
  $mailsend=$eadata[8];
  $emsg=$eadata[9];
 
  switch ($eadata[3]) { //snmptype
    case '1': $estype ="CPU limit exceeded"; break;
    case '2': $estype ="Memory limit exceeded"; break;
    case '3': $estype ="HDD limit exceeded"; break;
    case '4': $estype ="PORT closed"; break;
    case '5': $estype ="Process not running"; break;
    default: $estype = "イベント管理"; break;
  }
  switch ($econfclose) { //confclose
    case '1': $cfcl ="障害確認"; break;
    case '2': $cfcl ="処理クローズ"; break;
    case '3': $cfcl ="メール送信"; break;
    case '4': $cfcl ="ログ削除"; break;
    case '5': $cfcl ="メモ通知"; break;
    default: $cfcl= "その他"; break;
  }     
  $body = array();
  $dte=date('Y-m-d H:i:s');
  $sql="select * from header";
  $hdata=getdata($sql);
  $hdarr=explode(',',$hdata[0]);
  $body[0]='***** VisualMonitor (event) *****';
  $body[1]='From: ' . $hdarr[0];
  $body[2]='Date: ' .$dte;
  $body[3]='User code: '.$ekanrimei; //$kanrimei
  $body[4]='Managed number: ' .$ekanrino; //$kanrino
  $body[5]='Service: イベント管理('.$cfcl.')'; // cpu/ram...ans value
  $body[6]='Address: ' .$host; // $host
  $body[7]='TimeStamp: '.$etime;
  $body[8]='State: 処理完了'; // $confrm/close
  $body[9]='Additional Info:';
  $body[10]=$message; // message
  if($cbody!=''){
    $body[11]='Message:';
    $body[12]=$cbody;
  } 
  $bodystr='';
  $cc=count($body);
  for($cs=0;$cs<$cc;$cs++){
    $bodystr=$bodystr.$body[$cs]."\r\n";
  }    
  $ttl='';
  if(1 === preg_match('/</', $csubject)){
    $csubject=str_replace('<host>',$host,$csubject);
    $csubject=str_replace('<status>',$cfcl,$csubject);
    $csubject=str_replace('<title>',$hdarr[0],$csubject);
    $ttl=$csubject;
  }else{
    $sub1='イベント管理'; //Action Complete
    $sub2=$cfcl;
    $sub3=$host;
    //$sub3=$etime;
    $ttl='** '.$sub1.' '.$sub2. '/' .$sub3. ' **'; 
    //$ttl='**'.$sub1.' '.$sub2. '/' .$sub3. ' EventLog**'; 
  }
  //$toaddr=$sdata[3];
  //$fromaddr='From: '.$sdata[4];
  //$flg=mb_send_mail($toaddr,$ttl,$bodystr,$fromaddr);
  //$sql="select * from mailserver";
  //$kdata=getdata($sql);
  //$mdata=explode(',',$kdata[0]);
  //$mserver=$mdata[0]; // server host address
  //$mport=$mdata[1]; // server listen port
  $flg=phpsendmail("", "", $fromaddr, $toaddr, $ttl, $bodystr);
  if($flg==0){
    $mmsg='success '.$bodystr.' '.$toaddr.' '.$fromaddr."\r\n";
    //echo $mmsg;
    writelogd('sendmailevent debug',$mmsg);
  }else{
    $mmsg='failed '.$bodystr.' '.$toaddr.' '.$fromaddr."\r\n";
    //echo $mmsg;
    writeloge('sendmailevent debug',$mmsg);
  }
  return $flg;

}
/*
$datax='192.168.1.3,210704065132,2,1,w:60,,,0,0,message'; // statistics record
$kanrimeix='21001';
$kanrinox='1111111111';
$confclosex='1'; //1=確認済 2=close
$message='aaaa';
mailsendevent($datax,$kanrimeix,$kanrinox,$confclosex,$message);
*/
?>
