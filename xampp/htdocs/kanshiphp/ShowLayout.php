<?php

require_once 'mysqlkanshi.php';
error_reporting(E_ERROR | E_PARSE);

if (!isset($_GET['param'])){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="ShowLayout.php" method="get">';
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
  $inform=$_GET['param'];
  if (substr($inform,0,1)=="#"){
    $brarr=explode("#",ltrim($inform,"#"),4);
    $brcode=$brarr[0]; /// code
    $user=$brarr[1];   /// user
    $brmsg=$brarr[2];  /// message
  }else{
    $user=$inform;
  }
  echo '<html><head>';
  echo '<link rel="stylesheet" href="kanshi1_py.css">';
  echo '</head><body>';
  if ($brcode=="alert" || $brcode=="error" || $brcode=="notic"){
    echo "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }
  echo '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　レイアウト保存、読み込み　▽</h2>';
  echo '<h4>☆レイアウト名を「○」で選択して、「実行ボタン」をクリックして下さい<br>';
  echo '☆レイアウト名「現用」は現在使われいるレイアウトです、これを保存する場合は、<br>';
  echo '「選択」の上、保存先に8文字以内の英数を入力し、「現用を保存先へ保存」を実行します<br>';
  echo '☆保存レイアウトを現用で使用する場合は、「選択」の上、「レイアウトを現用へ読込」を実行します<br>';
  echo '☆保存レイアウトを削除する場合は、「選択」の上、「レイアウトを削除」を実行します</h4>';

  $showsql='show tables like "layout%"';
  $showdata=getdata($showsql);
  echo '<br>';
  echo '<table border=0 class="tablelayout"><form type=GET action="svldlayout.php">';
  $cc=0;
  echo '<tr><th align=center>レイアウト名</th><th align=center width=10px>保存・読込先</th><th colspan=2 align=center>実行ボタン</th></tr>';
  foreach($showdata as $item){
    $itemarr=explode('_',$item);
    echo "<input type=hidden name=user value={$user}>";
    echo '<tr>';
    if($item=='layout'){
      echo "<td ><input type='radio' name='terms' value={$item}>現用</td>";
    
      echo '<td ><input type=text name=tosave value="" size=10 ></td>';
      echo '<td><input class=button type="submit" name="save" value="現用を保存先へ保存"></td>';
    }else{
      echo "<td><input type='radio' name='terms' value={$item}>{$itemarr[1]}</td>";
    
      echo '<td>現用</td>';
      echo '<td><input class=button type="submit" name="load" value="レイアウトを現用へ読込"></td>';
      echo '<td><input class=buttondel type="submit" name="dele" value="レイアウトを削除"></td>';
    }
    echo '</tr>';
    $cc++;
  }
  echo '</form></table>';
}
echo '<br>';
echo "<a href='MonitorManager.php?param={$user}'><span class=buttonyell> 監視モニターへ戻る</span></a>";
echo '</body></html>';
?>
