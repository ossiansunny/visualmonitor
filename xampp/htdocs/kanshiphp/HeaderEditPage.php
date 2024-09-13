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
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';
  print '</head><body>';
///
  if($brcode=="error" or $brcode=="notic" or $brcode=="alert"){
    print "<h4 class={$brcode}>{$brmsg}</h4><hr>";
  }
///
  $head_sql="select * from header";
  $headRows=getdata($head_sql);
  $headArr=explode(',',$headRows[0]);
  $title = $headArr[0]; //host
  $subTitle=$headArr[1];
  $image1=$headArr[2];
  $image2=$headArr[3];
  $image3=$headArr[4];
  $image4=$headArr[5];
  $lnkTitle1=$headArr[7];
  $lnkTitle2=$headArr[8];
  $lnkTitle3=$headArr[9];
  $lnkTtile4=$headArr[10];
  $lnkUrl1=$headArr[12];
  $lnkUrl2=$headArr[13];
  $lnkUrl3=$headArr[14];
  $lnkUrl4=$headArr[15];
  
  print '<h2><img src="header/php.jpg" width="30" height="30">&ensp;&ensp;▽　ヘッダー情報更新　▽</h2>';
  print '<h4>☆リンクURLは、http://又はhttps://から入力して下さい</h4>';
  print '<form name="headeredit" type="get" action="headerupdb.php">';
  print '<table border=1>';
  print '<tr><th colspan=2>タイトル</th><th colspan=2>サブタイトル</th></tr>';
  print '<tr>';
  print '<td colspan=2><input type=text name=title size=50 value="'.$title.'" ></td>';
  print '<td colspan=2><input type=text name=subtitle size=50 value="'.$subTitle.'"></td>';
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
  print '<td><input type=text name=lnkttl1 size=10 value='.$lnkTitle1.'></td>';
  print '<td><input type=text name=lnkttl2 size=10 value='.$lnkTitle2.'></td>';
  print '<td><input type=text name=lnkttl3 size=10 value='.$lnkTille3.'></td>';
  print '<td><input type=text name=lnkttl4 size=10 value='.$lnkTitle4.'></td>';
  print '</tr>';
  print '<tr><th>リンクURL１</th><th>リンクURL２</th><th>リンクURL３</th><th>リンクURL４</th></tr>';
  print '<tr>';
  print '<td><input type=text name=lnkurl1 size=25 value='.$lnkUrl1.'></td>';
  print '<td><input type=text name=lnkurl2 size=25 value='.$lnkUrl2.'></td>';
  print '<td><input type=text name=lnkurl3 size=25 value='.$lnkUrl3.'></td>';
  print '<td><input type=text name=lnkurl4 size=25 value='.$lnkUrl4.'></td>';
  
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

