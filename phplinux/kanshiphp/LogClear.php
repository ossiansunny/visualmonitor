<?php
require_once "BaseFunction.php";
require_once "varread.php";
///
$pgm="LogClear.php";
$user="";
$brcode="";
$brmsg="";
///
if (!isset($_GET['param'])){
  paramGet($pgm);  
}else{
  paramSet();
  if($brcode=='error' or $brcode=='alert' or $brcode=='notic'){
     print "<h4 class={$brcode}>{$brmsg}</h4><hr>";
  }
}
///
$webLogDir='';
$plotLogDir='';
$kanshiLogDir='';
$osDirSep='';
$vpathParam=array("vpath_weblog","vpath_plothome","vpath_kanshiphp");
$rtnPath=pathget($vpathParam);
if(count($rtnPath)==3){
  if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
    $webLogDir=$rtnPath[0];
    $plotLogDir=$rtnPath[1]."\\logs";
    $kanshiLogDir=$rtnPath[2]."\\logs";
    $osDirSep="\\";
  }else{
    $webLogDir=$rtnPath[0];
    $plotLogDir=$rtnPath[1]."/logs";
    $kanshiLogDir=$rtnPath[2]."/logs";
    $osDirSep="/";
  }
}else{
  $msg="#error#".$user."#vpath_weblog,vpath_base,vpath_kanshiphpが不正
です";
  $nextpage='LogClear.php';
  branch($nextpage,$msg);
}
///
$now=new DateTime();
$ymd=$now->format("ymd");
$webdisabled="";
$kanshidisabled="";
$plotdisabled="";

///
///--- log存在チェック -----------
/// Webログ
$webdisabled='disabled';
$fileRows=glob($webLogDir.$osDirSep.'*_*.log');
foreach($fileRows as $fileRowsRec){
  $filename=basename($fileRowsRec);
  if (false === strpos($filename,$ymd)){
  echo $filename.'<br>';
    $webdisabled='';
  } 
}  
/// 監視ログ
$kanshidisabled='disabled';
$fileRows=glob($kanshiLogDir.$osDirSep.'kanshi_*.log');
foreach($fileRows as $fileRowsRec){
  $filename=basename($fileRowsRec);
  if(false === strpos($filename,$ymd)){
    $kanshidisabled='';
  } 
} 
/// PLOTログ
$plotdisabled='disabled';
$fileRows=glob($plotLogDir.$osDirSep.'plot_*.log');
foreach($fileRows as $fileRowsRec){
  $filename=basename($fileRowsRec);
  if (false === strpos($filename,$ymd)){
    $plotdisabled='';
  }  
}  

print '<html lang="ja">';
print '<head>';
print '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
print '</head><body>';
print '<h2><img src="header/php.jpg" width="30" height="30">&ensp;&ensp;▽　ログ種類一覧　▽</h2>';
print '<h4>本日以外の選択したログを全て削除します<br>';
print '削除したいログ種類を選択して、「削除」を実行して下さい</h4>';

print '<form name="upform" method="get" action="logcleardel.php">';
print '<table border=1>';
print '<tr><th colspan=3>ログ種類</th></tr>';
print '<tr>';
print "&emsp;&emsp;&emsp;&emsp;<td><span class=trylw>監視ログ：</span><span class=trblk><input type='radio' name='log' value='監視' {$kanshidisabled}></span></td>";
print "&emsp;&emsp;&emsp;&emsp;<td><span class=trylw>Webログ：</span><span class=trblk><input type='radio' name='log' value='Web' {$webdisabled}></span></td>";
print "<input type='hidden' name='user' value={$user}>";
print "&emsp;&emsp;&emsp;&emsp;<td><span class=trylw>プロットログ：</span><span class=trblk><input type='radio' name='log' value='プロット' {$plotdisabled}></span></td>";
print '</tr></table><br>';
print '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;<input class="buttondel" type="submit" name="show" value="削除"></span><br><br>';
print '</form>';  

print '&ensp;&emsp;&emsp;&emsp;&emsp;&emsp;<a href="MonitorManager.php"><span class=buttonyell>監視モニターへ戻る</span></a>';
print '</body></html>';
