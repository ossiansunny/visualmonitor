<?php
require_once 'mysqlkanshi.php';
echo '<html><head>';
echo '<link rel="stylesheet" href="kanshi1.css">';
echo '</head><body>';
//$garr = array();
//$harr = array();
//$gharr = array($garr,$harr);
$layout=substr($_GET['layoutname'],1); // cut left g
$layarr=explode('_',$layout);
$user = $_GET['user'];
$gharr = $_GET['host'];
$gseq=$_GET['gseq'];
$gseqi=intval($gseq);
$count=count($gharr[0]);
$gshid='g'.$gseq.'%';
$gc=count($gharr);
//for($i=0;$i<$gc;$i++){
//  $hc=count($gharr[$i]); 
//  echo 'count: strval('.$i.') count=strval('.$hc.')<br>';
//}
$delsql='delete from '.$layout.' where gshid like "'.$gshid.'"';
//echo $delsql.'<br>';
putdata($delsql);
for($cc=0;$cc<$gc;$cc++){
  $hc=count($gharr[$cc]);
  for($dd=0;$dd<$hc;$dd++) {
    //echo 'g'.strval($gseq).'s'.strval($cc).'h'.strval($dd);
    $gshid='g'.strval($gseq).'s'.strval($cc).'h'.strval($dd);
    //echo strval($gharr[$cc][$dd]).'<br>';
    $host=strval($gharr[$cc][$dd]);
    $insql='insert into '.$layout.' values("'.$gshid.'","'.$host.'")';
    //echo $insql.'<br>';
    putdata($insql);
  }
}
$upsql='update g'.$layout.' set dataflag="1" where gsequence='.$gseqi;
//echo $upsql.'<br>';
putdata($upsql);
$backurl='layouts.php?laynick='.$layarr[1];
echo '<h2>▽　グループレイアウト作成　その４　▽</h2>';
echo "<h4>レイアウト名称： {$layarr[1]} のグループ内ホスト情報を書き込みました<br>";
echo '☆別のホスト未入力グルーブを実行する場合は、「ホストレイアウト作成　その２へ 戻る」をクリック<br>';
echo '☆終了する場合は、「監視モニターへ戻る」をクリックして下さい</h4>';
echo "&emsp;<a href='{$backurl}'><span class=button>ホストレイアウト作成　その２へ 戻る</span></a><br><br>";
echo "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
echo '</body></html>';

?>
