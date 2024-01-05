<?php
require_once "BaseFunction.php";
///
$pgm="ManualPagephp.php";
$user="";
$brcode="";
$brmsg="";
///
if (!isset($_GET['param'])){
  paramGet($pgm);
}else{
  paramSet();
}
print '<html lang="ja">';
print '<head>';
print '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
print '<link rel="stylesheet" href="kanshi1_py.css">';
print '</head><body>';
print '<h2><img src="header/php.jpg" width="30" height="30">&ensp;&ensp;▽　マニュアル一覧　▽</h2>';
print '<h4>参照したいマニュアルを選択してください</h4>';
print '<table border=1>';
print '<tr><th>選択</th><th>マニュアル名</th></tr>';
print '<tr><td><a href="manual/serverimagemanage.pdf"><span class=redcolor>〇</span></a></td><td class=trblk>サーバー画像管理</td>';
print '<tr><td><a href="manual/resourcegraph.pdf">〇</a></td><td class=trblk>リソースグラフ</td></tr>';
print '<tr><td><a href="manual/SNMPSpec.pdf">〇</a></td><td class=trblk>SNMP仕様</td></tr>';
print '</table><br>';
print "&ensp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body>';
print '</html>';
?>
