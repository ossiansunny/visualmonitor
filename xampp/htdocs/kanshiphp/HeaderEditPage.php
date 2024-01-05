<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
///
$pgm='HeaderEditPage.php';
$user="";
$brcode="";
$brmsg="";
///
if (!isset($_GET['param'])){
  paramGet($pgm);
}else{
  paramSet();
///  
  print '<html><head><meta>';
  print '<link rel="stylesheet" href="kanshi1_py.css">';
  print '</head><body>';
///
  if($brcode=="error" or $brcode=="notic" or $brcode=="alert"){
    print "<h4 class={$brcode}>{$brmsg}</h4><hr>";
  }
///
  $rdsql="select * from header";
  $rows=getdata($rdsql);
  $sdata=explode(',',$rows[0]);
  $title = $sdata[0]; //host
  $subtitle=$sdata[1];
  $image1=$sdata[2];
  $image2=$sdata[3];
  $image3=$sdata[4];
  $image4=$sdata[5];
  $lnkttl1=$sdata[7];
  $lnkttl2=$sdata[8];
  $lnkttl3=$sdata[9];
  $lnkttl4=$sdata[10];
  $lnkurl1=$sdata[12];
  $lnkurl2=$sdata[13];
  $lnkurl3=$sdata[14];
  $lnkurl4=$sdata[15];
  
  print '<h2><img src="header/php.jpg" width="30" height="30">&ensp;&ensp;▽　ヘッダー情報更新　▽</h2>';
  print '<h4>☆リンクURLは、http://又はhttps://から入力して下さい</h4>';
  print '<form name="headeredit" type="get" action="headerupdb.php">';
  print '<table border=1>';
  print '<tr><th colspan=2>タイトル</th><th colspan=2>サブタイトル</th></tr>';
  print '<tr>';
  print '<td colspan=2><input type=text name=title size=50 value='.$title.' ></td>';
  print '<td colspan=2><input type=text name=subtitle size=50 value='.$subtitle.'></td>';
  print '</tr>';
  print '<tr><th>画像名１</th><th>画像名２</th><th>画像名３</th><th>画像名４</th></tr>';
  print '<tr>';
  print '<td><input type=text name=image1 size=10 value='.$image1.'></td>';
  print '<td><input type=text name=image2 size=10 value='.$image2.'></td>';
  print '<td><input type=text name=image3 size=10 value='.$image3.'></td>';
  print '<td><input type=text name=image4 size=10 value='.$image4.'></td>';
  
  print '</tr>';
  print '<tr><th>リンク名１</th><th>リンク名２</th><th>リンク名３</th><th>リンク名４</th></tr>';
  print '<tr>';
  print '<td><input type=text name=lnkttl1 size=10 value='.$lnkttl1.'></td>';
  print '<td><input type=text name=lnkttl2 size=10 value='.$lnkttl2.'></td>';
  print '<td><input type=text name=lnkttl3 size=10 value='.$lnkttl3.'></td>';
  print '<td><input type=text name=lnkttl4 size=10 value='.$lnkttl4.'></td>';
  print '</tr>';
  print '<tr><th>リンクURL１</th><th>リンクURL２</th><th>リンクURL３</th><th>リンクURL４</th></tr>';
  print '<tr>';
  print '<td><input type=text name=lnkurl1 size=25 value='.$lnkurl1.'></td>';
  print '<td><input type=text name=lnkurl2 size=25 value='.$lnkurl2.'></td>';
  print '<td><input type=text name=lnkurl3 size=25 value='.$lnkurl3.'></td>';
  print '<td><input type=text name=lnkurl4 size=25 value='.$lnkurl4.'></td>';
  
  print '</tr>';
  print '</table>';
  print '<br>';
  print '<input type=hidden name=user value="'.$user.'">';
  print '&emsp;<input class=button type="submit" name="up" value="更新実行">';
  print '</form>';
  print '<br>';
  
  print '&emsp;<a href="MonitorManager.php?param='.$user.'"><span class=buttonyell>監視モニターへ戻る</span></a>';
  print '</body>';
  print '</html>';
}
?>

