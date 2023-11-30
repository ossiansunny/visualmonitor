<?php
require_once 'mysqlkanshi.php';
error_reporting(E_ALL & ~E_NOTICE);

echo '<html>';
echo '<body><head>';
echo '<link rel="stylesheet" href="kanshi1.css">';
echo '</head><body>';
echo '<h2>▽　ホストレイアウト作成　その３　▽</h2>';
echo '<h4>ホスト配置情報入力</h4>';
echo '<h4>☆各々のホスト名下欄に<b>IPアドレス</b>または名前解決できる<b>ホスト名</b>を入力する<br>';
echo '&ensp;&ensp;入力の無い場合は空欄でレイアウトします</h4><br>';

$grp = array();
$seg = array();
$hst = array();
$gh = array($grp,$seg,$hst);
$user=$_GET['user'];
$layout=$_GET['layout'];
$laynick=explode('_',$layout);
$radio = $_GET['radio'];

$rarr=explode(',',$radio);
$gname=$rarr[0];
$gseq=$rarr[1]; //group sequence number
$ghost=$rarr[2];
$gseg=$rarr[3];
$sumi=$rarr[4]; //no check
if($sumi=='1'){
  $gid='g'.$gseq.'%';
  $selsql="select * from layout_".$laynick[1]." where gshid like '".$gid."'";
  $hdata=getdata($selsql);
  $hdatac=count($hdata);
  echo '<form name=myname action=layoutsdb.php method=get>';
  echo "<input type=hidden name=layoutname value={$layout}>";
  echo "<h4>グループ名：{$gname}<input type=hidden name=gseq value={$gseq}></h4>";
  echo '<table border=1>';
  echo '<tr>';
  $dn=intval($gseg);
  $hs=intval($ghost);
  for($hsc=0;$hsc<$hs;$hsc++){
    echo '<th>ホスト名</th>';
  }
  echo '</tr>';
  
  $hcc=0;
  while($hcc<$hdatac){    
    for($dnc=0;$dnc<$dn;$dnc++){ 
      echo '<tr>';
      //echo 'dnc:'.strval($dnc).' dh:'.$gseq.'<br>';
      for($hsc=0;$hsc<$hs;$hsc++){
        $hdarr=explode(',',$hdata[$hcc]);
        //echo $hdarr[0].' '.$hdarr[1].'<br>';
        echo "<td><input type=text name=host[{$dnc}][{$hsc}] size=20 value={$hdarr[1]}></td>";
        //echo '<td><input type=text name=host['.$dnc.']['.$hsc.'] size=20 value="'.$hdarr[1].'"></td>';
        //echo 'dnc:'.strval($dnc).' dh:'.$gseq.' hsc: '.strval($hsc).' hs:'.$ghost.'<br>';
        $hcc++;
      }	
      echo '</tr>';  
    }    
  }
  echo '</table>';
  echo "<input type=hidden name=user value={$user}>";
  echo '<br><input class=button type=submit name=go value="実行">';
  echo '</form>';
}else{
  $dn=intval($gseg);
  $hs=intval($ghost);
  echo '<form name=myname action=layoutsdb.php method=get>';
  echo "<input type=hidden name=layoutname value={$layout}>";
  echo "<h4>グループ名：{$gname}<input type=hidden name=gseq value={$gseq}></h4>";
  echo '<table border=1>';
  echo '<tr>';
  for($hsc=0;$hsc<$hs;$hsc++){
    echo'<th>ホスト名</th>';
  }
  echo '</tr>';
  for($dnc=0;$dnc<$dn;$dnc++){ 
    echo '<tr>';
    for($hsc=0;$hsc<$hs;$hsc++){
      echo "<td><input type=text name=host[{$dnc}][{$hsc}] size=20 value=''></td>";
    }	
    echo '</tr>';  
  }
  echo '</table>';
  echo "<input type=hidden name=user value={$user}>";
  echo '<br><input class=button type=submit name=go value="実行">';
  echo '</form>';
}
echo "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
echo '</body></html>';
?>
