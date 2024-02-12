<?php
error_reporting(0);
require_once "alarmwindow.php";

function mailactive($mailhost){
  $sql="select server from mailserver";
  $rows=getdata($sql);
  $row=explode(',',$rows[0]);
  if ($row[0]==$mailhost){
    delstatus('Mail Server InActive');
    delstatus('Mail Server Active');
    setstatus('0','Mail Server Active');;
  }
}

function hostping($host){
   /// WindowsとUNIXのpingパラメータが違う
   $cmd = "ping -c 1 " . $host;
   $r = exec($cmd, $output, $res);
   $sw = 0;
   $c = count($output);
   for ($i=0;$i<$c;$i++) {
      $reg = preg_match('/icmp_seq=1 ttl/',$output[$i]);
      if ($reg == 1)  {
         $sw = 1;         
         break;
      }
   }
   if($sw == 1){
      mailactive($host);
      return 0;
   } else {
      return 1;
   }
} 
/*
$rtn=hostping('192.168.1.139');
var_dump($rtn);
*/
?>

