<?php
date_default_timezone_set('Asia/Tokyo');
require_once 'mysqlkanshi.php';
require_once 'phpsendmail.php';
$pgm="mailsendsnmp.php";
function mailsendsnmp($mdata,$mtype,$mvalue,$updown){
  /// -------------------- Gmailに接続する時間がかかるのが、処理に時間がかかる
  /// 管理DB Read & 展開
  $sql="select * from admintb";
  $kdata=getdata($sql);
  $sdata=explode(',',$kdata[0]);
  $fromaddr=$sdata[4]; /// sender
  $toaddr=$sdata[3]; /// recipient
  $csubject=$sdata[5]; /// subject admintb
  $cbody=$sdata[6]; /// body admintb
  /// 引数 Read & 展開、$mdataは配列で来るから配列にする必要が無い
  ///$madata=explode(',',$mdata);
  $madata=$mdata;
  $host=$madata[0];
  $viewn=$madata[5];
  $snmpt=""; 
  $prsn='SNMP'; 
  $sub0='Problem';
  $sub1='Alert';
  $prorec='ALERT';
  $stat='';
  $info='';  

  ///$mdata: 管理データの配列
  ///$mtype: 1=CPU 2=RAM 3=DISK 4=Process 5=Port
  ///$mvalue: 1-3(n:58 w=60 c=80) 4(httpd;snmpd) 5(80;443)
  ///$updown: 1=前回データなし（ホスト新規、修正） 2=前回データあり  
  ///前回データなし  n:xx Subj=Info stat=Monitoring started       info=xx% normal
  ///                c:xx Subj=Plob stat=Critical value exceeded  info=xx% exceeded
  ///前回データあり  n:xx Subj=Info stat=Monitoring normal        info=xx% normal
  ///                c:xx Subj=Plob stat=Critical value exceeded  info=xx% exceeded

  switch ($mtype) { ///snmptype 1=CPU 2=RAM 3=Disk 4=Process 5=PORT 
    case '1':
    case '2':
    case '3':
      $wcflag=explode(':',$mvalue); /// w:80 c:90
      $info=$wcflag[1].'% exceeded';    
      if($wcflag[0]=='w'){
        $stat='Warning value exceeded';      
      }else if($wcflag[0]=='c'){
        $stat='Critical value exceeded';      
      }else{
        if($updown=='1'){
          $stat='Monitoring started';
        }else{
          $stat='Monitoring normal';
        }
        $info=$wcflag[1].'% normal';
        $sub0='Information';
        $sub1='Info';
        $prorec='INFO';
      }
      if($mtype=='1'){
        $snmpt='CPU';
      }else if($mtype=='2'){
        $snmpt='Memory';
      }else{
        $snmpt='Disk';
      }
      break;
    case '4': /// snmpd;ftp
      $snmpt='Process';
      if($mvalue=='' || $mvalue=='allok'){
        $stat='All Process running';
        $info='Process normal';
        $sub0='Information';
        $sub1='Info';
        $prorec='INFO';
      }else{
        $stat='Process not running';
        $info=$mvalue.' process not running';
      }
      break;
    case '5': /// 80;443
      $snmpt='TCPPort';
      if($mvalue=='' || $mvalue=='allok'){
        $stat='No TCP Port closing';
        $info='TCP Port Normal';
        $sub0='Information';
        $sub1='Info';
        $prorec='INFO';
      }else{
        $stat='TCP Port closing';
        $info=$mvalue.' TCP port closing';
      }
      break;     
    default:
      $stat='Unknown';
      break;
  }
  $dte=date('Y-m-d H:i:s');
  $body = array();
  $sql="select * from header";
  $hdata=getdata($sql);
  $hdarr=explode(',',$hdata[0]);
  $body[0]='***** VisualMonitor (snmp) *****';
  $body[1]='From: ' . $hdarr[0];
  $body[2]='Notification Type: '.$prorec;
  $body[3]='Date: ' .$dte;
  $body[4]='Service: ' .$prsn. ':' . $snmpt;
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
  if(1 === preg_match('/</', $csubject)){
    $csubject=str_replace('<host>',$host,$csubject);
    $csubject=str_replace('<status>',$stat,$csubject);
    $csubject=str_replace('<title>',$hdarr[0],$csubject);
    $ttl=$csubject;
  }else{
    $sub2=$viewn;
    $sub3='SNMP'; /// PING|SERVICE
    $sub4=$stat;
    $ttl='**'.$sub0.' Service ' .$sub1. ' ' .$sub2. '/' .$sub3. ' is ' .$sub4. '**'; 
  }
    
  $flg=phpsendmail("", "", $fromaddr, $toaddr, $ttl, $bodystr);
  if($flg==0){
    $mmsg='success '.$bodystr.' '.$toaddr.' '.$fromaddr."\r\n";
    writelogd('mailsendsnmp debug',$mmsg);
    //echo $mmsg;
    return 0;
  }else{
    $mmsg='failed '.$bodystr.' '.$toaddr.' '.$fromaddr."\r\n";
    echo $mmsg;
    writeloge('mailsendsnmp debug',$mmsg);
    return 1;
  }
}
?>
