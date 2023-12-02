<?php
require_once "mysqlkanshi.php";
require_once "dataflagreset.php";

echo '<html><head><meta>';
echo '<link rel="stylesheet" href="kanshi1.css">';
echo '</head><body>';
$pgm = "layoutsupmapdb.php";
$groupname=array();
$groupname=$_GET['groupname'];

$groupno=array();
$groupno=$_GET['groupno'];

$data=$_GET['data'];
$key=$_GET['key'];
$layout=$_GET['type'];
$user=$_GET['user'];

$gmax=count($groupno);
for ($i=0;$i<$gmax;$i++){  
  $gkey=$groupno[$i];
  $gvalue=$groupname[$i];
  $upsql='update g'.$layout.' set gname="'.$gvalue.'" where gsequence='.strval($gkey);
  putdata($upsql);
}

$max=count($key);
for ($i=0;$i<$max;$i++){ 
  $hkey=$key[$i];
  $hvalue=$data[$i];
  $upsql='update '.$layout.' set host="'.$hvalue.'" where gshid="'.$hkey.'"';
  putdata($upsql);   
}
/// ホストデータなしの場合、グループデータフラグをリセット
dataflagreset($layout);

echo '<h4>変更処理が終わりました、「監視モニターへ戻る」をクリックして下さい</h4>';
echo "<a href='MonitorManager.php?param={$user}'><span class=button>監視モニターへ戻る</span></a>";
echo '</body></html>';
?>
