<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

$pgm="historyupdb.php";
$user=$_GET['user'];  
$logType=$_GET["logtype"];
$logTime=$_GET["logtime"];
$logSubject=$_GET["logsubj"];
$logContents=$_GET["logcont"];

$webLogSubject=htmlspecialchars($logSubject,ENT_QUOTES);
$webLogContents=htmlspecialchars($logContents,ENT_QUOTES);
  
$hist_sql="update historylog set type='".$logType."', subject='".$webLogSubject."', contents='".$webLogContents."' where logtime='".$logTime."'";  
putdata($hist_sql);

$nextpage="HistoryPage.php";
$msg="#notic#".$user."#更新処理が完了しました";
branch($nextpage,$msg);

?>

