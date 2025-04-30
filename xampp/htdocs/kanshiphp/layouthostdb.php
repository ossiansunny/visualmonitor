<?php
require_once 'mysqlkanshi.php';
$user = $_GET['user'];
$user_sql="select authority,bgcolor from user where userid='".$user."'";
$userRows=getdata($user_sql);
if(empty($userRows)){
  $msg="#error#unkown#ユーザを見失いました";
  branch('logout.php',$msg);
}
$userArr=explode(',',$userRows[0]);
$authority=$userArr[0];
$bgColor=$userArr[1];
print '<html><head>';
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
print "</head><body class={$bgColor}>";
///
$layoutNick=$_GET['laynick'];
$layout='layout_'.$layoutNick;

$grpHostArr = $_GET['host'];
$grpSeq=$_GET['gseq'];
$grpSeqInt=intval($grpSeq);
$count=count($grpHostArr[0]);
$grpSegHostId='g'.$grpSeq.'%';
$grpCount=count($grpHostArr);
$show_sql='show tables like "'.$layout.'"';
$rows=getdata($show_sql);
if (empty($rows)){
  $create_sql='create table '.$layout.' like layout';
  putdata($create_sql);
}
$layout_sql='delete from '.$layout.' where gshid like "'.$grpSegHostId.'"';
putdata($layout_sql);
for($cc=0;$cc<$grpCount;$cc++){
  $hc=count($grpHostArr[$cc]);
  for($dd=0;$dd<$hc;$dd++) {
    $grpSegHostId='g'.strval($grpSeq).'s'.strval($cc).'h'.strval($dd);
    $host=strval($grpHostArr[$cc][$dd]);
    $layout_sql='insert into '.$layout.' values("'.$grpSegHostId.'","'.$host.'")';
    putdata($layout_sql);
  }
}
$layout_sql='update g'.$layout.' set dataflag="1" where gsequence='.$grpSeqInt;
putdata($layout_sql);
$branchVal="_notic_{$user}_{$layoutNick}";
$backUrl="layouthost2.php?param={$branchVal}";
print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　ホストレイアウト作成　その４　▽</h2>';
print "<h3>レイアウト名称： {$layoutNick} のグループ内ホスト情報を書き込みました<br>";
print '☆別のホスト未入力グルーブを実行する場合は、「ホストレイアウト作成　その２へ 戻る」をクリック<br>';
print '☆終了する場合は、「監視モニターへ戻る」をクリックして下さい</h3>';
print "&emsp;<a href='{$backUrl}'><span class=button>ホストレイアウト作成　その２へ 戻る</span></a><br><br>";
print "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body></html>';

?>

