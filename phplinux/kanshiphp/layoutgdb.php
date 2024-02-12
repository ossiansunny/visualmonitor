<?php
print '<html><head>';
print '<link rel="stylesheet" href="kanshi1.css">';
print '</head><body>';

require_once 'mysqlkanshi.php';
$gname = array();
$gseq = array();
$hostno = array();
$segno = array();
$user = $_GET['user'];
$gname = $_GET['gname'];
$gseq = $_GET['gseq'];
$hostno = $_GET['hostno'];
$segno = $_GET['segno'];
$laynick=$_GET['layout'];
$glayout='glayout_'.$laynick;
$layout='layout_'.$laynick;
$gdrsql='drop table if exists '.$glayout;
putdata($gdrsql);
$gcpsql='create table '.$glayout.' like glayout';
putdata($gcpsql);

$gc=count($gname);
for($cc=0;$cc<$gc;$cc++){
  $insql='insert into '.$glayout.' values("'.$gname[$cc].'",'.$gseq[$cc].','.$hostno[$cc].','.$segno[$cc].',"0")';
  putdata($insql);
}
$sdrsql='drop table if exists '.$layout;
putdata($sdrsql);
$scpsql='create table '.$layout.' like layout';
putdata($scpsql);

print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　グループレイアウト作成　その３　▽</h2>';
?>
<h4>レイアウト名称： <?php print $laynick; ?> のグループ情報を書き込み、ホスト情報をリセットしました</h4>
<?php
print '<br>';
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";

print '</body></html>';
?>

