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
   $r = exec("ping -n 1 $host" , $output, $result);
   $sw = 0;
   $c = count($output);
   for ($i=0;$i<$c;$i++) {
      $reg = preg_match('/ms TTL=/',$output[$i]);
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
?>

