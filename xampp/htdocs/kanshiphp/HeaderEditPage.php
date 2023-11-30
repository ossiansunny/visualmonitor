<?php
require_once "mysqlkanshi.php";
if (!isset($_GET['param'])){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="HeaderEditPage.php" method="get">';
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
  $rdsql="select * from header";
  $rows=getdata($rdsql);
  $sdata=explode(',',$rows[0]);
  $title = $sdata[0]; //host
  $subtitle=$sdata[1];
  $image1=$sdata[2];
  $image2=$sdata[3];
  $image3=$sdata[4];
  $image4=$sdata[5];
  $lnkttl1=$sdata[7];
  $lnkttl2=$sdata[8];
  $lnkttl3=$sdata[9];
  $lnkttl4=$sdata[10];
  $lnkurl1=$sdata[12];
  $lnkurl2=$sdata[13];
  $lnkurl3=$sdata[14];
  $lnkurl4=$sdata[15];
  
  echo '<h2><img src="header/php.jpg" width="30" height="30">&ensp;&ensp;▽　ヘッダー情報更新　▽</h2>';
  echo '<form name="headeredit" type="get" action="headerupdb.php">';
  echo '<table border=1>';
  echo '<tr><th colspan=2>タイトル</th><th colspan=2>サブタイトル</th></tr>';
  echo '<tr>';
  echo '<td colspan=2><input type=text name=title size=50 value='.$title.' ></td>';
  echo '<td colspan=2><input type=text name=subtitle size=50 value='.$subtitle.'></td>';
  echo '</tr>';
  echo '<tr><th>画像名１</th><th>画像名２</th><th>画像名３</th><th>画像名４</th></tr>';
  echo '<tr>';
  echo '<td><input type=text name=image1 size=10 value='.$image1.'></td>';
  echo '<td><input type=text name=image2 size=10 value='.$image2.'></td>';
  echo '<td><input type=text name=image3 size=10 value='.$image3.'></td>';
  echo '<td><input type=text name=image4 size=10 value='.$image4.'></td>';
  
  echo '</tr>';
  echo '<tr><th>リンク名１</th><th>リンク名２</th><th>リンク名３</th><th>リンク名４</th></tr>';
  echo '<tr>';
  echo '<td><input type=text name=lnkttl1 size=10 value='.$lnkttl1.'></td>';
  echo '<td><input type=text name=lnkttl2 size=10 value='.$lnkttl2.'></td>';
  echo '<td><input type=text name=lnkttl3 size=10 value='.$lnkttl3.'></td>';
  echo '<td><input type=text name=lnkttl4 size=10 value='.$lnkttl4.'></td>';
  
  echo '</tr>';
  echo '<tr><th>リンクURL１</th><th>リンクURL２</th><th>リンクURL３</th><th>リンクURL４</th></tr>';
  echo '<tr>';
  echo '<td><input type=text name=lnkurl1 size=25 value='.$lnkurl1.'></td>';
  echo '<td><input type=text name=lnkurl2 size=25 value='.$lnkurl2.'></td>';
  echo '<td><input type=text name=lnkurl3 size=25 value='.$lnkurl3.'></td>';
  echo '<td><input type=text name=lnkurl4 size=25 value='.$lnkurl4.'></td>';
  
  echo '</tr>';
  echo '</table>';
  echo '<br>';
  echo '<input type=hidden name=user value="'.$user.'">';
  echo '&emsp;<input class=button type="submit" name="up" value="更新実行">';
  echo '</form>';
  echo '<br>';
  
  echo '&emsp;<a href="MonitorManager.php?param='.$user.'"><span class=button>監視モニターへ戻る</span></a>';
  echo '</body>';
  echo '</html>';
}
?>
