<?php
require_once "varread.php";
require_once 'alarmwindow.php';
require_once 'mysqlkanshi.php';
$intval=120;
$pgm='Discover.php';
print '<html lang="ja">';
print '<head>';
print "<meta http-equiv='refresh' content={$intval}>";
print '<link rel="stylesheet" href="kanshi1.css">';
print '</head>';
print '<body>';
print "<h4>Discover Refresh {$intval}sec</h4>";
$vpatharr=array("vpath_weblog","vpath_htdocs");
$vpath_weblog="";
$vpath_htdocs="";
$rtnv=pathget($vpatharr);
$now=new DateTime();
$ymd=$now->format("ymd");

if(count($rtnv)==2){
  $vpath_weblog=$rtnv[0];
  $vpath_htdocs=$rtnv[1];
  /// web_log
 
  $result=glob($vpath_weblog.'/*_*.log');
  delstatus('Web Log Remain');
  foreach($result as $rec){        
    $filename=basename($rec);
    //writeloge($pgm,'weblog:'.$rec);
    if (false === strpos($filename,$ymd)){
      setstatus('1','Web Log Remain'); 
      break;
    }
     
  }

  /// kanshi_log
  $vpath_htdocs=$rtnv[1];
  $result=glob($vpath_htdocs.'/kanshiphp/logs/kanshi_*.log');
  delstatus('Kanshi Log Remain');
  foreach($result as $rec){        
    $filename=basename($rec);
    //writeloge($pgm,'kanshi log:'.$rec);
    if (false === strpos($filename,$ymd)){
      setstatus('1','Kanshi Log Remain'); 
      break;
    }
  }
  /// plot_log 
  $result=glob($vpath_htdocs.'/plot/logs/plot_*.log');
  delstatus('Plot Log Remain');
  foreach($result as $rec){        
    $filename=basename($rec);
    //writeloge($pgm,'plot log:'.$rec);
    if (false === strpos($filename,$ymd)){
      setstatus('1','Plot Log Remain'); 
      break;
    }
  }
}else{
  writeloge($pgm,"Can not get Path");
} 
print '</body></html>';
  
