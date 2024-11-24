<?php
require_once "varread.php";
require_once 'alarmwindow.php';
require_once 'hostncat.php';
require_once 'mysqlkanshi.php';

$interval=120;
$pgm='Discover.php';
print '<html lang="ja">';
print '<head>';
print "<meta http-equiv='refresh' content={$interval}>";
print '<link rel="stylesheet" href="css/kanshi1.css">';
print '</head>';
print '<body>';
print "<h4>メールサーバ・ログ監視 {$interval}秒 </h4>";
///
$osDirSep='';
if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
  $osDirSep="\\";
}else{
  $osDirSep="/";
}
///
$vpathParam=array("vpath_kanshiphp","vpath_plothome");
$vpath_kanshi="";
$vpath_plot="";
$rtnPath=pathget($vpathParam);
$now=new DateTime();
$ymd=$now->format("ymd");
if(count($rtnPath)==2){
  /// weblog
  $vpath_weblog="";
  $rtnVPathHtdocs=pathget(array("vpath_weblog"));
  if(count($rtnVPathHtdocs)==1){
    /// vpath_weblogがある場合、無ければデフォルトの場所のログ
    $vpath_weblog=$rtnVPathHtdocs[0];
    $webLogLists=glob($vpath_weblog.$osDirSep.'*_*.log');
    delstatus('Web Log Remain');
    foreach($webLogLists as $webLogFilePath){        
      $webLogFileName=basename($webLogFilePath);
      if (false === strpos($webLogFileName,$ymd)){
        setstatus('1','Web Log Remain'); 
        break;
      }
    }  
  }
  /// kanshi_log
  $vpath_kanshi=$rtnPath[0];
  $kanshiLogLists=glob($vpath_kanshi.$osDirSep.'logs'.$osDirSep.'kanshi_*.log');
  delstatus('Kanshi Log Remain');
  foreach($kanshiLogLists as $kanshiFileNamePath){        
    $kanshiFileName=basename($kanshiFileNamePath);
    if (false === strpos($kanshiFileName,$ymd)){
      setstatus('1','Kanshi Log Remain'); 
      break;
    }
  }
  /// plot_log 
  $vpath_plot=$rtnPath[1];
  $plotLogLists=glob($vpath_plot.$osDirSep.'logs'.$osDirSep.'plot_*.log');
  delstatus('Plot Log Remain');
  foreach($plotLogLists as $plotFileNamePath){        
    $plotFileName=basename($plotFileNamePath);
    if (false === strpos($plotFileName,$ymd)){
      setstatus('1','Plot Log Remain'); 
      break;
    }
  }
}else{
  $msg="Can't get Path";
  writeloge($pgm,$msg);
}
 
/// mailserver active check
$vpathParam=array("vpath_phpmailer");
$rtnPath=pathget($vpathParam);
if(count($rtnPath)==1){
  $mailSvr_sql='select server,port from mailserver';
  $mailRows=getdata($mailSvr_sql);
  $mailArr=explode(',',$mailRows[0]);
  $server=$mailArr[0];
  $port=$mailArr[1];
  if ($server != '127.0.0.1'){
    $rtnCde=hostncat($server,$port);
    if ($rtnCde==0){
      delstatus('Mail Server InActive');
      delstatus('Mail Server Active');
      setstatus('0','Mail Server Active');
      $mailSql="update mailserver set status='0'";
    }else{
      delstatus('Mail Server Active');
      delstatus('Mail Server InActive');
      setstatus('1','Mail Server InActive');
      $mailSql="update mailserver set status='1'";
    }
  }else{
    delstatus('Mail Server Active');
    delstatus('Mail Server InActive');
    setstatus('1','Mail Server InActive');
    $mailSql="update mailserver set status='1'";
  }
}else{
    delstatus('Mail Server Active');
    delstatus('Mail Server InActive');
    setstatus('1','Mail Server InActive');
    $mailSql="update mailserver set status='1'";
}
putdata($mailSql);

print '</body></html>';
?>  
