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
  $user_sql="select authority,bgcolor from user where userid='".$user."'";
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    branch('logout.php',$msg);
  }
  $userArr=explode(',',$userRows[0]);
  $authority=$userArr[0];
  $bgColor=$userArr[1];
}
print '<html lang="ja">';
print '<head>';
print '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
print "</head><body class={$bgColor}>";
print '<h2><img src="header/php.jpg" width="30" height="30">&ensp;&ensp;▽　再起動コアアプリ一覧　▽</h2>';
print '<h3>再起動するアプリケーションを選択して、「再起動」を実行して下さい</h3>';

print '<table border=1>';
print '<tr><th>Core Refresh</th><th>未使用</th><th>MRTG Refresh</th><th>メールサーバ・ログ管理</th><th>未使用</th></tr>';
print "<td width=150 align=center><a href='MonitorCoreAuto.php?param={$user}' target='core'>再起動</a></td>";
print "<td width=150 align=center><a href='PageShover.php?param={$user}' target='snmp'></a></td>";
print "<td width=150 align=center><a href='MrtgAutoRun.php?param={$user}' target='mrtg'>再起動</a></td>";
print "<td width=150 align=center><a href='Discover.php?param={$user}' target='disc'>再起動</a></td>";
print "<td width=150 align=center></td>";
print '</tr></table><br>';
print "&ensp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body></html>';
