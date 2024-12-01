<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";

$pgm = "viewgraphplot.php";

$get_user=$_GET['user'];
$user = $get_user;
if(!isset($_GET['fradio'])){
   $msg = "#error#".$user."#ホストを選択して下さい";
   $nextpage = "GraphListPlotPage.php";
   branch($nextpage,$msg);   
}

$server=$_SERVER['SERVER_ADDR'];
$osDirSep='';
///
if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
  $osDirSep='\\';
}else{
  $osDirSep='/';
}
///
$hostArr = explode(',',$_GET['fradio']);
$host=$hostArr[0];
$viewName=$hostArr[5];
$mailOpt=$hostArr[6];
$cpuLim=$hostArr[8];
$ramLim=$hostArr[9];
$diskLim=$hostArr[10];
$vpathParam=array("vpath_plothome");
$rtnVal=pathget($vpathParam);
$plotHome=$rtnVal[0];
$title=$viewName.'('.$host.')';
print '<html><head>';
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
print '</head><body>';
if ($mailOpt=='1'){
  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　プロットグラフ表示/メール添付　▽</h2>';
}else{
  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　プロットグラフ表示　▽</h2>';
}
print "<h3>▽　{$title}　▽</h3>";
$existSw=0;
if(!($cpuLim=="" or $ramLim=="" or $diskLim=="")){
  $noCache=date("ymdHis");
  $svgName=$host . ".svg?date=".$noCache;
  $svgFile=$host . ".svg";
  $fileName=$plotHome.$osDirSep.'plotimage'.$osDirSep.$svgFile;
  if (file_exists($fileName)){
    $existSw=1;
  }
  print "<h4>CPU/Memory/Disk Maximum Load per Hour</h4>";
  print "<img alt='画像がありません' src='http://{$server}/plot/plotimage/{$svgName}'>";
}else{
  print "<h4>グラフ指定なし</h4>";
}
if ($mailOpt=='1' and $existSw==1){
  print '<br><br>';
  print '<form action="graphmailsend.php" method="get">';
  print "<input type='hidden' name='host' value={$host}>";
  print "<input type='hidden' name='user' value={$user}>";
  print "<input type='hidden' name='graph' value={$svgFile}>";
  print '<input class="button" type="submit" name="attach" value="メール添付" />';
  print '</form>';
}
print '<br><br>';
print "&ensp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body></html>';
?>

