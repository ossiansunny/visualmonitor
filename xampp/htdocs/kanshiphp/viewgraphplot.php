<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";

$pgm = "viewgraphplot.php";

$get_user=$_GET['user'];
$user = $get_user;
$user_sql="select authority,bgcolor from user where userid='".$user."'";
$userRows=getdata($user_sql);
if(empty($userRows)){
  $msg="#error#unkown#ユーザを見失いました";
  branch('logout.php',$msg);
}
$userArr=explode(',',$userRows[0]);
$authority=$userArr[0];
$bgColor=$userArr[1];

if(!isset($_GET['fradio'])){
   $msg = "#error#".$user."#ホストを選択して下さい";
   $nextpage = "GraphListPlotPage.php";
   branch($nextpage,$msg);   
}

$server=$_SERVER['SERVER_ADDR'];
///
$hostArr = explode(',',$_GET['fradio']);
$host=$hostArr[0];
$viewName=$hostArr[5];
$mailOpt=$hostArr[6];
$cpuLim=$hostArr[8];
$ramLim=$hostArr[9];
$diskLim=$hostArr[10];
$vpathParam=array("vpath_plothome","vpath_htdocs");
$rtnVal=pathget($vpathParam);
if(count($rtnVal)!=2){
   $nextpage="GraphListPlotPage.php";
   $errMsg="#error#".$user."#Invalid vpath_plothome or/and vpath_htdocs Base";
   branch($nextpage,$errMsg);
}
$plotHome=$rtnVal[0];
$htdocs=$rtnVal[1];
$plotDirArr=explode($htdocs,$plotHome);
$plotParent=$plotDirArr[1];
$title=$viewName.'('.$host.')';
print '<html><head>';
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
print "</head><body class={$bgColor}>";
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
  $fileName=$plotHome.'/plotimage/'.$svgFile;
  if (file_exists($fileName)){
    $existSw=1;
  }
  print "<h3>CPU/Memory/Disk Maximum Load per Hour</h3>";
  print '<div class=bgwhite>';
  print "<img alt='画像がありません' src='http://{$server}{$plotParent}/plotimage/{$svgName}'>";
  print '</div>';
}else{
  print "<h3>グラフ指定なし</h3>";
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

