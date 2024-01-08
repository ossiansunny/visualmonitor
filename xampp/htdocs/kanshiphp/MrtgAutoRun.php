<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
require_once "mysqlkanshi.php";
require_once "varread.php";
///
$sql='select monintval from admintb';
$rows=getdata($sql);
$intval=$rows[0];
$pgm="MrtgAutoRun.php";
print '<html lang="ja">';
print '<head>';
print "<meta http-equiv='refresh' content={$intval}>";
print '<link rel="stylesheet" href="kanshi1.css">';
print '</head>';
print '<body>';
print "<h4>MRTG Refresh {$intval}sec</h4>";

date_default_timezone_set('Asia/Tokyo');
$dat=date('y/m/d H:i:s');
$vpath_mrtgbase="";
$vpath_plothome="";
$vpatharr=array("vpath_mrtgbase","vpath_plothome");
$rtnv=pathget($vpatharr);
if(count($rtnv)==2){
  $vpath_mrtgbase=$rtnv[0];
  $vpath_plothome=$rtnv[1];  
  $cmd=$vpath_mrtgbase.'\\ubin\\mrtgrun.vbs '.$vpath_mrtgbase;
  $out2 = shell_exec($cmd);
  writelogd($pgm,"call ".$cmd);
  //$cmd='e:\\VisualMonitor\\mrtg\\gnuplot\\vslogmake.vbs';
  $cmd=$vpath_mrtgbase.'\\ubin\\gnuplot\\vslogmake.vbs '.$vpath_mrtgbase.' '.$vpath_plothome;
  //print PHP_EOL."vslogmake:".$cmd.PHP_EOL;
  $out3 = shell_exec($cmd);
  writelogd($pgm,"call ".$cmd);
}else{
  $msg="パス変数 vpath_mrtgbase vpath_plothomeが得られない kanshiphp.iniをチェック";
  writeloge($pgm,$msg);
  print "<h4>{$msg}</h4>";
}

print '</body>';
print '</html>';

?>



