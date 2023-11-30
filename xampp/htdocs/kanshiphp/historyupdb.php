<?php
require_once "mysqlkanshi.php";

function branch($_page,$_param){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo "<form name='F' action={$_page} method='get'>";
  echo "<input type=hidden name=param value={$_param}>";
  echo '<input type="submit" name="next" value="Waiting...">';
  echo '</form>';
}
echo '<html><head><meta>';
echo '<link rel="stylesheet" href="kanshi1.css">';
echo '</head><body>';
echo '</body></html>';
  $user=$_GET['user'];  
  $logtype=$_GET["logtype"];
  $logtime=$_GET["logtime"];
  $logsubj=$_GET["logsubj"];
  $logcont=$_GET["logcont"];
  
  $weblogsubj=htmlspecialchars($logsubj,ENT_QUOTES);
  $weblogcont=htmlspecialchars($logcont,ENT_QUOTES);
  
  $upsql="update historylog set type='".$logtype."', subject='".$weblogsubj."', contents='".$weblogcont."' where logtime='".$logtime."'";  
  putdata($upsql);

  $nextpage="HistoryPage.php";
  branch($nextpage,$user);
  exit();

?>
