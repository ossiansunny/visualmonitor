<?php
require_once 'mysqlkanshi.php';
print '<html><head>';
print '<link rel="stylesheet" href="kanshi1.css">';
print '</head><body>';
///
//$layout=substr($_GET['layoutname'],1); // cut left g
$laynick=$_GET['laynick'];
$layout='layout_'.$laynick;
//$layarr=explode('_',$layout);
$user = $_GET['user'];
$gharr = $_GET['host'];
$gseq=$_GET['gseq'];
$gseqi=intval($gseq);
$count=count($gharr[0]);
$gshid='g'.$gseq.'%';
$gc=count($gharr);
$chksql='show tables like "'.$layout.'"';
$rows=getdata($chksql);
if (empty($rows)){
  $cresql='create table '.$layout.' like layout';
  putdata($cresql);
}
$delsql='delete from '.$layout.' where gshid like "'.$gshid.'"';
putdata($delsql);
for($cc=0;$cc<$gc;$cc++){
  $hc=count($gharr[$cc]);
  for($dd=0;$dd<$hc;$dd++) {
    $gshid='g'.strval($gseq).'s'.strval($cc).'h'.strval($dd);
    $host=strval($gharr[$cc][$dd]);
    $insql='insert into '.$layout.' values("'.$gshid.'","'.$host.'")';
    putdata($insql);
  }
}
$upsql='update g'.$layout.' set dataflag="1" where gsequence='.$gseqi;
putdata($upsql);
$backurl='layouts.php?laynick='.$laynick;
print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　ホストレイアウト作成　その４　▽</h2>';
print "<h4>レイアウト名称： {$laynick} のグループ内ホスト情報を書き込みました<br>";
print '☆別のホスト未入力グルーブを実行する場合は、「ホストレイアウト作成　その２へ 戻る」をクリック<br>';
print '☆終了する場合は、「監視モニターへ戻る」をクリックして下さい</h4>';
print "&emsp;<a href='{$backurl}'><span class=button>ホストレイアウト作成　その２へ 戻る</span></a><br><br>";
print "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body></html>';

?>

