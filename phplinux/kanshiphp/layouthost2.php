﻿<?php
require_once 'BaseFunction.php';
require_once 'mysqlkanshi.php';

$pgm="layouthost2.php";
$user="";
$brcode="";
$brmsg="";
$grp = array();
$seg = array();
$hst = array();
$gh = array($grp,$seg,$hst);
///
if (isset($_GET['param'])){
  paramSet();
  /// $brmsg format: #error#<user>#<laynick>/<messages>
  $msgarr=explode('/',$brmsg); 
  $brmsg=$msgarr[1];
  $layoutNick=$msgarr[0];
}else{
  $user=$_GET['user'];
  $layoutNick=$_GET['laynick'];
}
$grpLayout='glayout_'.$layoutNick;
///
print '<html><head>';
print '<link rel="stylesheet" href="css/kanshi1.css">';
print '</head><body>';
///
if ($brcode=='error' or $brcode=='notic' or $brcode=='alert'){
  print "<h4 class={$brcode}>{$brmsg}</h4><hr>";
}
///
print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　ホストレイアウト作成　その２　▽</h2>';
print '<h4>ホスト配置情報入力グループ選択</h2>';
print '<h4>レイアウト名称： '.$layoutNick.'</h4>';
$layout_sql = 'select * from '.$grpLayout.' order by gsequence';
$layoutRows=getdata($layout_sql);
if ($layoutRows[0]=='error'){
  $msg="#error".$user."#グループレイアウトがありません";
  $nextpage="LayoutHost1.php";
  branch($nextpage,$msg);
} else {
  $groupCount=count($layoutRows);
  print '<h4>☆下記のグループ情報が入力されています<br>';
  print '☆グループ内のホスト配置入力するグループを１つ選択して「入力実行」を実行します、<br>';
  print '☆全てのグループのホスト配置欄が「入力済」の場合、情報修正になります<br></h4>';

  print '<form name=myform action=layouthost3.php method=get>';
  print '<table border=1>';
  print '<tr><th>選択</th><th>グループ名</th><th>配置順序</th><th>行ホスト数</th><th>段数</th><th>ホスト配置</th></tr>';
  print "<input type=hidden name=laynick value={$layoutNick}>";
  for($i=0;$i<$groupCount;$i++){
    $groupArr=explode(',',$layoutRows[$i]);
    print "<tr><td><input type=radio name=radio value={$layoutRows[$i]}></td>";
    print "<td><input type=text name=gname size=20 value={$groupArr[0]}></td>";
    print "<td><input type=text name=gseq size=10 value={$groupArr[1]}></td>";
    print "<td><input type=text name=ghostno size=10 value={$groupArr[2]}></td>";
    print "<td><input type=text name=gsegno size=10 value={$groupArr[3]}></td>";
    $finishFlag="";
    if($groupArr[4]=='0'){
      $finishFlag='未入力';
    }else{
      $finishFlag='入力済';
    }
    print "<td><input type=text name=ghaichi size=10 value={$finishFlag} readonly></td></tr>";
  }
  print '</table><br>';
  print "<input type=hidden name=user value={$user}>";
  print '<input class=button type=submit name=go value="実行">';

  print '</form>';
}

print '<br>';
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body></html>';
?>
