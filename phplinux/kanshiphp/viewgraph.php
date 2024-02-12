<?php
require_once "BaseFunction.php";
require_once "varread.php";
require_once "mysqlkanshi.php";

$pgm = "viewgraph.php";
$user=$_GET['user'];
if(!isset($_GET['fradio'])){
  $msg = "#error#".$user."#ホストを選択して下さい";
  $nextpage = "GraphListPage.php";
  branch($nextpage,$msg);
  
}
$server=$_SERVER['SERVER_ADDR'];
$fradio = explode(',',$_GET['fradio']);
///fradio['host',,,,,'view','mailopt',,'cpu','ram','disk']
$view=$fradio[5];
$mailopt=$fradio[6]; /// '0':no send '1':send
$host=$fradio[0];
/// グラフ削除
if(isset($_GET['delete'])){
  $nextpage = "graphdelete.php";
  $msg = "#param#".$user."#".$_GET['fradio'];
  branch($nextpage,$msg);
  
}
/// グラフ作成
if(isset($_GET['create'])){
  $nextpage = "graphcreateGf.php";
  $msg = "#param#".$user."#".$_GET['fradio'];
  branch($nextpage,$msg);
  
}
/// グラフ表示
$vpatharr=array("vpath_mrtghome");
$rtnv=pathget($vpatharr);
$mrtghome=$rtnv[0];
$ttl=$view.'('.$host.')';
print '<html><head>';
print '<link rel="stylesheet" href="kanshi1_py.css">';
print '</head><body>';
if ($mailopt=='1'){
  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　リソースグラフ表示/メール添付　▽</h2>';
}else{
  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　リソースグラフ表示　▽</h2>';
}
print "<h3>▽　{$ttl}　▽</h3>";
$gsw=0;
$grapharray=array("","","");
if($fradio[8]!=""){
  $pngcpu=$host . ".cpu-day.png";
  $filename=$mrtghome.'/mrtgimage/'.$pngcpu;
  if (file_exists($filename)){
    $grapharray[0]=$pngcpu;
    $gsw=1;
  }
  print "<h4>CPU Load Average</h4>";
  print "<img alt='画像なし、未作成または作成中' src='http://{$server}/mrtg/mrtgimage/{$pngcpu}'>";
}else{
  print "<h4>CPU グラフ指定なし</h4>";
}
if($fradio[9]!=""){
  $pngram=$host . ".ram-day.png";
  $filename=$mrtghome.'/mrtgimage/'.$pngram;
  if (file_exists($filename)){
    $grapharray[1]=$pngram;
    $gsw=1;
  }
  print "<h4>Memory Usage</h4>";
  print "<img alt='画像なし、未作成または作成中' src='http://{$server}/mrtg/mrtgimage/{$pngram}'>";
}else{
  print "<h4>Memory グラフ指定なし</h4>";
}
if($fradio[10]!=""){
  $pngdisk=$host . ".disk-day.png";
  $filename=$mrtghome.'/mrtgimage/'.$pngdisk;
  if (file_exists($filename)){
    $grapharray[2]=$pngdisk;
    $gsw=1; /// graphあり
  }
  print "<h4>Disk Usage</h4>";
  print "<img alt='画像なし、未作成または作成中' src='http://{$server}/mrtg/mrtgimage/{$pngdisk}'>";
}else{
  print "<h4>Disk グラフ指定なし</h4>";
}
/// メール添付
$graphstr = join(',',$grapharray);
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

