<?php
require_once 'BaseFunction.php';
require_once 'mysqlkanshi.php';

$pgm="BlankBoard.php";
$user="";
$brcode="";
$brmsg="";

///
if(!isset($_GET['param'])){
  paramGet($pgm);
}else{
  paramSet();

  $user_sql='select bgcolor from user where userid="'.$user.'"';
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    branch('logout.php',$msg);
  }
  $bgcolor=$userRows[0];
  print '<html lang="ja">';
  print '<head>';
  print '<meta http-equiv="refresh" content="10" charset="UTF-8">';
  print '<link rel="stylesheet" href="css/CoreMenu.css">';
  print '</head>';
  print "<body class={$bgcolor}>";
  print '<div ><table><tr><td >';
  print '<h5><font color=white>未使用</font></h5>';
  print '</td></tr></table></div>';
  print '</body>';
  print '</html>';
}
?>
