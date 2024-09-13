<?php
require_once "varread.php";
require_once 'alarmwindow.php';
$interval=120;
$pgm='Discover.php';
print '<html lang="ja">';
print '<head>';
print "<meta http-equiv='refresh' content={$interval}>";
print '<link rel="stylesheet" href="css/kanshi1.css">';
print '</head>';
print '<body>';
print "<h4>Discover Refresh {$interval}sec</h4>";
///
$osDirsep='';
if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
  $osDirSep="\\";
}else{
  $osDirsep="/";
}
///
$vpathParam=array("vpath_kanshiphp","vpath_mrtgbase");
$vpath_kanshi="";
$vpath_plot="";
$rtnPath=pathget($vpathParam);
$now=new DateTime();
$ymd=$now->format("ymd");
if(count($rtnPath)==2){
  /// htdocs_log
  $vpath_htdocs="";
  $rtnVPathHtdocs=pathget(array("vpath_htdocs"));
  if(count($rtnVPathHtdocs)==1){
    /// vpath_htdocsがある場合、無ければデフォルトの場所のログ
    $vpath_htdocs=$rtnVPathHtdocs[0];
    $htdocsLogLists=glob($vpath_htdocs.$osDirsep.'logs'.$osDirSep.'*_*.log');
    delstatus('Web Log Remain');
    foreach($htdocsLogLists as $htdocsLogFilePath){        
      $htdocsFileName=basename($htdocsLogFilePath);
      if (false === strpos($htdocsFileName,$ymd)){
        setstatus('1','Web Log Remain'); 
        break;
      }
    }  
  }
  /// kanshi_log
  $vpath_kanshi=$rtnPath[0];
  $kanshiLogLists=glob($vpath_kanshi.$osDirSep.'logs'.$osDirSep.'kanshi_*.log');
  delstatus('Kanshi Log Remain');
  foreach($kanshilogLists as $kanshiFileNamePath){        
    $kanshiFileName=basename($kanshiFileNamePath);
    if (false === strpos($kanshiFileName,$ymd)){
      setstatus('1','Kanshi Log Remain'); 
      break;
    }
  }
  /// plot_log 
  $vpath_plot=$rtnPath[1].$osDirSep.'ubin'.$osDirsep.'gnuplot';
  $plotLogLists=glob($vpath_plot.$osDirsep.'logs'.$osDirSep.'plot_*.log');
  delstatus('Plot Log Remain');
  foreach($plotLogLists as $plotFileNamePath){        
    $plotFileName=basename($plotFileNamePath);
    if (false === strpos($plotFileName,$ymd)){
      setstatus('1','Plot Log Remain'); 
      break;
    }
  }
}else{
  setstatus('2',"Can't get Path");
} 
print '</body></html>';
  
