<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
/*
print '<html><head><meta>';
print '<link rel="stylesheet" href="css/kanshi1.css">';
print '</head><body>';
print '</body></html>';
*/
$pgm="historyupdb.php";
$user=$_GET['user'];  
$logType=$_GET["logtype"];
$logTime=$_GET["logtime"];
$logSubject=$_GET["logsubj"];
$logContents=$_GET["logcont"];
//print $pgm;
$webLogSubject=htmlspecialchars($logSubject,ENT_QUOTES);
$webLogContents=htmlspecialchars($logContents,ENT_QUOTES);
  
$hist_sql="update historylog set type='".$logType."', subject='".$webLogSubject."', contents='".$webLogContents."' where logtime='".$logTime."'";  
putdata($hist_sql);

$nextpage="HistoryPage.php";
$msg="#notic#".$user."#更新処理が完了しました";
branch($nextpage,$msg);

?>

