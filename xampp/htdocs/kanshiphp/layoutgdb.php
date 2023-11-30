<?php
echo '<html><head>';
echo '<link rel="stylesheet" href="kanshi1.css">';
echo '</head><body>';

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

echo '<h2>▽　グループレイアウト作成　その３　▽</h2>';
?>
<h4>レイアウト名称： <?php echo $laynick; ?> のグループ情報を書き込み、ホスト情報をリセットしました</h4>
<?php
echo '<br>';
echo "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";

echo '</body></html>';
?>
