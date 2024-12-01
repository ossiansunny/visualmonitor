<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
$pgm = "eventmemodeldb.php";
$user = $_GET['user'];
if (!isset($_GET['fckbox'])){
  $msg='#error#'.$user.'#チェックボックスにチェックをして下さ';
  $nextpage='EventMemoPage.php';
  $branch($nextpage,$msg);
  
}
///--- eventmemo layout -----------------------------------
/// "eventtime" . "host" . "user" . "kanrino" . "memo";
///--------------------------------------------------------
$memoRows = $_GET['fckbox'];

if (is_array($fckbox)){ ///複数行
  foreach ($memoRows as $memoRowsRec){  
    $memoArr=explode(',',$memoRowsRec);
    $evTime = $memoArr[0];
    $host = $memoArr[1];
    $memo_sql='delete from eventmemo where eventtime="'.$evTime.'" and host="'.$host.'"';
    putdata($memo_sql);
      
  }  
}else{ /// 1行
  $memoArr=explode(',',$memoRowsRec);
  $evTime = $memoArr[0];
  $host = $memoArr[1];
  $memo_sql='delete from eventmemo where eventtime="'.$evTime.'" and host="'.$host.'"';
  putdata($memo_sql);     
}
$msg='#notic#'.$user.'#正常に削除されました';
$nextpage='EventMemoPage.php';
$branch($nextpage,$msg);   
?>
