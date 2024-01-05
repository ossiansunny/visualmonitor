<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
$pgm='HostListPage.php';
$user='';
$brcode='';
$brmsg='';
if (!isset($_GET['param'])){
  paramGet($pgm);
}else{
  
  /// param引数情報の分解  
  $inform=$_GET['param'];
  paramSet();
  ///
  print '<html><head><meta>';
  print '<link rel="stylesheet" href="kanshi1_py.css">';
  print '</head><body>';
  if ($brcode=="alert" || $brcode=="error" || $brcode=="notic"){
    print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }

  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　ホスト一覧　▽</h2>';
  //
  ///----画面表示処理 --------------------
  //
  print '<h4>☆更新・削除するホストを１つ選択して「実行」をクリック</h4>';
  print '<form name="rform" method="get" action="hostupdel.php">';
  print '<table><tr><th width=20">選択</th><th width=100>ホスト</th><th>表示名</th><th>現行レイアウト</th></tr>';
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
    print "<tr><td><input type=radio name=fradio value={$strdata}></td>";
    print "<td class={$iro}>{$host}</td>";
    print "<td class={$iro}>{$viwname}</td>";
    print "<td class={$iro}>{$assign}</td></tr>";
  }
  print '<tr><td></td></tr>';
  print "<input type=hidden name=user value={$user}>";
  print '<tr><td>&emsp;<input class=button type="submit" name="" value="選択実行" ></td></tr>';
  print '</table></form>';
  print '<br>';
  print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
  print '</body></html>';
}
?>

