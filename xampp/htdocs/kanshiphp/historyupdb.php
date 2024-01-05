<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

print '<html><head><meta>';
print '<link rel="stylesheet" href="kanshi1.css">';
print '</head><body>';
print '</body></html>';
$pgm="historyupdb.php";
$user=$_GET['user'];  
$logtype=$_GET["logtype"];
$logtime=$_GET["logtime"];
$logsubj=$_GET["logsubj"];
$logcont=$_GET["logcont"];
print $pgm;
$weblogsubj=htmlspecialchars($logsubj,ENT_QUOTES);
$weblogcont=htmlspecialchars($logcont,ENT_QUOTES);
  
$upsql="update historylog set type='".$logtype."', subject='".$weblogsubj."', contents='".$weblogcont."' where logtime='".$logtime."'";  
putdata($upsql);

$nextpage="HistoryPage.php";
$msg="#notic#".$user."#更新処理が完了しました";
branch($nextpage,$msg);

?>

