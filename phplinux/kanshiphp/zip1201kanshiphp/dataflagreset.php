<?php
require_once "mysqlkanshi.php";
/// レイアウトのデータ作成済フラグをリセット
function dataflagreset($_layout){
 $layout_sql="select * from g".$_layout." order by gsequence";
 $grpName="";
 $grpSeq="";
 $layoutRows=getdata($layout_sql);
 foreach ($layoutRows as $layoutRowsRec){
  $layoutArr=explode(',',$layoutRowsRec);
  $grpName=$layoutArr[0];
  $grpSeq=$layoutArr[1];
  if ($grpName==""){
    $pattern="g".$grpSeq."%";
    $layout_sql="select * from ".$_layout." where gshid like '".$pattern."'";
    $layoutRows=getdata($layout_sql);
    $isSw=0;
    foreach ($layoutRows as $layoutRowsec){
      $layoutArr=explode(',',$layoutRowsRec);
      if ($layoutArr[1]!=""){
        $isSw=1;
      }   
    }
    if ($isSw==0){
      $layout_sql="update g".$_layout." set dataflag='0' where gsequence=".$grpSeq;
      putdata($layout_sql);
    }
  }
 }
}
?>
