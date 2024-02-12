<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

print '<html><head><meta>';
print '<link rel="stylesheet" href="kanshi1.css">';
print '</head><body>';
$pgm = "eventmemodeldb.php";
$userid = $_GET['user'];
if (!isset($_GET['fckbox'])){
  $msg='#error#'.$userid.'#チェックボックスにチェックをして下さ';
  $nextpage='EventMemoPage.php';
  $branch($nextpage,$msg);
  
}
///--- eventmemo layout -----------------------------------
/// "eventtime" . "host" . "user" . "kanrino" . "memo";
///--------------------------------------------------------
$fckbox = $_GET['fckbox'];

if (is_array($fckbox)){ //複数行
  foreach ($fckbox as $fckrec){  
    $sdata=explode(',',$fckrec);
    $evtime = $sdata[0];
    $host = $sdata[1];
    $delsql='delete from eventmemo where eventtime="'.$evtime.'" and host="'.$host.'"';
    putdata($delsql);
      
  }  
}else{ // 1行
  $sdata=explode(',',$fckbox);
  $evtime = sdata[0];
  $host = sdata[1];
  $delsql='delete from eventmemo where eventtime="'.$evtime.'" and host="'.$host.'"';
  putdata($delsql);     
}
$msg='#notic#'.$userid.'#正常に削除されました';
$nextpage='EventMemoPage.php';
$branch($nextpage,$msg);   
?>
