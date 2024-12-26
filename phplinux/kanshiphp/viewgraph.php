<?php
require_once "BaseFunction.php";
require_once "varread.php";
require_once "mysqlkanshi.php";
///
$pgm = "viewgraph.php";
$get_user=$_GET['user'];
$user = $get_user;
$alerMsg="";
///
$osDirSep='';
if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
  $osDirSep='\\';
}else{
  $osDirSep='/';
}
///
if(!isset($_GET['fradio'])){
  $alerMsg = "#error#".$user."#ホストを選択して下さい";
  $nextpage = "GraphListPage.php";
  branch($nextpage,$alerMsg);  
}
$server=$_SERVER['SERVER_ADDR'];
$hostArr = explode(',',$_GET['fradio']);
///fradio['host',,,,,'view','mailopt',,'cpu','ram','disk']
$host=$hostArr[0];
$viewName=$hostArr[5];
$mailOpt=$hostArr[6]; /// '0':no send '1':send
$cpuLim=$hostArr[8];
$ramLim=$hostArr[9];
$diskLim=$hostArr[10];
/// グラフ削除
if(isset($_GET['delete'])){
  $nextpage = "graphdelete.php";
  $alerMsg = "#param#".$user."#".$_GET['fradio'];
  branch($nextpage,$alerMsg);  
}
/// グラフ作成
if(isset($_GET['create'])){
  $nextpage = "graphcreate.php";
  $alerMsg = "#param#".$user."#".$_GET['fradio'];
  branch($nextpage,$alerMsg);  
}
/// グラフ表示
$vpathParam=array("vpath_mrtghome");
$rtnVal=pathget($vpathParam);
$mrtgHome=$rtnVal[0];
$title=$viewName.'('.$host.')';
print '<html><head>';
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
print '</head><body>';
if ($mailOpt=='1'){
  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　リソースグラフ表示/メール添付　▽</h2>';
}else{
  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　リソースグラフ表示　▽</h2>';
}
print "<h3>▽　{$title}　▽</h3>";
$gSw=0;
$graphArr=array("","","");
$noCache=date("ymdHis");
if($cpuLim!=""){
  $pngCpu=$host . ".cpu-day.png?date=".$noCache;
  $fileCpu=$host . ".cpu-day.png";
  $fileName=$mrtgHome.$osDirSep.'mrtgimage'.$osDirSep.$fileCpu;
  if (file_exists($fileName)){
    $graphArr[0]=$fileCpu;
    $gSw=1;
  }
  print "<h4>CPU Load Average</h4>";
  print "<img alt='画像なし、未作成または作成中' src='http://{$server}/mrtg/mrtgimage/{$pngCpu}'>";
}else{
  print "<h4>CPU グラフ指定なし</h4>";
}
if($ramLim!=""){
  $pngRam=$host . ".ram-day.png?date=".$noCache;
  $fileRam=$host . ".ram-day.png";
  $fileName=$mrtgHome.$osDirSep.'mrtgimage'.$osDirSep.$fileRam;
  if (file_exists($fileName)){
    $graphArr[1]=$fileRam;
    $gSw=1;
  }
  print "<h4>Memory Usage</h4>";
  print "<img alt='画像なし、未作成または作成中' src='http://{$server}/mrtg/mrtgimage/{$pngRam}'>";
}else{
  print "<h4>Memory グラフ指定なし</h4>";
}
if($diskLim!=""){
  $pngDisk=$host . ".disk-day.png?date=".$noCache;
  $fileDisk=$host . ".disk-day.png";
  $fileName=$mrtgHome.$osDirSep.'mrtgimage'.$osDirSep.$fileDisk;
  if (file_exists($fileName)){
    $graphArr[2]=$fileDisk;
    $gSw=1; /// graphあり
  }
  print "<h4>Disk Usage</h4>";
  print "<img alt='画像なし、未作成、または作成中' src='http://{$server}/mrtg/mrtgimage/{$pngDisk}'>";
}else{
  print "<h4>Disk グラフ指定なし</h4>";
}
/// メール添付
$graphStr = join(',',$graphArr);
if ($mailOpt=='1' and $gSw==1){
  print '<br><br>';
  print '<form action="graphmailsend.php" method="get">';
  print "<input type='hidden' name='host' value={$host}>";
  print "<input type='hidden' name='user' value={$user}>";
  print "<input type='hidden' name='graph' value={$graphStr}>";
  print '<input class="button" type="submit" name="attach" value="メール添付" />';
  print '</form>';
}
print '<br><br>';
print "&ensp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body></html>';
?>

