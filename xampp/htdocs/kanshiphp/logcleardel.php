<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";
///
$vpatharr=array("vpath_apache","vpath_mrtgbase","vpath_kanshiphp");
$rtnv=pathget($vpatharr);
$apachelogdir=$rtnv[0]."\\logs";
$plotlogdir=$rtnv[1]."\\ubin\\gnuplot\\logs";
$kanshilogdir=$rtnv[2]."\\logs";

///
$now=new DateTime();
$ymd=$now->format("ymd");
///
print '<html><head><meta>';
print '<link rel="stylesheet" href="kanshi1.css">';
print '</head><body>';
$pgm = "logcleardel.php";
$user = $_GET['user'];
///
if (!isset($_GET['log'])){  
  $msg="#error#".$user."#チェックボックスにチェックをして下さい";
  $nextpage='LogClear.php';
  branch($nextpage,$msg);
}

$logtype = $_GET['log'];  /// 選択されたlog種類
///--- log削除処理 -----------
if($logtype=='Web'){
  $result=glob($apachelogdir.'\\*_*.log');
  foreach($result as $rec){
    $filename=basename($rec);
    if (false === strpos($filename,$ymd)){
      unlink($apachelogdir."\\".$filename);
    } // end of if    
  }  // end of for
}elseif($logtype=='監視'){
  $result=glob($kanshilogdir.'\\kanshi_*.log');
  foreach($result as $rec){        
    $filename=basename($rec);
    if(false === strpos($filename,$ymd)){
      unlink($kanshilogdir.'\\'.$filename);
    } // end of if
  } // end of for
}else{
  $result=glob($plotlogdir.'\\plot_*.log');
  foreach($result as $rec){        
    $filename=basename($rec);
    if (false === strpos($filename,$ymd)){
      unlink($plotlogdir.'\\'.$filename);
    }  // end of if
  }  // end for
}

$msg="#notic#".$user."#".$logtype."ログの削除が完了しました";
$nextpage="LogClear.php";
branch($nextpage,$msg);

echo '</body></html>';
?>
