<?php
echo "<html lang='ja'>";
echo "<head>";
echo "<meta http-equiv='content-type' content='text/html;charset=utf-8'>";
echo '<link rel="stylesheet" href="kanshi1_py.css">';
echo "</head>";
echo "<body>";
/// セッション情報のユーザーを取得
if(!isset($_GET['param'])){  
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="ManualPagephp.php" method="get">';
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
/// ユーザ取得後処理
  $user=$_GET['param'];
  echo "<h2>▽　マニュアル一覧　▽</h2>";
  echo "<h4>参照したいマニュアルを選択してください</h4>";
  echo "<table border=1>";
  echo '<tr><th>選択</th><th>マニュアル名</th></tr>';
  echo '<tr><td><a href="manual/serverimagemanage.pdf">〇</a></td><td>サーバー画像管理</td>';
  echo '<tr><td><a href="manual/resourcegraph.pdf">〇</a></td><td>リソースグラフ</td></tr>';
  echo '<tr><td><a href="manual/SNMPSpec.pdf">〇</a></td><td>SNMP仕様</td></tr>';
  echo '<tr><td><a href="manual/layout.pdf">〇</a></td><td>レイアウト</td></tr>';
  echo '<tr><td><a href="manual/監視ホスト追加修正.pdf">〇</a></td><td>監視ホスト追加・修正</td></tr>';
  echo '<tr><td><a href="manual/weberrorlog.pdf">〇</a></td><td>Webエラーログ管理</td></tr>';
  echo '<tr><td><a href="manual/initialize.pdf">〇</a></td><td>監視アプリ初期化</td></tr>';
  echo '<tr><td><a href="manual/VMMIBインストール設定.pdf">〇</a></td><td>VMMIBインストール設定</td></tr>';
  echo "</table><br>";
  echo "&ensp;<a href='MonitorManager.php?param={$user}'><span class=button>監視モニターへ戻る</span></a>";
}
echo "</body></html>";
?>
