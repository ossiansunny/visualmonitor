<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";
///
$webLogDir='';
$plotHomeDir='';
$kanshiphpDir='';
$vpathParam=array("vpath_weblog","vpath_plothome","vpath_kanshiphp");
$rtnPath=pathget($vpathParam);
if(count($rtnPath)==3){
  $webLogDir=$rtnPath[0];
  $plotHomeDir=$rtnPath[1];
  $kanshiphpDir=$rtnPath[2];
}else{
  $msg="#error#".$user."#vpath_weblog,vpath_plothome,vpath_kanshiphpが不正です";
  $nextpage='LogClear.php';
  branch($nextpage,$msg);
}
///
$now=new DateTime();
$ymd=$now->format("ymd");
///
$pgm = "logcleardel.php";
$user = $_GET['user'];
///

if (!isset($_GET['log'])){  
  $msg="#error#".$user."#チェックボックスにチェックをして下さい";
  $nextpage='LogClear.php';
  branch($nextpage,$msg);
}

$logType = $_GET['log'];  /// 選択されたlog種類
///--- log削除処理 -----------
if($logType=='Web'){
  $fileRows=glob($webLogDir.'/error_*.log');
  foreach($fileRows as $fileRowsRec){
    $filename=basename($fileRowsRec);
    if (false === strpos($filename,$ymd)){
      $rtcd=unlink($fileRowsRec);
    }
  }
}elseif($logType=='監視'){
  $fileRows=glob($kanshiphpDir.'/logs/kanshi_*.log');
  foreach($fileRows as $fileRowsRec){        
    $filename=basename($fileRowsRec);
    if(false === strpos($filename,$ymd)){
      unlink($fileRowsRec);
    }
  }
}elseif($logType=='プロット'){
  $fileRows=glob($plotHomeDir.'/logs/plot_*.log');
  foreach($fileRows as $fileRowsRec){        
    $filename=basename($fileRowsRec);
    if (false === strpos($filename,$ymd)){
      unlink($fileRowsRec);
    }
  }
}elseif($logType=='イベント'){
  $cDate=date('ymd').'000000';
  $eventSql="delete from eventlog where eventtime < {$cDate}";
  putdata($eventSql);
}else{
  $msg="#error#".$user."#".$logType."ログのタイプが不正です、システムエラー";
  $nextpage="LogClear.php";
  branch($nextpage,$msg);
}
$msg="#notic#".$user."#".$logType."ログの削除が完了しました";
$nextpage="LogClear.php";
branch($nextpage,$msg);


?>
