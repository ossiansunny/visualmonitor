<?php
require_once "BaseFunction.php";
///
$pgm="CoreRestart.php";
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
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
print '</head><body>';
print '<h2><img src="header/php.jpg" width="30" height="30">&ensp;&ensp;▽　再起動コアアプリ一覧　▽</h2>';
print '<h4>再起動したいアプリケーションを選択して、「再起動」を実行して下さい</h4>';

print '<table border=1>';
print '<tr><th>Core</th><th>SNMP</th><th>MRTG</th><th>Discover</th><th>Message</th></tr>';
print "<td width=150 align=center><a href='MonitorCoreAuto.php?param={$user}' target='core'>再起動</a></td>";
print "<td width=150 align=center><a href='SnmpAutoScan.php?param={$user}' target='snmp'>再起動</a></td>";
print "<td width=150 align=center><a href='MrtgAutoRun.php?param={$user}' target='mrtg'>再起動</a></td>";
print "<td width=150 align=center><a href='Discover.php?param={$user}' target='disc'>再起動</a></td>";
print "<td width=150 align=center><a href='Messages.php?param={$user}' target='msg'>再起動</a></td></tr>";
print '</table><br>';
print "&ensp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body></html>';
