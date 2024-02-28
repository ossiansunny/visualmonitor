<?php
error_reporting(0);
require_once "alarmwindow.php";

function mailactivencat($mailhost){
  $sql="select server from mailserver";
  $rows=getdata($sql);
  $row=explode(',',$rows[0]);
  if ($row[0]==$mailhost){
    delstatus('Mail Server InActive');
    delstatus('Mail Server Active');
    setstatus('0','Mail Server Active');;
  }
}

function hostncat($host){
   /// WindowsとUNIXのncatコマンドは同じだが
   /// Windowsは先頭にcmd.exeが必要
   $output=null;
   $result=null;
   $cmd="cmd.exe ncat -zv -w 2 ".$host." 22";
   $rtn=exec($cmd, $output, $result);
   if($result == 0){
      mailactivencat($host);
      return 0;
   } else {
      return 1;
   }
} 

?>
