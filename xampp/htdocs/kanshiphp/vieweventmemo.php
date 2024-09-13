<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

function arraycheck($_data){
  $rtnDataArr=array();
  if (is_array($_data)){
    $rtnDataArr=$_data;
  }else{
    $rtnDataArr[0]=$_data;
  }
  return $rtnDataArr;
}
$pgm = "vieweventmemo.php";
$userId=$_GET['user'];
$alerMsg="";
//$auth=$_GET['authcd'];
///-----------------------------------------------------------
///---- fckbox 選択された削除候補データ
///-----------------------------------------------------------
if (isset($_GET['delete'])){
  /// 削除処理
  if (isset($_GET['ckdata'])){
    $memoData=$_GET['ckdata'];
    $memoRows=arraycheck($memoData);
    foreach ($memoRows as $memoRowsRec){
      $memoArr=explode(',',$memoRowsRec);
      $memoStamp=$memoArr[0];
      $memoHost=$memoArr[1];
      $memo_sql='delete from eventmemo where host="'.$memoHost.'" and eventtime="'.$memoStamp.'"';
      putdata($memo_sql);      
    }
    $alerMsg='#notic#'.$userId.'#削除が完了';
    $nextpage='EventMemoPage.php';
    branch($nextpage,$alerMsg);    
  
  }else{
    $alerMsg='#error#'.$userId.'#チェックボックスでメモを選択して下さい';
    $nextpage='EventMemoPage.php';
    branch($nextpage,$alerMsg);
    
  }
}
?>

