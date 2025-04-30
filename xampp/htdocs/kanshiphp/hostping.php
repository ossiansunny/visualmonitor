<?php
error_reporting(0);
require_once "alarmwindow.php";

function mailactive($_mailhost){
  $mail_sql="select server from mailserver";
  $mailRows=getdata($mail_sql);
  $mailArr=explode(',',$mailRows[0]);
  if ($mailArr[0]==$_mailhost){
    delstatus('Mail Server InActive');
    delstatus('Mail Server Active');
    setstatus('0','Mail Server Active');;
  }
}

function hostping($_host){  
   /// Windowsのpingパラメータ
   if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
      $rtnCde = exec("ping -n 1 $_host" , $output, $result);
      $breakSw = 0;
      $lineCount = count($output);
      for ($i=0;$i<$lineCount;$i++) {
         $matchLine = preg_match('/ms TTL=/',$output[$i]);
         if ($matchLine == 1)  {
            $breakSw = 1;         
            break;
         }
      }
      if($breakSw == 1){
         return 0;
      } else {
         return 1;
      }
   /// UNIXのpingパラメータ
   } elseif(PHP_OS == 'Linux') {
      $cmd = "ping -c 2 " . $_host;
      $rtnCde = exec($cmd, $output, $res);
      $breakSw = 0;
      $lineCount = count($output);
      for ($i=0;$i<$lineCount;$i++) {
         $matchLine = preg_match("/icmp_seq=1 ttl/",$output[$i]);
         if ($matchLine == 1)  {
            $breakSw = 1;
            break;
         }
      }
      if($breakSw == 1){
         return 0;
      } else {
         return 1;
      }
   } else {
      return 1;
   }
} 
/*
$rtcd=hostping('192.168.1.21');
var_dump($rtcd);
*/
?>
