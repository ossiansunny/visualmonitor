<?php
require_once "BaseFunction.php";
///
$pgm="vmadmin.php";
$user="";
$brcode="";
$brmsg="";
///
if(!isset($_GET['param'])){
  paramGet($pgm);
///
}else{
  paramSet();
  print '<html><head><meta>';
  print '<link rel="stylesheet" href="css/css/kanshi1.css">';
  print '<style>';
  print 'body{font-family: "メイリオ", meiryo, sans-serif;background: linear-gradient(45deg, #FFCCFF, #14EFFF);background: linear-gradient(45deg, #FFCCFF, #14EFFF);font-size: small;}';
  print '</style>';
  print '</head><body>';
  print '<table><tr><td>';
  $logout = $user.": ログアウト";
  print "</td><td><b><a href='logout.php?param={$user}' target='sframe'><span class=buttonyell>{$logout}</span></a></b></td></tr></table>";
  print '</body></html>';
}

?>

