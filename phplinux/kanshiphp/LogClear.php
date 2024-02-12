<?php
require_once "BaseFunction.php";
///
$pgm="LogClear.php";
$user="";
$brcode="";
$brmsg="";
///
if (!isset($_GET['param'])){
  paramGet($pgm);
  
}else{
  paramSet();
  if($brcode=='error' or $brcode=='alert' or $brcode=='notic'){
    print '<h4 class="'.$brcode.'">"'.$brmsg.'"</h4><hr>';
    //print "<h4 class={$brcode}>{$brmsg}</h4><hr>";
  }
}
print '<html lang="ja">';
print '<head>';
print '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
print '<link rel="stylesheet" href="kanshi1_py.css">';
print '</head><body>';
print '<h2><img src="header/php.jpg" width="30" height="30">&ensp;&ensp;▽　ログ種類一覧　▽</h2>';
print '<h4>本日以外の選択したログを全て削除します<br>';
print '削除したいログ種類を選択して、「削除」を実行して下さい<br>';
print 'Webログ削除は無効です、個別に削除して下さい</h4>';

print '<form name="upform" method="get" action="logcleardel.php">';
print '<table border=1>';
print '<tr><th colspan=3>ログ種類</th></tr>';
print '<tr>';
print '&emsp;&emsp;&emsp;&emsp;<td><span class=trylw>監視ログ：</span><span class=trblk><input type="radio" name="log" value="監視" ></span></td>';
print '&emsp;&emsp;&emsp;&emsp;<td><span class=trylw>Webログ：</span><span class=trblk><input type="radio" name="log" value="Web" disabled></span></td>';
print "<input type='hidden' name='user' value={$user}>";
print '&emsp;&emsp;&emsp;&emsp;<td><span class=trylw>プロットログ：</span><span class=trblk><input type="radio" name="log" value="プロット" ></span></td>';
print '</tr></table><br>';
print '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;<input class="buttondel" type="submit" name="show" value="削除"></span><br><br>';
print '</form>';  

print '&ensp;&emsp;&emsp;&emsp;&emsp;&emsp;<a href="MonitorManager.php"><span class=buttonyell>監視モニターへ戻る</span></a>';
print '</body></html>';
