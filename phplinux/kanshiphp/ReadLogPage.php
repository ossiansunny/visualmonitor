<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

$pgm="ReadLogPage.php";
$user="";
$brcode="";
$brmsg="";
if(isset($_GET['remove'])){
  $user = $_GET['user'];
  $timeStamp = date("ymdHis");
  $ymd=substr($timeStamp,0,6);
  $relVal='';
  if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
    unlink('logs\kanshi_'.$ymd.'.log');
    $resVal = touch('logs\kanshi_'.$ymd.'.log');
  }else{
    unlink('logs/kanshi_'.$ymd.'.log');
    $resVal = touch('logs/kanshi_'.$ymd.'.log');
  }
  if(!$resVal) {
    $msg = "監視ログ削除失敗";
    writeloge($pgm,$msg);
    $msg="#error".$user."#監視ログ削除失敗";
    $nextpage=$pgm;
    branch($nextpage,$msg);
  }else{
    $nextpage="MonitorManager.php";
    branch($nextpage,$user);
  }
  
} elseif (isset ($_GET['end'])){
  $user = $_GET['user'];
  $nextpage="MonitorManager.php";
  branch($nextpage,$user);
  
} elseif (!isset($_GET['param'])){
  paramGet($pgm);
  ///
} else {
  paramSet();
  ///
  $interval="60";
  $title1='<img src="header/php.jpg" width="30" height="30">';
  $title2='　▽　デバッグログ　▽　';
  $title3=$interval . '　秒間隔更新';
  $title=$title1 . $title2 . $title3;
  print '<html>';
  print '<head>';
  print "<meta http-equiv='Refresh'  content={$interval}>";
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';  
  print '</head><body>';
  if ($brcode=='error'){
    print "<h3 class={$brcode}>{$brmsg}</h4><hr>";
  }
  print "<h2>{$title}</h2>";

  print "<table>";
  $logData=readlog();
  $recCount = count($logData);
  for($i=0;$i<$recCount;$i++){
    $okData=htmlspecialchars($logData[$i]);
    print "<tr><td>{$okData}</td></tr>";
  }
  $user_sql='select authority from user where userid="'.$user.'"';
  $userRows=getdata($user_sql);
  if (empty($userRows)){
    $msg="#error#admin#ユーザがありません、再ログインして下さい";
    branch($pgm,$msg);
  }  
  $userArr=explode(',',$userRows[0]);
  $authority=$userArr[0];
  print "</table>";
  print '<form action="ReadLogPage.php" method="get">';
  print "<input type='hidden' name='user' value={$user} >";
  print '<input class=button type="submit" name="end" value="表示終了" />';
  if ($authority=='1'){
    print '&nbsp;&nbsp;<input class=buttondel type="submit" name="remove" value="全ログ削除" />';
  }
  print '</form>';
  print '</body>';
  print '</html>';
}
?>

