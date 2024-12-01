<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

$pgm="SelectLayout.php";
$brcode="";
$brmsg="";
$user="";
///
if (!isset($_GET['param'])){
  paramGet($pgm);
  ///
}else{
  paramSet();
  ///
  print '<html><head><meta>';
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';
  print '</head><body>';
  if ($brcode=='error' or $brcode=='alert' or $brcode=='notic'){
    print "<h4 class={$brcode}>{$brmsg}</h4><hr>";
  }
  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　修正用レイアウト選択　▽</h2>';
  print '<h4>レイアウト名の「○」を選択して、下記の「選択」をクリックして下さい<br>レイアウト名「現用」は現在使われいるレイアウトです</h4>';
  ///
  $show_sql='show tables like "layout%"';
  $showRows=getdata($show_sql);
  print '<br><table border=0 class="tablelayout"><form type=GET action="layouthostedit.php">';
  print '<tr><th align=center>レイアウト名</th></tr>';
  ///
  foreach ($showRows as $showRowsRec){ /// item ('layout',) itemlayout[0]='layout'
    $showArr=explode(',',$showRowsRec);
    print '<tr>';
    if ($showArr[0]=='layout'){
      print "<td class=trylw><span class=trblk><input type='radio' name='terms' value={$showArr[0]}></span>現用</td>";
    }else{
      $layoutName=explode('_',$showArr[0]);
      print "<td class=trylw><span class=trblk><input type='radio' name='terms' value={$showArr[0]}></span>{$layoutName[1]}</td>";
    }
    print '</tr>';
  }
  print "<input type=hidden name=user value={$user}>";
  print '<tr><td><input type="submit" name="button" value="選択" class="button"></td></tr>';
  print '</form></table>';
  print "<br><a href='MonitorManager.php?param={$user}'><span class=buttonyell> 監視モニターへ戻る</span></a>";
  print '</body></html>';
}
?>

