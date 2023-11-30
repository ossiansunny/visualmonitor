<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
require_once "mysqlkanshi.php";
require_once "varread.php";
$sql='select monintval from admintb';
$rows=getdata($sql);
$intval=$rows[0];
$pgm="MrtgAutoRun.php";
echo '<html lang="ja">';
echo '<head>';
echo "<meta http-equiv='refresh' content={$intval}>";
echo '<link rel="stylesheet" href="kanshi1.css">';
echo '</head>';
echo '<body>';
echo "<h4>MRTG Refresh {$intval}sec</h4>";

date_default_timezone_set('Asia/Tokyo');
$dat=date('y/m/d H:i:s');
$vpath_mrtgbase="";
$vpatharr=array("vpath_mrtgbase");
$rtnv=pathget($vpatharr);
if(count($rtnv)==1){
  $vpath_mrtgbase=$rtnv[0];  
  $cmd=$vpath_mrtgbase.'\\ubin\\mrtgrun.vbs '.$vpath_mrtgbase;
  $out2 = shell_exec($cmd);
  writelogd($pgm,$cmd);
  $cmd=$vpath_mrtgbase.'\\ubin\\gnuplot\\vslogmake.vbs';
  $out3 = shell_exec($cmd);
  writelogd($pgm,$cmd);
}else{
  $msg="variable vpath_mrtgbase could not get path ";
  writelogd($pgm,$msg);
  echo "<h4>{$msg}</h4>";
}

echo '</body>';
echo '</html>';

?>
