<?php
require_once "varread.php";
require_once 'alarmwindow.php';
$intval=120;
$pgm='Discover.php';
print '<html lang="ja">';
print '<head>';
print "<meta http-equiv='refresh' content={$intval}>";
print '<link rel="stylesheet" href="kanshi1.css">';
print '</head>';
print '<body>';
print "<h4>Discover Refresh {$intval}sec</h4>";
$vpatharr=array("vpath_apache","vpath_kanshiphp","vpath_mrtgbase");
$vpath_apache="";
$vpath_kanshi="";
$vpath_plot="";
$rtnv=pathget($vpatharr);
$now=new DateTime();
$ymd=$now->format("ymd");

if(count($rtnv)==3){
  /// apache_log
  $vpath_apache=$rtnv[0];
  //$currpath = $vpath_apache."\\logs\\".$currelog; 
  $result=glob($vpath_apache.'\\logs\\*_*.log');
  delstatus('Apache Log Remain');
  foreach($result as $rec){        
    $filename=basename($rec);
    if (false === strpos($filename,$ymd)){
      setstatus('1','Apache Log Remain'); 
      break;
    }
     
  }
  /// kanshi_log
  $vpath_kanshi=$rtnv[1];
  $result=glob($vpath_kanshi.'\\logs\\kanshi_*.log');
  delstatus('Kanshi Log Remain');
  foreach($result as $rec){        
    $filename=basename($rec);
    if (false === strpos($filename,$ymd)){
      setstatus('1','Kanshi Log Remain'); 
      break;
    }
  }
  /// plot_log 
  $vpath_plot=$rtnv[2].'\\ubin\\gnuplot';
  $result=glob($vpath_plot.'\\logs\\plot_*.log');
  delstatus('Plot Log Remain');
  foreach($result as $rec){        
    $filename=basename($rec);
    if (false === strpos($filename,$ymd)){
      setstatus('1','Plot Log Remain'); 
      break;
    }
  }
}else{
  setstatus('2',"Can't get Path");
} 
print '</body></html>';
  