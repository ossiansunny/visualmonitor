<?php
require_once "varread.php";

Function mrtgplotck(){
  $pathParam=array("vpath_mrtg","vpath_gnuplot");
  $rtnPath=pathget($pathParam);
  if (count($rtnPath)==2){
    if(file_exists($rtnPath[0]) and file_exists($rtnPath[1])){
      return true;
    }
  }
  return false;
}

print '<html><head>';
print '<title>Visual Manager</title>';
print '</head>';

print '<frameset rows="140,*,30">';
  print '<frame src="HeaderPage.php" name="hframe" title="ヘッダフレーム">';
  print '<frameset cols="210,*">';
    print '<frame src="MenuPage.php" name="mframe" title="メニューフレーム">';
    print '<frame src="MonitorManager.php" name="sframe" scrolling="yes" title="ステータスフレーム">';
  print '</frameset>';
  print '<frameset cols="150,150,145,145,155,0">';
    print '<frame name="core" src="MonitorCoreAuto.php">';
    print '<frame name="snmp" src="PageShover.php">';  // 未使用
    //if(mrtgplotck()){
      print '<frame name="mrtg" src="MrtgAutoRun.php">';
    //}else{
    //  print '<frame name="mrtg" src="NoFunction.php">';
    //}
    print '<frame name="disc" src="Discover.php">';
    print '<frame name="push" src="PageShover.php">';  // 未使用
    print '</frameset>';
print '</frameset>';

print '<noframes>';
print '<body>';
print '<p>フレームの代替内容</p>';
print '</body>';
print '</noframes>';

print '</frameset>';

print '</html>';
?>
