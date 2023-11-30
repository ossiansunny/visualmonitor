<?php
require_once "mysqlkanshi.php";
$user='';
$brcode='';
$brmsg='';
if (!isset($_GET['param'])){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="HostListPage.php" method="get">';
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
  $inform=$_GET['param'];
  if (substr($inform,0,1)=="#"){
    $brarr=explode("#",ltrim($inform,"#"),4);
    $brcode=$brarr[0]; /// code
    $user=$brarr[1];   /// user
    $brmsg=$brarr[2];  /// message
  }else{
    $user=$inform;
  }
  ///
  echo '<html><head><meta>';
  echo '<link rel="stylesheet" href="kanshi1.css">';
  echo '<style type="text/css">';
  echo '.redcolor { color: red; font-size: 10pt}';
  echo '.greencolor { color: green; font-size: 10pt}';
  echo '</style>';
  echo '</head><body>';
  if ($brcode=="alert" || $brcode=="error" || $brcode=="notic"){
    echo "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }

  echo '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　ホスト一覧　▽</h2>';
  //
  //----画面表示処理 --------------------
  //
  echo '<h4>☆更新・削除するホストを１つ選択して「実行」をクリック</h4>';
  echo '<form name="rform" method="get" action="hostupdel.php">';
  echo '<table><tr><th width=20">選択</th><th width=100>ホスト</th><th>表示名</th><th>現行レイアウト</th></tr>';
  $sql="select * from layout";
  $hrows=getdata($sql);
  $sql="select * from host order by groupname";
  $rows=getdata($sql);
  foreach ($rows as $strdata){  
    $sdata = explode(',',$strdata);
    $host = $sdata[0];
    $assign='未割り当て';
    $iro="redcolor";
    foreach ($hrows as $hrow){
      $hitem=explode(',',$hrow);
      if ($hitem[1]==$host){
        $assign='割り当て済';
        $iro="greencolor";
        break;
      }
    }
    $viwname = $sdata[5];
    echo "<tr><td><input type=radio name=fradio value={$strdata}></td>";
    echo "<td class={$iro}>{$host}</td>";
    echo "<td class={$iro}>{$viwname}</td>";
    echo "<td class={$iro}>{$assign}</td></tr>";
  }
  echo '<tr><td></td></tr>';
  echo "<input type=hidden name=user value={$user}>";
  echo '<tr><td>&emsp;<input class=button type="submit" name="" value="選択実行" ></td></tr>';
  echo '</table></form>';
  echo '<br>';
  echo "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
  echo '</body></html>';
}
?>
