<?php
require_once "mysqlkanshi.php";

if (!isset($_GET['param'])){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="SelectLayout.php" method="get">';
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
  //$informuser=$_GET['param'];
  $inform=$_GET['param'];
  if (substr($inform,0,1)=="#"){
    $brarr=explode("#",ltrim($inform,"#"),4);
    $brcode=$brarr[0]; /// code
    $user=$brarr[1];   /// user
    $brmsg=$brarr[2];  /// message
  }else{
    $user=$inform;
  }

  echo '<html><head><meta>';
  echo '<link rel="stylesheet" href="kanshi1_py.css">';
  echo '</head><body>';
  if ($brcode=="alert" || $brcode=="error" || $brcode=="notic"){
    echo "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }
  echo '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　修正用レイアウト選択　▽</h2>';
  echo '<h4>☆レイアウト名の「○」を選択して、下記の「選択」をクリックして下さい<br>☆レイアウト名「現用」は現在使われいるレイアウトです</h4>';

  $showsql='show tables like "layout%"';
  $showdata=getdata($showsql);
  //#print(showdata)
  echo '<br><table border=0 class="tablelayout"><form type=GET action="layoutsupmap.php">';
  echo '<tr><th align=center>レイアウト名</th></tr>';

  foreach ($showdata as $item){ // item ('layout',) itemlayout[0]='layout'
    $itemarray=explode(',',$item);
    //#print(itemarray[0])
    echo '<tr>';
    if ($itemarray[0]=='layout'){
      echo "<td ><input type='radio' name='terms' value={$itemarray[0]}>現用</td>";
    }else{
      $layname=explode('_',$itemarray[0]);
      echo "<td><input type='radio' name='terms' value={$itemarray[0]}>{$layname[1]}</td>";
    }
    echo '</tr>';
  }
  echo "<input type=hidden name=user value={$user}>";
  echo '<tr><td><input type="submit" name="button" value="選択" class="button"></td></tr>';
  echo '</form></table>';
  echo "<br><a href='MonitorManager.php?param={$user}'><span class=buttonyell> 監視モニターへ戻る</span></a>";
  echo '</body></html>';
}
?>
