<?php
require_once 'mysqlkanshi.php';

function hostimagelist($imageRows,$type){
  /// type:1 イメージ名、表示名  type:2 イメージ名、
  if($type==1){  
    print '<h3>▽　登録可能ホスト画像　▽</h3>';
  }else{
    print '<h3>▽　登録済ホスト画像　▽</h3>';
  }
  $svrImage=array();
  $svrViewName=array();
  $index=0;
  foreach ($imageRows as $imageRowsRec){
    $imageArr=explode(',',$imageRowsRec);
    $svrImage[$index]=$imageArr[0];
    if($type==2){
      $svrViewName[$index]=$imageArr[1];
    }
    $index++;
  }
  print '<table border=1><tr>';
  $svrImageCount=count($svrImage);
  for ($index=0;$index<$svrImageCount;$index++){
    print "<th>{$svrImage[$index]}</th>";
  }
  print '</tr><tr>';
  $svrImageCount=count($svrImage);
  for ($index=0;$index<$svrImageCount;$index++){
    $targetImage=explode('.',$svrImage[$index]);
    $image1Png=$targetImage[0].'1.png';
    print "<td align=center><img src='hostimage/{$image1Png}' class=size></td>";
    
  }
  print '</tr></table>';  
}
?>

