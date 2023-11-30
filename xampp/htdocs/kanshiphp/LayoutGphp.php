<?php
if (!isset($_GET['param'])){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="LayoutGphp.php" method="get">';
  echo '<input type="hidden" name="param" value="">';
  echo '<input type="submit" name="next" style="display:none;" />';
  echo '</form></body></html>';
  echo '<script type="text/javascript">';
  echo 'var keyvalue = sessionStorage.getItem("user");';
  echo 'if (!keyvalue) {';
  echo '  keyvalue = "unknown";';
  echo '}';
  echo 'document.forms["F"].elements["param"].value = keyvalue;';
  echo '</script>';
}else{  
  /// 引数情報の分解  param=#<code>#<user>#<message>　または param=<user>
  $user=$_GET['param'];
  echo '<html lang="ja">';
  echo '<head>';
  echo '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
  echo '<link rel="stylesheet" href="kanshi1.css">';
  echo '</head>';
  echo '<body>';
  echo '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　グループレイアウト作成　その１　▽</h2>';
  echo '<h4>☆作成するレイアウト名称（半角英数8文字以内）およびグループの数を入力して下さい</h4>';

  echo '<form name=myform action=layoutg.php method=get>'; 
  echo '<table border=1>';
  echo '<tr><th>レイアウト名称</th><th>グループ数</th></tr>';
  echo '<tr><td><input type=text name=laynick size=14 value="" placeholder="必須：英数"></td>';
  echo '<td><input type=text name=grpno size=6  value="" placeholder="必須：数字"></td></tr>';
  echo '</table>';
  echo "<input type=hidden name=user value={$user}>";
  echo '<br>';
  echo '&ensp;<input class=button type=submit name=exe value=実行>'; 

  echo '<br><br>';
  echo "&ensp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
  echo '</form>';
  echo '</body>';
  echo '</html>';
}
?>
