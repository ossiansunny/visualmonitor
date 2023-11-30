<?php
require_once 'mysqlkanshi.php';

echo '<html>';
echo '<head>';
echo '<link rel="stylesheet" href="kanshi1.css">';
echo '</head><body>';
echo '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　ホストレイアウト作成　その２　▽</h2>';

$grp = array();
$seg = array();
$hst = array();
$gh = array($grp,$seg,$hst);
$user=$_GET['user'];
$layout='glayout_'.$_GET['laynick'];
echo '<h4>ホスト配置情報入力グループ選択</h2>';
echo '<h4>レイアウト名称： '.$_GET['laynick'].'</h4>';
$selsql = 'select * from '.$layout.' order by gsequence';
//echo $selsql.'<br>';
$glayout=getdata($selsql);
if ($glayout[0]=='error'){
  echo "<h4><font color=red>グループレイアウトがありません<br>
「グループレイアウト作成」を先に実行して下さい</font></h4><br>";
} else {
  $gc=count($glayout);
  echo '<h4>☆下記のグループ情報が入力されています<br>';
  echo '☆グループ内のホスト配置入力するグループを１つ選択して「入力実行」を実行します、<br>';
  echo '☆全てのグループのホスト配置欄が「入力済」の場合、情報修正になります<br></h4>';

  echo '<form name=myform action=layoutstp.php method=get>';
  echo '<table border=1>';
  echo '<tr><th>選択</th><th>グループ名</th><th>配置順序</th><th>行ホスト数</th><th>段数</th><th>ホスト配置</th></tr>';
  echo "<input type=hidden name=layout value={$layout}>";
  for($i=0;$i<$gc;$i++){
    $garr=explode(',',$glayout[$i]);
    echo "<tr><td><input type=radio name=radio value={$glayout[$i]}></td>";
    echo "<td><input type=text name=gname size=20 value={$garr[0]}></td>";
    echo "<td><input type=text name=gseq size=10 value={$garr[1]}></td>";
    echo "<td><input type=text name=ghostno size=10 value={$garr[2]}></td>";
    echo "<td><input type=text name=gsegno size=10 value={$garr[3]}></td>";
    if($garr[4]=='0'){
      $sumi='未入力';
    }else{
      $sumi='入力済';
    }
    echo "<td><input type=text name=ghaichi size=10 value={$sumi} readonly></td></tr>";
  }
  echo '</table><br>';
  echo "<input type=hidden name=user value={$user}>";
  echo '<input class=button type=submit name=go value="実行">';

  echo '</form>';
}

echo '<br>';
echo "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
echo '</body></html>';
?>
