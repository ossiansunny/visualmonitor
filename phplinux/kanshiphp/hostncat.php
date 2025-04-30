<?php
error_reporting(0);
require_once "varread.php";
///
$vpath_ncat="";
$vpathParam=array("vpath_ncat");
$vpathArr=pathget($vpathParam);
$vpathCount=count($vpathArr);
if ($vpathCount==1){
  $vpath_ncat=$vpathArr[0];
}
///

function hostncat($host,$port){
  /// WindowsとUNIXのncatコマンドは同じだが
  /// Windowsは先頭にcmd.exeが必要
  global $vpath_ncat, $vpathCount;
  if ($vpathCount==1){ 
    $output=null;
    $result=null;
    $cmd="";
    if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
      ///  Windows
      $cmd='"'.$vpath_ncat.'" -z -w 0.5 '.$host.' '.$port;    
      exec('cmd /s /c "'.$cmd.'"',$output,$result);      
    }else{
      /// Unix/Linux
      $cmd='"'.$vpath_ncat.'" -z -w 0.5 '.$host.' '.$port;
      exec($cmd,$output,$result);
    }
     
  }else{
    /// vpath_ncatなし
    $result=1;
  }
  return $result;  
}

/*
$rtn=hostncat('192.168.1.18','587');
if ($rtn==0) {
  echo 'open';
}else{
  echo 'close';
}
*/

?>
