<?php
error_reporting(0);

   /// WindowsとUNIXのncatコマンドは同じだが
   /// Windowsは先頭にcmd.exeが必要
   $output=null;
   $result=null;
   
   $cmd='"c:\program files (x86)\nmap\ncat.exe" -zv -w 0.5 192.168.1.22 443';
   exec('cmd /s /c "'.$cmd.'"',$output,$result);
   var_dump($output);
   var_dump($result);   
 
?>
