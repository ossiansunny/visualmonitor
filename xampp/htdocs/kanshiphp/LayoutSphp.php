<?php
require_once "BaseFunction.php";
///
$pgm='LayoutSphp.php';
$user="";
$brcode="";
$brmsg="";
///
if (!isset($_GET['param'])){
  paramGet($pgm);
  
}else{  
  /// 引数情報の分解  param=#<code>#<user>#<message>　または param=<user>
  paramSet();  
  ///
  print '<html lang="ja">';
  print '<head>';
  print '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
  print '<link rel="stylesheet" href="kanshi1.css">';
  print '</head><body>';
  ///
  if ($brcode=='error' or $brcode=='notic' or $brcode=='alert'){
    print "<h4 class={$brcode}>{$brmsg}</h4><hr>";
  }
  ///
  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　ホストレイアウト作成　その１　▽</h2>';
  print '<h4>☆グループにホストデータを配置します<br>';
  print '☆グループで作成したレイアウト略称を選択して下さい<br>';
  print '<br>';
  print '<form name=myform action=layouts.php method=get>'; 
  print '<table border=1>';
  print '<tr><th>レイアウト名称</th></tr>';
   
  $showsql='show tables like "glayout%"';
  $showdata=getdata($showsql);
  print '<tr><td><select name=laynick>';
  foreach ($showdata as $showrec){
    $layout=explode('_',$showrec);
    if(! is_null($layout[1])){
      print "<option value={$layout[1]}>{$layout[1]}</option>";
    }
  }
  print '</select></td></tr>';
   
  print '</table>';
  print "<input type=hidden name=user value={$user}>";
  print '<br>';
  print '&ensp;<input class=button type=submit name=exe value=実行>';
  print '</form>'; 
  print '<br><br>';
}
print "&ensp;<a href='MonitorManager.php}param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body>';
print '</html>';
?>

