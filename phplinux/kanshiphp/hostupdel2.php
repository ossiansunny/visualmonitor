<?php
require_once "mysqlkanshi.php";

$pgm="hostupdel2.php";
 
///
///-------------------------------
///-- ホストデータ更新処理 -------
///-------------------------------
  /// update sql作成]


  /// get host layout
for($c=1;$c<50;$c++){ 
  writeloge($pgm,"scan ".strval($c)." 開始 ".date('H:i:s'));
  $layout_sql='select host from layout where NOT (host="No Assign" or host="") for update';
  $layoutRows=getdata($layout_sql);
  //var_dump($layoutRows);
  foreach($layoutRows as $layoutRow){
    $layoutHost=$layoutRow;
    ///
    $timestamp=date('H:i:s');
    $host_sql="update host set viewname='".$timestamp."' where host='".$layoutHost."'";
    $hostRows=putdata($host_sql);
    writeloge($pgm,$host_sql);    
    sleep(1);
  }
  writeloge($pgm,"scan ".strval($c)." 終了 ".date('H:i:s'));
   
}
print $pgm." ended";
?>

