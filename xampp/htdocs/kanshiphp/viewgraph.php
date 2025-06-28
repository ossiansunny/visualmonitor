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
$vpathParam=array("vpath_mrtgbase","vpath_htdocs");
$rtnVal=pathget($vpathParam);
if(count($rtnVal)!=2){
  $nextpage="GraphListPage.php";
  $errMsg="#error#".$user."#Invalid vpath_mrtgbase or/and vpath_htdocs Base";
  branch($nextpage,$errMsg);
}
$mrtgBase=$rtnVal[0];
$htdocs=$rtnVal[1];
$mrtgDirArr=explode($htdocs,$mrtgBase);
$mrtgParent=$mrtgDirArr[1];
$title=$viewName.'('.$host.')';
$user_sql="select authority,bgcolor from user where userid='".$user."'";
$userRows=getdata($user_sql);
if(empty($userRows)){
  $msg="#error#unkown#ユーザを見失いました";
  branch('logout.php',$msg);
}
$userArr=explode(',',$userRows[0]);
$authority=$userArr[0];
$bgColor=$userArr[1];
print '<html><head>';
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
print "</head><body class={$bgColor}>";
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
  $fileName=$mrtgBase.'/mrtgimage/'.$fileCpu;
  if (file_exists($fileName)){
    $graphArr[0]=$fileCpu;
    $gSw=1;
  }
  print "<h3>CPU Load Average</h3>";
  print "<img alt='画像なし、未作成または作成中' src='http://{$server}{$mrtgParent}/mrtgimage/{$pngCpu}'>";
}else{
  print "<h3>CPU グラフ指定なし</h3>";
}
if($ramLim!=""){
  $pngRam=$host . ".ram-day.png?date=".$noCache;
  $fileRam=$host . ".ram-day.png";
  $fileName=$mrtgBase.'/mrtgimage/'.$fileRam;
  if (file_exists($fileName)){
    $graphArr[1]=$fileRam;
    $gSw=1;
  }
  print "<h3>Memory Usage</h3>";
  print "<img alt='画像なし、未作成または作成中' src='http://{$server}{$mrtgParent}/mrtgimage/{$pngRam}'>";
}else{
  print "<h3>Memory グラフ指定なし</h3>";
}
if($diskLim!=""){
  $pngDisk=$host . ".disk-day.png?date=".$noCache;
  $fileDisk=$host . ".disk-day.png";
  $fileName=$mrtgBase.'/mrtgimage/'.$fileDisk;
  if (file_exists($fileName)){
    $graphArr[2]=$fileDisk;
    $gSw=1; /// graphあり
  }
  print "<h3>Disk Usage</h3>";
  print "<img alt='画像なし、未作成、または作成中' src='http://{$server}{$mrtgParent}/mrtgimage/{$pngDisk}'>";
}else{
  print "<h3>Disk グラフ指定なし</h3>";
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

