<?php
require_once "mysqlkanshi.php";

$gname = array();
$hostno = array();
$segno = array();
$gseq = array();
$groupNum = $_GET['grpno'];
$layoutNick=$_GET['laynick'];
$user=$_GET['user'];
///
$user_sql="select authority,bgcolor from user where userid='".$user."'";
$userRows=getdata($user_sql);
if(empty($userRows)){
  $msg="#error#unkown#ユーザを見失いました";
  branch('logout.php',$msg);
}
$userArr=explode(',',$userRows[0]);
$authority=$userArr[0];
$bgColor=$userArr[1];
print '<html>';
print '<head>';
print '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
print '</head>';
print "<body class={$bgColor}>";
print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　グループレイアウト作成　その２　▽</h2>';
print '<h3>☆グループ名：日本語も可能です<br>';
print '☆配置順序：グループを上から縦に配置する、上からの連続番号です<br>';
print '☆１段のホスト数：グループ内のホストを横に展開する数で、標準では８です<br>';
print '☆段数：１グループのホスト数が多いとき下の段に配置します<br>';
print '　例：５段で１行のホスト数が８の場合、１グループホスト数は４０ホストになります</h3>';


$groupNumberCount=intval($groupNum);

print "<h4>レイアウト名称: {$layoutNick}　グループ数： {$groupNum}</h4>";
print '<form method=get action=layoutgroupdb.php>';
print '<table border=1>';
print '<tr><th>グループ名</th><th>配置順序</th><th>1段のホスト数</th><th>段数</th></tr>';
print "<input type=hidden name=layout value={$layoutNick}>";
for($cc=0;$cc<$groupNumberCount;$cc++){
  print '<tr>';
  $strCurrentCount=strval($cc+1);
  print "<td><input type=text name=gname[{$cc}] size=20 value='' required></td>";
  print "<td><input type=text name=gseq[{$cc}] size=10 value={$strCurrentCount} readonly></td>";
  print "<td><input type=text name=hostno[{$cc}] size=10 value='' required></td>";
  print "<td><input type=text name=segno[{$cc}] size=10 value='' required></td>";
  print '</tr>';
}
print '</table>';
print '<br>';
print "<input type=hidden name=user value={$user}>";
print '<input class=button type=submit value="実行">';
print '</form>';

print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body></html>';
?>

