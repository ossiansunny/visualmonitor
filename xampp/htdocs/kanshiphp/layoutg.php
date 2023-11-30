<?php
echo '<html>';
echo '<head>';
echo '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
echo '<link rel="stylesheet" href="kanshi1.css">';
echo '</head>';
echo '<body>';
echo '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　グループレイアウト作成　その２　▽</h2>';
echo '<h4>☆グループ名：日本語も可能です<br>';
echo '☆配置順序：グループを上から縦に配置する、上からの連続番号です<br>';
echo '☆１段のホスト数：グループ内のホストを横に展開する数で、標準では８です<br>';
echo '☆段数：１グループのホスト数が多いとき下の段に配置します<br>';
echo '　例：５段で１行のホスト数が８の場合、１グループホスト数は４０ホストになります</h4>';

$gname = array();
$hostno = array();
$segno = array();
$gseq = array();
$gn = $_GET['grpno'];
$ln=$_GET['laynick'];
$user=$_GET['user'];
$gnc=intval($gn);

echo "<h4>レイアウト名称: {$ln}　グループ数： {$gn}</h4>";
echo '<form method=get action=layoutgdb.php>';
echo '<table border=1>';
echo '<tr><th>グループ名</th><th>配置順序</th><th>1段のホスト数</th><th>段数</th></tr>';
echo "<input type=hidden name=layout value={$ln}>";
for($cc=0;$cc<$gnc;$cc++){
  echo '<tr>';
  $strcc=strval($cc+1);
  echo "<td><input type=text name=gname[{$cc}] size=20 value=''></td>";
  echo "<td><input type=text name=gseq[{$cc}] size=10 value={$strcc} readonly></td>";
  echo "<td><input type=text name=hostno[{$cc}] size=10 value=''></td>";
  echo "<td><input type=text name=segno[{$cc}] size=10 value=''></td>";
  echo '</tr>';
}
echo '</table>';
echo '<br>';
echo "<input type=hidden name=user value={$user}>";
echo '<input class=button type=submit value="実行">';
echo '</form>';

echo "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
echo '</body></html>';
?>
