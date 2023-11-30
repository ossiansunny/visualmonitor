<?php
require_once "mysqlkanshi.php";

function branch($_page,$_param){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="'.$_page.'" method="get">';
  echo '<input type=hidden name=param value="'.$_param.'">';
  echo '<input type="submit" name="next" value="Waiting...">';
  echo '</form>';
}

echo '<html><head><meta>';
echo '<link rel="stylesheet" href="kanshi1.css">';
echo '</head><body>';
$pgm = "eventmemodeldb.php";

if (!isset($_GET['fckbox'])){
  echo '「チェックボックスにチェックをして下さい<br>';
  echo '<a href="EventMemoPage.py">イベントメモページへ戻る</a>';
  exit();
}
///--- eventmemo layout -----------------------------------
/// "eventtime" . "host" . "user" . "kanrino" . "memo";
///--------------------------------------------------------
$fckbox = $_GET['fckbox'];
$userid = $_GET['user'];
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
$nextpage="MonitorManager.php";
branch($nextpage,$userid);
?>
