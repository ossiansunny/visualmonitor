<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
require_once "mysqlkanshi.php";
require_once "varread.php";
///
$admin_sql='select monintval from admintb';
$adminRows=getdata($admin_sql);
$monitorInterval=$adminRows[0];
$pgm="MrtgAutoRun.php";
print '<html lang="ja">';
print '<head>';
print "<meta http-equiv='refresh' content={$monitorInterval}>";
print '<link rel="stylesheet" href="css/kanshi1.css">';
print '</head>';
print '<body>';
///
$core_sql='select mrtg from coretimetb';
$coreRows=getdata($core_sql);
$mrtgTimeStamp=$coreRows[0];
$currentTimeStamp=date('ymdHis');
$diffTime=intval($currentTimeStamp) - intval($mrtgTimeStamp);
if ($diffTime > intval($monitorInterval)*3){
  print "<h4>MRTG Refresh {$monitorInterval}sec</h4>";
  date_default_timezone_set('Asia/Tokyo');
  if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
    /// windows xampp
    $vpath_mrtgbase="";
    $vpath_plothome="";
    $vpath_base="";
    $vpathParam=array("vpath_mrtgbase","vpath_plothome","vpath_base","vpath_perlbin");
    $rtnPath=pathget($vpathParam);
    if(count($rtnPath)==4){
      $vpath_mrtgbase=$rtnPath[0];
      $vpath_plothome=$rtnPath[1];
      $vpath_base=$rtnPath[2]; 
      $vpath_perlbin=$rtnPath[3];
 
      $cmdMrtgRun=$vpath_perlbin."\\perl ".$vpath_mrtgbase."\\bin\\mrtg ".$vpath_mrtgbase."\\newmrtg.cfg";
      $out2 = shell_exec($cmdMrtgRun);
      writelogd($pgm,"call ".$cmdMrtgRun);
      $cmdPlotGraph="cscript ".$vpath_base.'\\ubin\\plotgraph.vbs '.$vpath_base; /// 
      $out3 = shell_exec($cmdPlotGraph);
      writelogd($pgm,"call ".$cmdPlotGraph);
    }else{
      $msg="Invalid path , Check kanshiphp.ini";
      writeloge($pgm,$msg);
      print "<h4>{$msg}</h4>";
    }
  }else{
    /// Linux
    $vpathParam=array("vpath_htdocs","vpath_kanshibin");
    $rtnPath=pathget($vpathParam);
    if(count($rtnPath)==2){
      $htdocs=$rtnPath[0];
      $kanshibin=$rtnPath[1];
      $cmd1=$kanshibin.'/mrtgrun.sh '.$htdocs;
      $out1 = shell_exec($cmd1);
      writelogd($pgm,'shell_exec '.$cmd1);
      $cmd2=$kanshibin.'/plotgraph.sh '.$htdocs;
      $out2 = shell_exec($cmd2);
      writelogd($pgm,'shell_exec '.$cmd2);
    }else{
      $msg="Invalid path , Check kanshiphp.ini";
      writeloge($pgm,$msg);
      print "<h4>{$msg}</h4>";
    }
  }
}else{
  print "<h4>MRTG Daemon Running</h4>";
}

print "</body></html>";
?>
