<?php
require_once 'mysqlkanshi.php';
$grpName = array();
$grpSeq = array();
$hostNum = array();
$segNum = array();
$user = $_GET['user'];
print '<html>';
print '<head>';
print '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
print '</head>';
$user_sql="select authority,bgcolor from user where userid='".$user."'";
$userRows=getdata($user_sql);
if(empty($userRows)){
  $msg="#error#unkown#ユーザを見失いました";
  branch('logout.php',$msg);
}
$userArr=explode(',',$userRows[0]);
$authority=$userArr[0];
$bgColor=$userArr[1];
print "<body class={$bgColor}>";
///
$grpName = $_GET['gname'];
$grpSeq = $_GET['gseq'];
$hostNum = $_GET['hostno'];
$segNum = $_GET['segno'];
$layoutNick=$_GET['layout'];
$grpLayout='glayout_'.$layoutNick;
$layout='layout_'.$layoutNick;
$drop_sql='drop table if exists '.$grpLayout;
putdata($drop_sql);
$create_sql='create table '.$grpLayout.' like glayout';
putdata($create_sql);

$grpCount=count($grpName);
for($cc=0;$cc<$grpCount;$cc++){
  $layout_sql='insert into '.$grpLayout.' values("'.$grpName[$cc].'",'.$grpSeq[$cc].','.$hostNum[$cc].','.$segNum[$cc].',"0")';
  putdata($layout_sql);
}
$drop_sql='drop table if exists '.$layout;
putdata($drop_sql);
$create_sql='create table '.$layout.' like layout';
putdata($create_sql);

print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　グループレイアウト作成　その３　▽</h2>';
print '<h4>レイアウト名称：'.$layoutNick.' のグループ情報を書き込み、ホスト情報をリセットしました</h4>';
print '<br>';
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";

print '</body></html>';
?>

