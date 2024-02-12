<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

function arraycheck($data){
  $dataarr=array();
  if (is_array($data)){
    $dataarr=$data;
  }else{
    $dataarr[0]=$data;
  }
  return $dataarr;
}
$pgm = "vieweventmemo.php";
$userid=$_GET['user'];
$auth=$_GET['authcd'];
///-----------------------------------------------------------
///---- fckbox 選択された削除候補データ
///-----------------------------------------------------------
if (isset($_GET['delete'])){
  /// 削除処理
  if (isset($_GET['ckdata'])){
    $ffckbox=$_GET['ckdata'];
    $fckbox=arraycheck($ffckbox);
    foreach ($fckbox as $fckrec){
      $sdata=explode(',',$fckrec);
      $delsql='delete from eventmemo where host="'.$sdata[1].'" and eventtime="'.$sdata[0].'"';
      putdata($delsql);      
    }
    $msg='#notic#'.$userid.'#削除が完了';
    $nextpage='EventMemoPage.php';
    branch($nextpage,$msg);
    
  
  }else{
    $msg='#error#'.$userid.'#チェックボックスでメモを選択して下さい';
    $nextpage='EventMemoPage.php';
    branch($nextpage,$msg);
    
  }
}
?>

