<?php
require_once "BaseFunction.php";

$pgm = "logoutu.php";
$user="";
$brcode="";
$brmsg="";
//
if(!isset($_GET['param'])){
  paramGet($pgm);
}else{
  paramSet();
  print '<!DOCTYPE html>';
  print '<html>';
  print '<head>';
  print '<meta charset="utf-8">';
  print '<title>サンプル</title>';
  print '<link rel="stylesheet" href="login.css">';
  print '</head>';
  print '<body>';
  print '<div class="login">';
  print '<div class="login-triangle"></div>';
  print '<h2 class="login-header"><img src="header/php.jpg" width="70" height="70">&emsp;&emsp;ログアウト</h2>';
  print '<p><font color="white">ブラウザの閉じる「X」でクローズして下さい</font></p>';
  print '</div>';
  print '<div class="login">';
  print '</div>';
  print '</body>';
  print '</html>';
}
?>

