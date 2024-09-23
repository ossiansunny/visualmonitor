<?php
require_once 'BaseFunction.php';
require_once 'mysqlkanshi.php';
require_once 'serverimagedisplay.php';

$pgm="ServerImage.php";
$user="";
$brcode="";
$brmsg="";

if (!isset($_GET['param'])){
  paramGet($pgm);
  ///
}else{
  paramSet();
  ///
  print '<html><head><meta>';
  print '<link rel="stylesheet" href="css/kanshi1.css">';
  print '</head><body>';
  if ($brcode=='error' or $brcode=='notic' or $brcode=='alert'){
    print "<h4 class={$brcode}>{$brmsg}</h4><hr>";
  }
  print '<h2><img src="header/php.jpg" width="30" height="30">▽　サーバー画像管理　▽</h2>';
  print '<h3>☆画像名はpngファイルのみ許容します、詳細はマニュアル参照<br>';
  print '☆画像名の重複は出来ません<br>';
  print '☆削除と追加は一緒に出来ません</h3>';
  /// ホスト画像表示
  hostimagelist();
  print '<br>';
  ///
  print '<form  type="get" action="serverimageinsdeldb.php">';
  print '<table border=1>';
  print '<tr><th>削除</th><th width="150">画像名</th><th width="248">サーバー名</th></tr>';
  $image_sql="select * from serverimage order by image";
  $imageRows=getdata($image_sql);
  if (empty($imageRows)){
    print "<h4 class='alert'>画像がありません、登録して下さい</h4><hr>";
  }
  $isSw=0;
  $imageArr=array();
  foreach ($imageRows as $imageRowsRec){
    $isSw=1;
    $imageArr = explode(',',$imageRowsRec);
    print '<tr>';
    print "<td><input type='checkbox' name='fckbox[]' value={$imageArr[0]}></td>";
    print "<td><input type=text name=image size=20 value={$imageArr[0]}></td>";
    print "<td><input type=text name=name size=40 value={$imageArr[1]}></td>";
    print '</tr>';
  }
  if ($isSw==0){
    print '<tr>';
    print '<td><input type=text name=dummy size=1 value=""></td>';
    print '<td><input type=text name=image size=20 value="No data"></td>';
    print '<td><input type=text name=name size=40 value="No data"></td>';
    print '</tr>';
    print '</table>';
  }else{
    print '</table>';
    print "<input type=hidden name=user value={$user}>";
    print '<br>&emsp;<input class=buttondel type="submit" name="del" value="削除実行">';
  }
  print '<br>';
  print '</form><hr>';

  print '<form type="get" action="serverimageinsdeldb.php">';
  print '<table border=1>';
  print '<tr><th>画像名</th><th>サーバー名</th></tr>';
  print '<tr>';
  print '<td><input type=text name=image size=20 value=""></td>';
  print '<td><input type=text name=name size=40 value=""></td>';
  print '</tr>';
  print '</table>';
  print "<input type=hidden name=user value={$user}>";
  print '<br>&emsp;<input class=button type="submit" name="ins" value="登録実行">';
  print '</form>';
  print '<br>';
  
  print "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
  print '</body>';
  print '</html>';
}
?>

