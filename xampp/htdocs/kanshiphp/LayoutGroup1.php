<?php
require_once "BaseFunction.php";
///
$pgm="LayoutGroup1.php";
$user="";
$brcode="";
$brmsg="";
///
if (!isset($_GET['param'])){
  paramGet($pgm);
}else{  
  /// 引数情報の分解  param=#<code>#<user>#<message>　または param=<user>
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
  ///
  print '<html lang="ja">';
  print '<head>';
  print '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';
  print '</head>';
  print "<body class={$bgColor}>";
  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　グループレイアウト作成　その１　▽</h2>';
  print '<h3>☆新たに作成するレイアウト名称（半角英数8文字以内）およびグループの数を入力し<span class=trblk>「実行」</span>をクリック<br>';
  print '<span class="trred">現存するレイアウト名称との重複は不可</span></h3>';
  print '<form name=myform action=layoutgroup2.php method=get>'; 
  print '<table border=1>';
  print '<tr><th>レイアウト名称</th><th>グループ数</th></tr>';
  print '<tr><td><input type=text name=laynick size=14 value="" placeholder="必須：英数" required></td>';
  print '<td><input type=text name=grpno size=6  value="" placeholder="必須：数字" required></td></tr>';
  print '</table>';
  print "<input type=hidden name=user value={$user}>";
  print '<br>';
  print '&ensp;<input class=button type=submit name=exe value=実行>'; 

  print '<br><br>';
  print "&ensp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
  print '</form>';
  print '</body>';
  print '</html>';
}
///
?>

