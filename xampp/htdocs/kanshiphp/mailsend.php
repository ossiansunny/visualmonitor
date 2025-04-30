<?php
error_reporting(E_ERROR);
date_default_timezone_set('Asia/Tokyo');
require_once 'mysqlkanshi.php';
require_once 'phpsendmail.php';
$pgm="mailsend.php";
///

function mailsend($hostArr,$user,$mailType,$param1="",$param2="",$param3="",$message=""){
//echo $hostArr.','.$user.','.$mailType.','.$param1.'<br>';
///          監視管理                 'admin','0',   ,'message'
///          異常発生                  admin .'1'     'snmp','process','oracle'
///          正常復帰                  admin .'2'     'snmp','process','allok'
///          監視開始                  admin .'3'     'snmp','process','allok'
///          ＤＢ異常                  admin .'4'     'sqlまたはtable'
///          アプリ異常                admin .'5'
///          ログイン                  admin .'6'
///          ログアウト                admin .'7'     'reset'
///          障害確認                  admin .'8'
///          障害解決                  admin .'9'
  global $pgm;
  $bodyStr = "";
  $mailOpt='1';
  $host="";
  $viewName="";
  if(is_array($hostArr)){
    $host=$hostArr[0];
    $viewName=$hostArr[5];
    $mailOpt=$hostArr[6];
  }else{
    $host=$hostArr;
  }
  if(!($mailOpt==0 and ($mailType=="1" or $mailType=="2" or $mailType=="3"))){
      switch ($mailType) {
        case '0':
          $headTTL="通知";
          $event="監視管理";
          break;
        case '1':
          $headTTL="異常";
          $event="異常発生";
          break;
        case '2':
          $headTTL="通知";
          $event="正常復帰";
          break;
        case '3':
          $headTTL="通知";
          $state="監視開始";
          break;
        case '4':
          $headTTL="異常";
          $event="DB異常";
          break;
        case '5':
          $headTTL="異常";
          $event="アプリ異常";
          break;
        case '6':
          $headTTL="通知";
          $event="ログイン";
          break;
        case '7':
          $headTTL="通知";
          $event="ログアウト";
          break;
        case '8':
          $headTTL="通知";
          $event="グラフ添付";      
          break;
        case '9':
          $headTTL="通知";
          $event="障害解決";      
          break;
      }

      $timeStamp=date('Y-m-d H:i:s');
      $body = array();
      $headerSql="select title,subtitle from header";
      $headerRows=getdata($headerSql);
      $headArr=explode(',',$headerRows[0]);
      $headTitle=$headArr[0];
      $headSubTitle=$headArr[1];
      $body[0]='***** VisualMonitor ('.$headTTL.') *****';
      $body[1]='From: ' .$headTitle.' '.$headSubTitle.' '.$user;
      $body[2]='Date: ' .$timeStamp;
      $body[3]='Event: '.$event;
      $body[4]='Origin: '.$param1.' '.$param2.' '.$param3;
      $body[5]='Source: ' .$viewName.' '.$host;
      $body[6]='Additional Info:';
      $body[7]=$message;    
      $bodyStr="";
      foreach($body as $item){
        $bodyStr=$bodyStr.$item.PHP_EOL;
      }
  //var_dump($bodyStr);
      $adminSql="select receiver,sender from admintb";
      $adminRows=getdata($adminSql);
      $adminArr=explode(",",$adminRows[0]);
      $adminToAddr=$adminArr[0];
      $adminFromAddr=$adminArr[1];
      $subject="VisualMonitor ".$event;
      $rtnFlag=1;
      $rtnFlag=phpsendmail("","", $adminFromAddr, $adminToAddr, $subject, $bodyStr);
      $mmsg="";
      if($rtnFlag==0){
        $mmsg="送信完了 ".$bodyStr." ".$adminToAddr." ".$adminFromAddr."\r\n";
        writelogd($pgm,"phpsendmailから通知 ".$mmsg);
      }else if($rtnFlag==1){
        $mmsg="送信失敗 ".$bodyStr." ".$adminToAddr." ".$adminFromAddr."\r\n";
        writelogd($pgm,"phpsendmailから通知 ".$mmsg);
      }
      //echo $mmsg.PHP_EOL;
      return $rtnFlag;
  }
}
/*
$rtnCd=mailsend('','admin','7','ログアウト','Logout success','','');
echo 'return mailsend:'.$rtnCd.'<br>'.PHP_EOL;
*/
/*
$hostArr=array("192.168.1.155","","0","1","1","opt","1","80","80:90","80:90","httpd","server.png","public","","0","");
$user="admin";
$mailType="1";
$message="good morming";
$action="snmp";
$snmpType="process";
$snmpValue="oracle";
$rtcd=mailsend($hostArr,$user,$mailType,$action,$snmpType,$snmpValue,$message);
//$subject="";
//$body="";
//mailsendany("192.168.1.139",25,"vmadmin@mydomain.jp","mailuser@mydomain.jp",$subject,$body)
*/
?>

