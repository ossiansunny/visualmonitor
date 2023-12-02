<?php
require_once 'mysqlkanshi.php';
require_once 'serverimagedisplay.php';

if (!isset($_GET['param'])){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="ServerImage.php" method="get">';
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
  $user=$_GET['param'];
  echo '<html><head><meta>';
  echo '<link rel="stylesheet" href="kanshi1.css">';
  echo '</head><body>';
  echo '<h2><img src="header/php.jpg" width="30" height="30">▽　サーバー画像管理　▽</h2>';
  echo '<h3>☆画像名はpngファイルのみ許容します、詳細はマニュアル参照<br>';
  echo '☆削除と追加は一緒に出来ません</h3>';
  /// ホスト画像表示
  hostimagelist();
  echo '<br>';
  ///
  echo '<form  type="get" action="serverimageinsdeldb.php">';
  echo '<table border=1>';
  echo '<tr><th>削除</th><th width="150">画像名</th><th width="248">サーバー名</th></tr>';
  $rdsql="select * from serverimage order by image";
  $rows=getdata($rdsql);
  $sw=0;
  $sdatalist=array();
  foreach ($rows as $sdata){
    $sw=1;
    $sdatalist = explode(',',$sdata);
    echo '<tr>';
    echo "<td><input type='checkbox' name='fckbox[]' value={$sdatalist[0]}></td>";
    echo "<td><input type=text name=image size=20 value={$sdatalist[0]}></td>";
    echo "<td><input type=text name=name size=40 value={$sdatalist[1]}></td>";
    echo '</tr>';
  }
  if ($sw==0){
    echo '<tr>';
    echo '<td><input type=text name=dummy size=1 value=""></td>';
    echo '<td><input type=text name=image size=20 value="No data"></td>';
    echo '<td><input type=text name=name size=40 value="No data"></td>';
    echo '</tr>';
    echo '</table>';
  }else{
    echo '</table>';
    echo "<input type=hidden name=user value={$user}>";
    echo '&emsp;<input class=buttondel type="submit" name="del" value="削除実行">';
  }
  echo '<br>';
  echo '</form>';

  echo '<form type="get" action="serverimageinsdeldb.php">';
  echo '<table border=1>';
  echo '<tr><th>画像名</th><th>サーバー名</th></tr>';
  echo '<tr>';
  echo '<td><input type=text name=image size=20 value=""></td>';
  echo '<td><input type=text name=name size=40 value=""></td>';
  echo '</tr>';
  echo '</table>';
  echo "<input type=hidden name=user value={$user}>";
  echo '&emsp;<input class=button type="submit" name="ins" value="追加実行">';
  echo '</form>';
  echo '<br>';
  
  echo "&emsp;<a href='MonitorManager.php?param={$user}'><span class=button>監視モニターへ戻る</span></a>";
  echo '</body>';
  echo '</html>';
}
?>
