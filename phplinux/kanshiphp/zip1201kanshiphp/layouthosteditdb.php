<?php
require_once "mysqlkanshi.php";
require_once "dataflagreset.php";

$pgm = "layouthosteditdb.php";
$groupName=array();
$groupName=$_GET['groupname'];

$groupNum=array();
$groupNum=$_GET['groupno'];

$hostArr=$_GET['data'];
$gshKey=$_GET['key'];
$layout=$_GET['type'];
$user=$_GET['user'];

$grpCount=count($groupNum);
for ($i=0;$i<$grpCount;$i++){  
  $gkey=$groupNum[$i];
  $gvalue=$groupName[$i];
  $upsql='update g'.$layout.' set gname="'.$gvalue.'" where gsequence='.strval($gkey);
  putdata($upsql);
}

$keyMax=count($gshKey);
for ($i=0;$i<$keyMax;$i++){ 
  $hostKey=$gshKey[$i];
  $hostValue=$hostArr[$i];
  $layout_sql="delete from {$layout} where gshid='{$hostKey}'";
  putdata($layout_sql);
  writelogd($pgm,$layout_sql);
  $layout_sql="insert into {$layout} values('{$hostKey}','{$hostValue}')";
  putdata($layout_sql);
  writelogd($pgm,$layout_sql);
}
/// ホストデータなしの場合、グループデータフラグをリセット
dataflagreset($layout);

print '<html><head><meta>';
print '<link rel="stylesheet" href="css/kanshi1.css">';
print '</head><body>';
print '<h4>変更処理が終わりました、「監視モニターへ戻る」をクリックして下さい</h4>';
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body></html>';
?>

