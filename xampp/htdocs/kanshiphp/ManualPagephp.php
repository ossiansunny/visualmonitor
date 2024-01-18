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
print '<link rel="stylesheet" href="kanshi1.css">';
print '</head><body>';
print '<h2><img src="header/php.jpg" width="30" height="30">&ensp;&ensp;▽　マニュアル一覧　▽</h2>';
print '<h4>参照したいマニュアルを選択してください</h4>';
print '<table border=1>';
print '<tr><th>選択</th><th>マニュアル名</th></tr>';
print '<tr><td class=trblk><a href="manual/serverimagemanage.pdf"><span class=trblk>〇</span></a></td><td class=trylw>サーバー画像管理</td>';
print '<tr><td class=trblk><a href="manual/resourcegraph.pdf"><span class=trblk>〇</span></a></td><td class=trylw>リソースグラフ</td></tr>';
print '<tr><td class=trblk><a href="manual/SNMPSpec.pdf"><span class=trblk>〇</span></a></td><td class=trylw>SNMP仕様</td></tr>';
print '<tr><td class=trblk><a href="manual/layout.pdf"><span class=trblk>〇</span></a></td><td class=trylw>レイアウト</td></tr>';
print '<tr><td class=trblk><a href="manual/監視ホスト追加修正.pdf"><span class=trblk>〇</span></a></td><td class=trylw>監視ホスト追加・修正</td></tr>';
print '<tr><td class=trblk><a href="manual/weberrorlog.pdf"><span class=trblk>〇</span></a></td><td class=trylw>Webエラーログ管理</td></tr>';
print '<tr><td class=trblk><a href="manual/initialize.pdf"><span class=trblk>〇</span></a></td><td class=trylw>監視アプリ初期化</td></tr>';
print '<tr><td class=trblk><a href="manual/VMMIBインストール設定.pdf"><span class=trblk>〇</a></td><td class=trylw>VMMIBインストール設定</td></tr>';
print '<tr><td class=trblk><a href="manual/apltracelog.pdf"><span class=trblk>〇</span></a></td><td class=trylw>アプリトレースとログ管理</td></tr>';
print '<tr><td class=trblk><a href="manual/障害管理.pdf"><span class=trblk>〇</span></a></td><td class=trylw>障害管理</td></tr>';
print '</table><br>';
print "&ensp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body>';
print '</html>';
?>
