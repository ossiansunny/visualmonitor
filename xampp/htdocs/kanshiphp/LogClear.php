<html><head>
</head>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script type="text/javascript">
<!--
function viewPopup(data) {
  swal.fire({
    title: '',
    width:400,
    height:600,
    html: data,
    showConfirmButton: true,
    confirmButtonText: 'クローズ',
    background: '#dcdcdc',
  });
}
-->
</script>
</html>

<?php
require_once "BaseFunction.php";
require_once "varread.php";
///
function popupMsgSet($dataStr){
  $popArr=explode("//",$dataStr);
  $popupData="";
  $firstSw=0;
  foreach($popArr as $popItem){
    if($firstSw==0){
      $popItem="&lt;p&gt;".$popItem."&lt;/p&gt;&lt;p&gt;&lt;font size=2&gt;";
      $firstSw=1;
    }
    $popupData=$popupData.$popItem;    
  }
  $popupData=$popupData."&lt;/font&gt;&lt;/p&gt;";
  return $popupData;
}


$pgm="LogClear.php";
$user="";
$brcode="";
$brmsg="";
///
if (!isset($_GET['param'])){
  paramGet($pgm);  
}else{
  paramSet();
  $user_sql="select authority,bgcolor from user where userid='".$user."'";
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    branch('logout.php',$msg);
  }
  $userArr=explode(',',$userRows[0]);
  $authority=$userArr[0];
  $bgColor=$userArr[1];
  if($brcode=='error' or $brcode=='alert' or $brcode=='notic'){
     print "<h4 class={$brcode}>{$brmsg}</h4><hr>";
  }
}
///
$webLogDir='';
$kanshiLogDir='';
$vpathParam=array("vpath_weblog","vpath_kanshiphp");
$rtnPath=pathget($vpathParam);
if(count($rtnPath)==2){
  $webLogDir=$rtnPath[0];
  $kanshiLogDir=$rtnPath[1];  
}else{
  $msg="#error#".$user."#vpath_weblog,vpath_kanshiphpが不正です";
  $nextpage='LogClear.php';
  branch($nextpage,$msg);
}
$plotHomeDir="";
$vpathParam=array("vpath_gnuplot","vpath_plothome");
$rtnPath=pathget($vpathParam);
if(count($rtnPath)==2 and file_exists($rtnPath[0])){
  $plotHomeDir=$rtnPath[1];
}

///
$now=new DateTime();
$ymd=$now->format("ymd");
$webdisabled="";
$kanshidisabled="";
$plotdisabled="";

///
/// webログについてポップアップ
///
$popData="ログが削除出来ない場合は？//".
"削除対象のログファイルに対するapacheのアクセス権があるかチェック";

$popupRtnData=popupMsgSet($popData);

///
///--- log存在チェック -----------
///
/// Webログ
///
$webdisabled='disabled';
$fileRows=glob($webLogDir.'/error_*.log');
foreach($fileRows as $fileRowsRec){
  $filename=basename($fileRowsRec);
  if (false === strpos($filename,$ymd)){
    $webdisabled='';
  } /// end of if
}  /// end of for
///
/// 監視ログ
///
$kanshidisabled='disabled';
$fileRows=glob($kanshiLogDir.'/logs/kanshi_*.log');
foreach($fileRows as $fileRowsRec){
  $filename=basename($fileRowsRec);
  if(false === strpos($filename,$ymd)){
    $kanshidisabled='';
  } /// end of if
} /// end of for
///
/// PLOTログ
///
$plotdisabled='disabled';
if($plotHomeDir != ''){
  $fileRows=glob($plotHomeDir.'/logs/plot_*.log');
  foreach($fileRows as $fileRowsRec){
    $filename=basename($fileRowsRec);
    if (false === strpos($filename,$ymd)){
      $plotdisabled='';
    }  /// end of if
  }  /// end for
}
///
/// Eventログ
///
$eventdisabled='disabled';
  $cDate=date('ymd').'000000';
  $eventSql="select eventtime from eventlog where eventtime < {$cDate}";
  $eventRows=getdata($eventSql);
  if(count($eventRows)!=0){
    $eventdisabled='';
  }  /// end of if
 

///
/// ログ表示
///
print '<html lang="ja">';
print '<head>';
print '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
print "</head><body class={$bgColor}>";
print '<h2><img src="header/php.jpg" width="30" height="30">&ensp;&ensp;▽　ログ種類一覧　▽</h2>';
print '<h3>本日以外の選択したログを全て削除する</h3>';
print '<h4>☆削除したいログ種類を選択して、<a class=trred href="" onclick="viewPopup(\''.$popupRtnData.'\'); return false;">「削除実行」</a>をクリック<br>';
print '☆選択出来ない場合は、ログなし</h4>';
print '<form name="upform" method="get" action="logcleardel.php">';
print '<table border=1>';
print '<tr><th colspan=4>ログ種類</th></tr>';
print '<tr>';
print "&emsp;&emsp;&emsp;&emsp;<td><span class=trylw>監視ログ：</span><span class=trblk><input type='radio' name='log' value='監視' {$kanshidisabled}></span></td>";
print "&emsp;&emsp;&emsp;&emsp;<td><span class=trylw>Webエラーログ：</span><span class=trblk><input type='radio' name='log' value='Web' {$webdisabled}></span></td>";
print "<input type='hidden' name='user' value={$user}>";
print "&emsp;&emsp;&emsp;&emsp;<td><span class=trylw>プロットログ：</span><span class=trblk><input type='radio' name='log' value='プロット' {$plotdisabled}></span></td>";
print "&emsp;&emsp;&emsp;&emsp;<td><span class=trylw>イベントログ：</span><span class=trblk><input type='radio' name='log' value='イベント' {$eventdisabled}></span></td>";
print '</tr></table><br>';
print '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;<input class="buttondel" type="submit" name="show" value="削除実行"></span><br><br>';
print '</form>';  

print '&ensp;&emsp;&emsp;&emsp;&emsp;&emsp;<a href="MonitorManager.php"><span class=buttonyell>監視モニターへ戻る</span></a>';
print '</body></html>';
