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
/// Unix/Linux マネージャ ncat
///
function hostncat($host,$port){
  /// WindowsとUNIXのncatコマンドは同じだが
  /// Windowsは先頭にcmd.exeが必要
  global $vpath_ncat, $vpathCount;
  if ($vpathCounr==1){ 
    $output=null;
    $result=null;
    $cmd="";
    if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
      ///  Windows
      $cmd='"'.$vpath_ncat.'/ncat.exe"'.' -zv -w 0.5 '.$host.' '.$port;
      //$cmd='"'.$vpath_ncat.'" -zv -w 0.5 '.$host.' '.$port;
      //$cmd='"c:/program files (x86)/nmap/ncat.exe" -zv -w 0.5 '.$host.' '.$port;
      exec('cmd /s /c "'.$cmd.'"',$output,$result);
      //exec('cmd /s /c ""c:/program files (x86)/nmap/ncat.exe" -zv -w 0.5 192.168.1.22 443"',$output,$result);
      //exec('cmd /s /c ""c:/program files (x86)/nmap/ncat.exe" -zv -w 0.5 '.$host.' '.$port.'"',$output,$resu
    }else{
      /// Unix/Linux
      $cmd="ncat -zv -w 0.5 ".$host." ".$port;
      exec($cmd,$output,$result);
    }
     
  }else{
    /// vpath_ncatなし
    $result=1;
  }
  return $result;  
}
///
/// Windows マネージャ ncat
///
function winhostncat($host,$port){
   global $vpath_ncat; 
   /// WindowsとUNIXのncatコマンドは同じだが
   /// Windowsは先頭にcmd.exeが必要
   /// vmsetup/kanshiphp,ini で vpath_ncat="ncat.exeのディレクトリ" 設定
   //$vpath_ncat='c:/program files (x86)/nmap';
   $output=null;
   $result=null;
   //echo '<br>'.$port;
   $cmd='"'.$vpath_ncat.'/ncat.exe"'.' -zv -w 0.5 '.$host.' '.$port;
   //$cmd='"'.$vpath_ncat.'" -zv -w 0.5 '.$host.' '.$port;
   //$cmd='"c:/program files (x86)/nmap/ncat.exe" -zv -w 0.5 '.$host.' '.$port;
   //echo $cmd;
   //$cmd="ncat.exe -zv -w 0.5 ".$host." ".$port;  // ncat.exeが見つからない？　PATH登録済
   exec('cmd /s /c "'.$cmd.'"',$output,$result);
   //exec('cmd /s /c ""c:/program files (x86)/nmap/ncat.exe" -zv -w 0.5 192.168.1.22 443"',$output,$result);
   //echo 'cmd /s /c ""c:/program files (x86)/nmap/ncat.exe" -zv -w 0.5 '.$host.' '.$port.'"';
   //exec('cmd /s /c ""c:/program files (x86)/nmap/ncat.exe" -zv -w 0.5 '.$host.' '.$port.'"',$output,$result);
   echo '<br>return:'.strval($result).'<br>';
   return $result; 
}

function hostnmap($host,$portlist){
   $listArr=explode(';',$portlist);
   $string="";
   foreach ($listArr as $listRec){ 
     
     $output=null;
     $result=null;
     $matchsw=0;
     if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
       $cmd='nmap -sS '.$host.' | find "open"';
       exec('cmd /s /c "'.$cmd.'"',$output,$result);
     }else{
       $cmd='nmap -sS '.$host.' | grep "open"';
       exec($cmd,$output,$result);
     }
     foreach ($output as $openRec){
       $openRecArr=explode('/',$openRec);
       if ($listRec == $openRecArr[0]){
         //echo PHP_EOL.'match:'.$listRec;
         $matchsw=1;
         break;
       }
     }
     if ($matchsw==0) {
       $string=$string.$listRec.";";
     }
   }
   if ($string=="") {
     $string="allok";
   }else{
     $string=rtrim($string,";");
   }
   //echo date("H:i:s");
   return $string;
} 
/*
$rtn=hostncat('192.168.1.22','443');
if ($rtn==0) {
  echo 'open';
}else{
  echo 'close';
}
*/
/*
$portlist=array("123","80","443");
$ngports="";
foreach($portlist as $port){
  echo '<br>request:'.$port.'<br>';
  $rtn=winhostncat('192.168.1.22',$port);
  if($rtn==1){
    echo '<br>ngport:'.$port.'<br>';
    $ngports=$ngports.$port.";";
  }
}
$ngports=rtrim($ngports,';');
echo '<br>all ngport:'.$ngports.'<br>';
*/
?>
