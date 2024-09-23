<?php
date_default_timezone_set('Asia/Tokyo');
require_once 'mysqlkanshi.php';
require_once 'phpsendmail.php';
$pgm="mailsendsnmp.php";
function mailsendsnmp($_hostArr,$_snmpType,$_snmpValue,$_updown){
  /// -------------------- 
  /// 管理DB Read & 展開
  $admin_sql="select * from admintb";
  $adminRows=getdata($admin_sql);
  $adminArr=explode(',',$adminRows[0]);
  $mailFromAddr=$adminArr[4]; /// sender
  $mailToAddr=$adminArr[3]; /// recipient
  $adjSubject=$adminArr[5]; /// subject admintb
  $adminBody=$adminArr[6]; /// body admintb
  /// $_hostArrは配列で来るから配列にする必要が無い
  $host=$_hostArr[0];
  $viewName=$_hostArr[5];
  $snmpName=""; 
  $subj0='Problem';
  $subj1='Alert';
  $noticeType='ALERT';
  $stat='';
  $info='';  
  ///$snmpType: 1=CPU 2=RAM 3=DISK 4=Process 5=Port
  ///$snmpValue: 1-3(n:58 w=60 c=80) 4(httpd;snmpd) 5(80;443)
  ///$updown: 1=前回データなし（ホスト新規、修正） 2=前回データあり  
  ///前回データなし  n:xx Subj=Info stat=Monitoring started       info=xx% normal
  ///                c:xx Subj=Plob stat=Critical value exceeded  info=xx% exceeded
  ///前回データあり  n:xx Subj=Info stat=Monitoring normal        info=xx% normal
  ///                c:xx Subj=Plob stat=Critical value exceeded  info=xx% exceeded
  switch ($_snmpType) { 
    case '1':
    case '2':
    case '3':
      $_wcMark=explode(':',$_snmpValue); /// w:80 c:90
      $info=$_wcMark[1].'% exceeded';    
      if($_wcMark[0]=='w'){
        $stat='Warning value exceeded';      
      }else if($_wcMark[0]=='c'){
        $stat='Critical value exceeded';      
      }else{
        if($_updown=='1'){
          $stat='Monitoring started';
        }else{
          $stat='Monitoring normal';
        }
        $info=$_wcMark[1].'% normal';
        $subj0='Information';
        $subj1='Info';
        $noticeType='INFO';
      }
      if($_snmpType=='1'){
        $snmpName='CPU';
      }else if($_snmpType=='2'){
        $snmpName='Memory';
      }else{
        $snmpName='Disk';
      }
      break;
    case '4': /// eg. snmpd;ftp
      $snmpName='Process';
      if($_snmpValue=='' || $_snmpValue=='allok'){
        $stat='All Process running';
        $info='Process normal';
        $subj0='Information';
        $subj1='Info';
        $noticeType='INFO';
      }else{
        $stat='Process not running';
        $info=$_snmpValue.' process not running';
      }
      break;
    case '5': /// eg. 80;443
      $snmpName='TCPPort';
      if($_snmpValue=='' || $_snmpValue=='allok'){
        $stat='No TCP Port closing';
        $info='TCP Port Normal';
        $subj0='Information';
        $subj1='Info';
        $noticeType='INFO';
      }else{
        $stat='TCP Port closing';
        $info=$_snmpValue.' TCP port closing';
      }
      break;     
    default:
      $stat='Unknown';
      break;
  }
  $timeStamp=date('Y-m-d H:i:s');
  $body = array();
  $header_sql="select * from header";
  $headerRows=getdata($header_sql);
  $headerArr=explode(',',$headerRows[0]);
  $body[0]='***** VisualMonitor (snmp) *****';
  $body[1]='From: ' . $headerArr[0];
  $body[2]='Notification Type: '.$noticeType;
  $body[3]='Date: ' .$timeStamp;
  $body[4]='Service: ' .'SNMP: ' . $snmpName;
  $body[5]='HOST: ' .$viewName;
  $body[6]='Address: ' .$host;
  $body[7]='State: ' .$stat;
  $body[8]='Additional Info:';
  $body[9]=$info; 
  if($adminBody!=''){
    $body[10]='Message:';
    $body[11]=$adminBody;
  }
  $bodystr='';
  $cc=count($body);
  for($cs=0;$cs<$cc;$cs++){
    $bodystr=$bodystr.$body[$cs]."\r\n";
  }    
  if(1 === preg_match('/</', $adjSubject)){
    $adjSubject=str_replace('<host>',$host,$adjSubject);
    $adjSubject=str_replace('<status>',$stat,$adjSubject);
    $adjSubject=str_replace('<title>',$headerArr[0],$adjSubject);
    $title=$adjSubject;
  }else{
    $subj2=$viewName;
    $subj3='SNMP'; /// PING|SERVICE
    $subj4=$stat;
    $title='**'.$subj0.' Service ' .$subj1. ' ' .$subj2. '/' .$subj3. ' is ' .$subj4. '**'; 
  }
    
  $flag=phpsendmail("", "", $mailFromAddr, $mailToAddr, $title, $bodystr);
  if($flag==0){
    $mmsg='success '.$bodystr.' '.$mailToAddr.' '.$mailFromAddr."\r\n";
    writelogd('mailsendsnmp debug',$mmsg);
    //print $mmsg;
    return 0;
  }else{
    $mmsg='failed '.$bodystr.' '.$mailToAddr.' '.$mailFromAddr."\r\n";
    //print $mmsg;
    writelogd('mailsendsnmp debug',$mmsg);
    return 1;
  }
}
?>

