<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";

$pgm="ReadLogPage.php";
$user="";
$brcode="";
$brmsg="";
/// vpath_kanshiphpパス取得
$vpathParam=array("vpath_kanshiphp");
$vpathArr=pathget($vpathParam);
if(count($vpathArr)==1){
  $kanshiDir=$vpathArr[0];
}else{
  $msg = "vpath_kanshiphpがkanshiphp.iniにありません";
  writeloge($pgm,$msg);
  $msg="#error".$user."#監視パス取得失敗".$msg;
  $nextpage=$pgm;
  branch($nextpage,$msg);
} 
/// 
$timeStamp = date("ymdHis");
$ymd=substr($timeStamp,0,6);
$currLog='';
if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
  $currLog=$kanshiDir.'\\logs\\kanshi_'.$ymd.'.log';
}else{
  $currLog=$kanshiDir.'/logs/kanshi_'.$ymd.'.log';
}
/// ログ削除
if(isset($_GET['remove'])){
  $user = $_GET['user'];
  $resVal='';
  unlink($currLog);
  $resVal = touch($currLog);
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
/// ログ表示終了  
} elseif (isset ($_GET['end'])){
  $user = $_GET['user'];
  $nextpage="MonitorManager.php";
  branch($nextpage,$user);
///   
} elseif (!isset($_GET['param'])){
  paramGet($pgm);
///
} else {
  paramSet();
/// ログ表示
  $interval="60";
  $title1='<img src="header/php.jpg" width="30" height="30">';
  $title2='　▽　監視トレースログ　▽　';
  $title3=$interval . '　秒間隔更新';
  $title=$title1 . $title2 . $title3;
  print '<html>';
  print '<head>';
  print "<meta http-equiv='Refresh'  content={$interval}>";
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';  
  print '</head><body>';
  if ($brcode=='error'){
    print "<h4 class={$brcode}>{$brmsg}</h4><hr>";
  }
  print "<h2>{$title}</h2>";

  print "<table>";
  print "<h4>{$currLog}</h4>";  
  if(file_exists($currLog)){
    $fpLog = fopen($currLog,"r");
    $isSw=0;
    if($fpLog){
      while ($line = fgets($fpLog)) {
        $okData=htmlspecialchars($line);
        print "<tr><td>{$okData}</td></tr>";
        $isSw=1;
      }
      fclose($fpLog);
    }
    if($isSw==0){
      print "ログファイルにデータがありません<br>";
    }
  }else{
    print "ログファイルがありません<br>";
  }
  ///
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

