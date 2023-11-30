<?php

require_once "mysqlkanshi.php";
echo '<html><head>';
echo '<title>リソースグラフ表示</title>';
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

$pgm = "viewgraph.php";
$uid=$_GET['user'];
if(!isset($_GET['fradio'])){
  $msg = "#error#".$uid."#ホストを選択して下さい";
  $nextpage = "GraphListPage.php";
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
  $msg = "#param#".$uid."#".$_GET['fradio'];
  branch($nextpage,$msg);
  exit;
}
if(isset($_GET['create'])){
  $nextpage = "graphcreate.php";
  $msg = "#param#".$uid."#".$_GET['fradio'];
  branch($nextpage,$msg);
  exit;
}
$ttl=$view.'('.$host.')';
echo "<h2>▽　{$ttl}　▽</h2>";
if($fradio[8]!=""){
  $pngcpu=$host . ".cpu-day.png";
  echo "<h4>CPU Load Average</h4>";
  echo "<img alt='画像なし、未作成' src='http://{$server}/mrtg/mrtgimage/{$pngcpu}'>";
  //echo 'src="http://'.$server.'/mrtg/mrtgimage/' . $pngcpu;
}else{
  echo "<h4>CPU グラフ指定なし</h4>";
}
if($fradio[9]!=""){
  $pngram=$host . ".ram-day.png";
  echo "<h4>Memory Usage</h4>";
  echo "<img alt='画像なし、未作成' src='http://{$server}/mrtg/mrtgimage/{$pngram}'>";
}else{
  echo "<h4>Memory グラフ指定なし</h4>";
}
if($fradio[10]!=""){
  $pngdisk=$host . ".disk-day.png";
  echo "<h4>Disk Usage</h4>";
  echo "<img alt='画像なし、未作成' src='http://{$server}/mrtg/mrtgimage/{$pngdisk}'>";
}else{
  echo "<h4>Disk グラフ指定なし</h4>";
}
echo '<br><br>';
echo "&ensp;<a href='MonitorManager.php?param={$uid}'><span class=buttonyell>監視モニターへ戻る</span></a>";
echo '</body></html>';
?>
