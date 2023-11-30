<?php
require_once "mysqlkanshi.php";
echo '<html><head>';
echo '<title>プロットグラフ表示</title>';
echo '<link rel="stylesheet" href="kanshi1_py.css">';
echo '</head><body>';

function branch($_page,$_param){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo "<form name='F' action={$_page} method='get'>";
  echo "<input type=hidden name=param value={$_param}>";
  echo '<input type="submit" name="next" value="Waiting...">';
  echo '</form>';
}

$pgm = "viewgraphplot.php";

$uid=$_GET['user'];
if(!isset($_GET['fradio'])){
   $msg = "#error#".$uid."#ホストを選択して下さい";
   $nextpage = "GraphListPlotPage.php";
   writelogd($pgm,$msg);
   branch($nextpage,$msg);
   exit;
  
}

$server=$_SERVER['SERVER_ADDR'];
$fradio = explode(',',$_GET['fradio']);
$view=$fradio[5];
$host=$fradio[0];
if(isset($_GET['delete'])){
  $nextpage = "graphdelete.php";
  branch($nextpage,$host); 
  exit;
}
if(isset($_GET['create'])){
  $nextpage = "graphcreate.php";
  branch($nextpage,$_GET['fradio']);
  exit;
}
$ttl=$view.'('.$host.')';
echo "<h2>▽　{$ttl}　▽</h2>";
$pngcpu=$host . ".svg";
echo "<h4>CPU/Memory/Disk Load Average</h4>";
echo "<img alt='画像がありません' src='http://{$server}/plot/plotimage/{$pngcpu}'>";
echo '<br><br>';
echo "&ensp;<a href='MonitorManager.php?param={$uid}'><span class=button>監視モニターへ戻る</span></a>";
echo '</body></html>';
?>
