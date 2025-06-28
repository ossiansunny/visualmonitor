<?php
require_once "BaseFunction.php";
///
$pgm="ManualPageU.php";
$user="";
$brcode="";
$brmsg="";
$man='';
///
function imageSet(){
  global $brcode, $user, $brmsg;
  $inform=$_GET['param'];
  if (substr($inform,0,1)==","){
    $branchArr=explode(",",ltrim($inform,","),4);
    $brcode=$branchArr[0];
    $user=$branchArr[1];
    $brmsg=$branchArr[2];
  }else{
    $user=$inform;
  }
  if ($user=='unknown'){
    /// Lost Userを赤(2)で表示
    setstatus("2","Lost User");
  }else{
    delstatus("Lost User");
  }
}
///
if (!isset($_GET['param'])){
  paramGet($pgm);
}else{
  imageSet();
  $manval=$brmsg;
  if($manval==""){
    $man='manual/manualinit.pdf';
  }else{
    $man='manual/'.$manval.'.pdf';
  }
  ///
  $user_sql='select authority,bgcolor from user where userid="'.$user.'"';
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    branch('logout.php',$msg);
  }
  $userArr=explode(',',$userRows[0]);
  $auth=$userArr[0];
  $bgColor=$userArr[1];
  print '<html lang="ja">';
  print '<head>';
  print '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';
  print "</head><body class={$bgColor}>";
  print '<h2><img src="header/php.jpg" width="30" height="30">&ensp;&ensp;▽　マニュアル一覧　▽</h2>';
  print '<h3>参照したいマニュアルの〇を選択してください</h3>';
  print "<table><tr><td class={$bgColor}><div>";
  print '<table border=1>';
  print '<tr><th>選択</th><th>マニュアル名</th></tr>';
  print "<tr><td class=trblk><a href='ManualPage.php?param=,manual,{$user},NewEventLogGuide'><span class=trblk>〇</span></a></td><td class=trylw>イベントログ障害管理</td>";
  print "<tr><td class=trblk><a href='ManualPage.php?param=,manual,{$user},NewImageAudioGuide'><span class=trblk>〇</span></a></td><td class=trylw>画像警報音管理</td></tr>";
  print "<tr><td class=trblk><a href='ManualPage.php?param=,manual,{$user},NewLayoutGuide'><span class=trblk>〇</span></a></td><td class=trylw>レイアウト管理</td></tr>";
  print "<tr><td class=trblk><a href='ManualPage.php?param=,manual,{$user},NewServerImageGuide'><span class=trblk>〇</span></a></td><td class=trylw>サーバー画像管理</td></tr>";
  print "<tr><td class=trblk><a href='ManualPage.php?param=,manual,{$user},NewTraceLogGuide'><span class=trblk>〇</span></a></td><td class=trylw>トレースログ管理</td></tr>";
  print '</table><br>';
  print '<hr>';
  print "&ensp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
  print '</div></td>';
  /// iframe
  print '<td><div>';
  print "<iframe id='manual' width='710' height='800' src={$man}>Manual</iframe>";
  print '</div></td></tr></table>';
}
print '</body>';
print '</html>';
?>
