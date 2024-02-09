<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";

$pgm = "viewgraphplot.php";

$user=$_GET['user'];
if(!isset($_GET['fradio'])){
   $msg = "#error#".$user."#ホストを選択して下さい";
   $nextpage = "GraphListPlotPage.php";
   branch($nextpage,$msg);   
}

$server=$_SERVER['SERVER_ADDR'];
$fradio = explode(',',$_GET['fradio']);
$view=$fradio[5];
$mailopt=$fradio[6];
$host=$fradio[0];
$vpatharr=array("vpath_plothome");
$rtnv=pathget($vpatharr);
$plothome=$rtnv[0];
$ttl=$view.'('.$host.')';
print '<html><head>';
print '<link rel="stylesheet" href="kanshi1_py.css">';
print '</head><body>';
if ($mailopt=='1'){
  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　プロットグラフ表示/メール添付　▽</h2>';
}else{
  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　プロットグラフ表示　▽</h2>';
}
print "<h3>▽　{$ttl}　▽</h3>";
$gsw=0;
//$grapharray=array("");
if(!($fradio[8]=="" or $fradio[9]=="" or $fradio[10]=="")){
  $svgall=$host . ".svg";
  $filename=$plothome.'\\plotimage\\'.$svgall;
  if (file_exists($filename)){
    $graphstr=$svgall;
    $gsw=1;
  }
  print "<h4>CPU/Memory/Disk Load Average</h4>";
  print "<img alt='画像がありません' src='http://{$server}/plot/plotimage/{$svgall}'>";
}else{
  print "<h4>グラフ指定なし</h4>";
}
//$graphstr = join(',',$grapharray);
if ($mailopt=='1' and $gsw==1){
  print '<br><br>';
  print '<form action="graphmailsend.php" method="get">';
  print "<input type='hidden' name='host' value={$host}>";
  print "<input type='hidden' name='user' value={$user}>";
  print "<input type='hidden' name='graph' value={$graphstr}>";
  print '<input class="button" type="submit" name="attach" value="メール添付" />';
  print '</form>';
}
print '<br><br>';
print "&ensp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body></html>';
?>

