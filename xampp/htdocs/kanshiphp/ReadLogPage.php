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
$uid="";
if(isset($_GET['remove'])){
  $uid = $_GET['user'];
  unlink('kanshi.log');
  $res = touch('kanshi.log');
  if(!$res) {
    $msg = "error kanshi.log recreate failed ";
    writeloge($pgm,$msg);
  }
  $nextpage="MonitorManager.php";
  branch($nextpage,$uid);
  exit;
} elseif (isset ($_GET['end'])){
  $uid = $_GET['user'];
  $nextpage="MonitorManager.php";
  branch($nextpage,$uid);
  exit;
} elseif (!isset($_GET['param'])){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="ReadLogPage.php" method="get">';
  echo '<input type="hidden" name="param" value="">';
  echo '<input type="submit" name="next" style="display:none;" />';
  echo '</form></body></html>';
  echo '<script type="text/javascript">';
  echo 'var keyvalue = sessionStorage.getItem("user");';
  echo 'if (!keyvalue) {';
  echo '  keyvalue = "unknown";';
  echo '}';
  echo 'document.forms["F"].elements["param"].value = keyvalue;';
  echo '</script>';
} else {
  $uid=$_GET['param'];
  $interval="60";
  $ttl1='<img src="header/php.jpg" width="30" height="30">';
  $ttl2='　▽　デバッグログ　▽　';
  $ttl3=$interval . '　秒間隔更新';
  $ttl=$ttl1 . $ttl2 . $ttl3;
  echo '<html>';
  echo '<head>';
  echo "<meta http-equiv='Refresh'  content={$interval}>";
  echo '<style type="text/css">';
  echo 'body { font-family: "メイリオ", meiryo, sans-serif;';
  echo 'background: linear-gradient(45deg, #FFCCFF, #14EFFF); font-size: small; }';
  echo 'h2 { font-weight: bold; color: Gray; }';
  echo '.button { font-size: small; color: white; border-radius: 15%; background-color: green; }';
  echo '.buttonred { font-size: small; color: white; border-radius: 15%; background-color: red; }';
  echo '</style>';
  echo '</head>';
  echo '<body>';
  echo "<h2>{$ttl}</2>";

  $pgm="ReadLogPage.php";
  echo "<table>";
  $data=readlog();
  $tbcount = count($data);
  for($i=0;$i<$tbcount;$i++){
    $okchar=htmlspecialchars($data[$i]);
    echo "<tr><td>{$okchar}</td></tr>";
  }
  $usql='select authority from user where userid="'.$uid.'"';
  $rows=getdata($usql);
  $udata=explode(',',$rows[0]);
  $auth=$udata[0];
  echo "</table>";
  echo '<form action="ReadLogPage.php" method="get">';
  echo "<input type='hidden' name='user' value={$uid} >";
  echo '<input class=button type="submit" name="end" value="表示終了" />';
  if ($auth=='1'){
    echo '&nbsp;&nbsp;<input class=buttonred type="submit" name="remove" value="全ログ削除" />';
  }
  echo '</form>';
  echo '</body>';
  echo '</html>';
}
?>
