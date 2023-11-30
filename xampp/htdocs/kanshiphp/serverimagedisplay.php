<?php
require_once 'mysqlkanshi.php';

function hostimagelist(){  
  echo '<h3>▽　ホスト画像　▽</h3>';
  $svi=array();
  $svn=array();
  $sql='select * from serverimage order by image';
  $rows=getdata($sql);
  $cnt=0;
  foreach ($rows as $iitem){
    $iitemlist=explode(',',$iitem);
    $svi[$cnt]=$iitemlist[0];
    $svn[$cnt]=$iitemlist[1];
    $cnt++;
  }
  echo '<table border=1><tr>';
  $svin=count($svi);
  for ($cnt=0;$cnt<$svin;$cnt++){
    echo "<th>{$svn[$cnt]}</th>";
  }
  echo '</tr><tr>';
  $svin=count($svi);
  for ($cnt=0;$cnt<$svin;$cnt++){
    $hlist=explode('.',$svi[$cnt]);
    $himg=$hlist[0].'1.png';
    echo "<td align=center><img src='hostimage/{$himg}' class=size></td>";
    
  }
  echo '</tr></table>';  
}
?>
