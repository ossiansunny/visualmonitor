<?php

require_once "mysqlkanshi.php";

function branchtarget($_page,$_param,$_target,$_jump){
  print '<html lang="ja">';
  print '<meta http-equiv="Content-Type" content="text/html;charset=utf-8">';
  print '<body onLoad="document.F.submit();">';
  print "<form name='F' action={$_page} target={$_target} method='get'>";
  print '<input type=hidden name=param value="'.$_param.'">';
  print '<input type="submit" name="next" value="Refresh '.$_page.'"';
  print '</form>';
  exit();
}
$pgm="pageshover.php";

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
echo '<html lang="ja">';
echo '<head>';
echo "<meta http-equiv='refresh' content='5'>";
echo '<link rel="stylesheet" href="css/kanshi1.css">';
echo '</head>';
echo '<body>';
echo "<h4>PageShover Refresh 5 sec</h4>";
///
/// 指定ページへジャンプする 
///
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
}
///
print '</body></html>';

?>

