<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";
///
$htdocsLogDir='';
$plotLogDir='';
$kanshiLogDir='';
$osDirSep='';
$vpathParam=array("vpath_htdocs","vpath_plothome","vpath_kanshiphp");
$rtnPath=pathget($vpathParam);
if(count($rtnPath)==3){
  if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
    //$htdocsLogDir=$rtnPath[0]."\\logs";
    $plotLogDir=$rtnPath[1]."\\logs";
    $kanshiLogDir=$rtnPath[2]."\\logs";
    $osDirSep="\\";
  }else{
    $htdocsLogDir=$rtnPath[0]."/httplogs";
    $plotLogDir=$rtnPath[1]."/logs";
    $kanshiLogDir=$rtnPath[2]."/logs";
    $osDirSep="/";
  }
}else{
  $msg="#error#".$user."#vpath_htdocs,vpath_base,vpath_kanshiphpが不正です";
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
if($logType=='Web' && $osDirSep=="/"){
  $fileRows=glob($htdocsLogDir.$osDirSep.'*_*.log');
  foreach($fileRows as $fileRowsRec){
    $filename=basename($fileRowsRec);
    //echo $filename.'<br>';
    if (false === strpos($filename,$ymd)){
      unlink($htdocsLogDir.$osDirSep.$filename);
    } // end of if    
  }  // end of for
}elseif($logType=='監視'){
  $fileRows=glob($kanshiLogDir.$osDirSep.'kanshi_*.log');
  foreach($fileRows as $fileRowsRec){        
    $filename=basename($fileRowsRec);
    if(false === strpos($filename,$ymd)){
      unlink($kanshiLogDir.$osDirSep.$filename);
    } // end of if
  } // end of for
}else{
  $fileRows=glob($plotLogDir.$osDirSep.'plot_*.log');
  foreach($fileRows as $fileRowsRec){        
    $filename=basename($fileRowsRec);
    if (false === strpos($filename,$ymd)){
      unlink($plotLogDir.$osDirSep.$filename);
    }  // end of if
  }  // end for
}

$msg="#notic#".$user."#".$logType."ログの削除が完了しました";
$nextpage="LogClear.php";
branch($nextpage,$msg);

//echo '</body></html>';
?>
