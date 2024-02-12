<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

$pgm="ReadLogPage.php";
$user="";
$brcode="";
$brmsg="";
if(isset($_GET['remove'])){
  $user = $_GET['user'];
  $tstamp = date("ymdHis");
  $ymd=substr($tstamp,0,6);
  unlink('logs/kanshi_'.$ymd.'.log');
  $res = touch('logs/kanshi_'.$ymd.'.log');
  if(!$res) {
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
  $ttl1='<img src="header/php.jpg" width="30" height="30">';
  $ttl2='　▽　デバッグログ　▽　';
  $ttl3=$interval . '　秒間隔更新';
  $ttl=$ttl1 . $ttl2 . $ttl3;
  print '<html>';
  print '<head>';
  print "<meta http-equiv='Refresh'  content={$interval}>";
  print '<link rel="stylesheet" href="kanshi1_py.css">';  
  print '</head><body>';
  if ($brcode=='error'){
    print '<h3 class="'.$brcode.'">"'.$brmsg.'"</h3><hr>';
    //print "<h3 class={$brcode}>{$brmsg}</h4><hr>";
  }
  print '<h2>'.$ttl.'</h2>';

  print "<table>";
  $data=readlog();
  $tbcount = count($data);
  for($i=0;$i<$tbcount;$i++){
    $okchar=htmlspecialchars($data[$i]);
    print "<tr><td>{$okchar}</td></tr>";
  }
  $usql='select authority from user where userid="'.$user.'"';
  $rows=getdata($usql);
  if (empty($rows)){
    $msg="#error#admin#ユーザがありません、再ログインして下さい";
    branch($pgm,$msg);
  }  
  $udata=explode(',',$rows[0]);
  $auth=$udata[0];
  print "</table>";
  print '<form action="ReadLogPage.php" method="get">';
  print "<input type='hidden' name='user' value={$user} >";
  print '<input class=button type="submit" name="end" value="表示終了" />';
  if ($auth=='1'){
    print '&nbsp;&nbsp;<input class=buttondel type="submit" name="remove" value="全ログ削除" />';
  }
  print '</form>';
  print '</body>';
  print '</html>';
}
?>

