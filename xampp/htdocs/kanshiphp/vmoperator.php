<?php
require_once "BaseFunction.php";
///
$pgm="vmoperator.php";
$user="";
$brcode="";
$brmsg="";
///
if(!isset($_GET['param'])){
  paramGet($pgm);
///
}else{
  paramSet();
  ///
  print '<html><head><meta>';
  print '<link rel="stylesheet" href="css/kanshi1.css">';
  print '</head><body>';
  print '<table><tr><td>';
  $logout = $user.": ログアウト";
  print "</td><td><a href='logoutu.php?param={$user}' target='_top'><span class=buttonyell>{$logout}</span></a></td></tr></table>";
  print '</body></html>';
}
///
?>

