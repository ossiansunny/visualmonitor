<?php
require_once 'BaseFunction.php';
require_once 'mysqlkanshi.php';
error_reporting(E_ERROR | E_PARSE);
///
$pgm="ShowLayout.php";
$user="";
$brcode="";
$brmsg="";
///
if (!isset($_GET['param'])){
  paramGet($pgm);
  ///
}else{
  paramSet();
  ///
  print '<html><head>';
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';
  print '</head><body>';
  ///
  if ($brcode=="alert" or $brcode=="error" or $brcode=="notic"){
    print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }
  /// 
  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　レイアウト保存、読み込み　▽</h2>';
  print '<h4>☆レイアウト名を「○」で選択して、「実行ボタン」をクリックして下さい<br>';
  print '☆レイアウト名「現用」は現在使われいるレイアウトです、これを保存する場合は、<br>';
  print '「選択」の上、保存先に8文字以内の英数を入力し、「現用を保存先へ保存」を実行します<br>';
  print '☆保存レイアウトを現用で使用する場合は、「選択」の上、「レイアウトを現用へ読込」を実行します<br>';
  print '☆保存レイアウトを削除する場合は、「選択」の上、「レイアウトを削除」を実行します</h4>';
  ///
  $show_sql='show tables like "layout%"';
  $showRows=getdata($show_sql);
  print '<br>';
  print '<table border=0 class="tablelayout"><form type=GET action="svldlayout.php">';
  $cc=0;
  print '<tr><th align=center>レイアウト名</th><th align=center width=10px>保存・読込先</th><th colspan=2 align=center>実行ボタン</th></tr>';
  foreach($showRows as $showRowsRec){
    $showArr=explode('_',$showRowsRec);
    print "<input type=hidden name=user value={$user}>";
    print '<tr>';
    if($showRowsRec=='layout'){
      print "<td class=trylw><span class=trblk><input type='radio' name='terms' value={$showRowsRec}></span>現用</td>";
      print '<td class=trylw><span class=trblk><input type=text name=tosave value="" size=10 ></span></td>';
      print '<td><input class=button type="submit" name="save" value="現用を保存先へ保存"></td>';
    }else{
      print "<td class=trylw><span class=trblk><input type='radio' name='terms' value={$showRowsRec}></span>{$showArr[1]}</td>";
      print '<td>現用</td>';
      print '<td><input class=button type="submit" name="load" value="レイアウトを現用へ読込"></td>';
      print '<td><input class=buttondel type="submit" name="dele" value="レイアウトを削除"></td>';
    }
    print '</tr>';
    $cc++;
  }
  print '</form></table>';
}
print '<br>';
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell> 監視モニターへ戻る</span></a>";
print '</body></html>';
?>

