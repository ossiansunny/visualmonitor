<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";
///
$vpatharr=array("vpath_weblog","vpath_plothome","vpath_kanshiphp");
$rtnv=pathget($vpatharr);
$weblogdir=$rtnv[0]."/logs";
$plotlogdir=$rtnv[1]."/logs";
$kanshilogdir=$rtnv[2]."/logs";
//writeloge($pgm,'webdir:'.$weblogdir);

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
//writeloge($pgm,'logtype:'.$logtype);
///--- log削除処理 -----------
if($logtype=='Web'){ //できない
  $result=glob($weblogdir.'/*_*.log');
  //var_dump($result);
  foreach($result as $rec){
    $filename=basename($rec);
    //writeloge($pgm,'web:'.$filename);
    if (false === strpos($filename,$ymd)){
      unlink($weblogdir."/".$filename);
    } // end of if    
  }  // end of for
}elseif($logtype=='監視'){
  $result=glob($kanshilogdir.'/kanshi_*.log');
  foreach($result as $rec){        
    $filename=basename($rec);
    //writeloge($pgm,'kanshi:'.$filename);
    if(false === strpos($filename,$ymd)){
      unlink($kanshilogdir.'/'.$filename);
    } // end of if
  } // end of for
}else{
  $result=glob($plotlogdir.'/plot_*.log');
  foreach($result as $rec){        
    $filename=basename($rec);
    //writeloge($pgm,'plot:'.$filename);
    if (false === strpos($filename,$ymd)){
      unlink($plotlogdir.'/'.$filename);
    }  // end of if
  }  // end for
}

$msg="#notic#".$user."#".$logtype."ログの削除が完了しました";
$nextpage="LogClear.php";
branch($nextpage,$msg);

echo '</body></html>';
?>
