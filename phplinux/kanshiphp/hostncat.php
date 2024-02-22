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
   /// WindowsとUNIXのncatコマンドは同じ
   /// Windowsはncatコマンドの前にcmd.exeが必要
   /// Windowsはnmapをインストールするとncatが使える
   $output=null;
   $result=null;
   $cmd="ncat -z -w 2 ".$host." 80";           /// Linux/Unix
   //$cmd="cmd.exe ncat -z -w 2 ".$host." 80"; /// Windows
   $rtn=exec($cmd, $output, $result);
   //echo $host.' hostncat return='.$result.'<br>';
   if($result == 0){
      mailactivencat($host);
      return 0;
   } else {
      return 1;
   }
} 
/*
$rtn=hostncat('gcp.sunnyblue.mydns.jp');
if ($rtn==0) {
  echo 'active';
}else{
  echo 'not active';
}
*/
?>
