<?php
require_once 'BaseFunction.php';
require_once 'mysqlkanshi.php';

function branchtarget($_page,$_param,$_target,$_jump){
  print '<html lang="ja">';
  print '<meta http-equiv="Content-Type" content="text/html;charset=utf-8">';
  //print '<meta http-equiv="refresh" content="1;'.$_jump.'">';
  print '<body onLoad="document.F.submit();">';
  print "<form name='F' action={$_page} target={$_target} method='get'>";
  print '<input type=hidden name=param value="'.$_param.'">';
  print '<input type="submit" name="next" value="Refresh '.$_page.'"';
  print '</form>';
  exit();
}
$pgm="PageShover.php";
///
if(!isset($_GET['param'])){
  print '<html>';
  print "<body bgcolor=khaki>";
  print '<h4><font color=gray>お待ち下さい....</font></h4>';
  print "</body></html>";
  paramGet($pgm);
}else{
  paramSet();

  $user_sql='select bgcolor from user where userid="'.$user.'"';
  $userRows=getdata($user_sql);
  $bgcolor=$userRows[0];
  echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
  echo '<html lang="ja">';
  echo '<head>';
  echo "<meta http-equiv='refresh' content='5'>";
  echo '<link rel="stylesheet" href="css/CoreMenu.css">';
  echo '</head>';
  echo "<body class={$bgcolor}>";
  echo '<div><table><tr><td>';
  echo "<h4><font color=white>PageShover Refresh 5 sec</font></h4>";
  echo '</td></tr></table></div>';
  ///
  /// 指定ページへジャンプする 
  ///
  //$stamp=date('ymdHis');
  $shove_sql="select * from shovetb";
  $shoveRows=getdata($shove_sql);
  foreach ($shoveRows as $shoveRowsRec){
    $shoveArr=explode(',',$shoveRowsRec);
    $tStamp=$shoveArr[0];
    $toCore=$shoveArr[1];
    $toFrame=$shoveArr[2];
    $toPage=$shoveArr[3];
    $del_sql='delete from shovetb where timestamp="'.$tStamp.'"';
    putdata($del_sql);
    branchtarget($toCore,'',$toFrame,'');
    //exit();
  }
  ///
  print '</body></html>';
}
?>

