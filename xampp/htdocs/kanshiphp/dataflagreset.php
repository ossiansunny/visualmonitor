<?php
require_once "mysqlkanshi.php";
/// レイアウトのデータ作成済フラグをリセット
function dataflagreset($layout){
 $sql="select * from g".$layout." order by gsequence";
 $gname="";
 $gseq="";
 $dataflg="";
 $rows=getdata($sql);
 foreach ($rows as $grow){
  $grec=explode(',',$grow);
  $gname=$grec[0];
  $gseq=$grec[1];
  $dataflg=$grec[4];
  if ($gname==""){
    $pat="g".$gseq."%";
    $sql="select * from ".$layout." where gshid like '".$pat."'";
    $hrows=getdata($sql);
    $issw=0;
    foreach ($hrows as $hrow){
      $hrec=explode(',',$hrow);
      if ($hrec[1]!=""){
        $issw=1;
      }   
    }
    if ($issw==0){
      $sql="update g".$layout." set dataflag='0' where gsequence=".$gseq;
      putdata($sql);
    }
  }
 }
}
?>
