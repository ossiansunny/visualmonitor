<?php
require_once 'BaseFunction.php';
require_once 'mysqlkanshi.php';
error_reporting(E_ALL & ~E_NOTICE);

print '<html>';
print '<body><head>';
print '<link rel="stylesheet" href="kanshi1.css">';
print '</head><body>';
print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　ホストレイアウト作成　その３　▽</h2>';
print '<h4>ホスト配置情報入力</h4>';
print '<h4>☆各々のホスト名下欄に<b>IPアドレス</b>または名前解決できる<b>ホスト名</b>を入力する<br>';
print '&ensp;&ensp;入力の無い場合は空欄でレイアウトします</h4><br>';

$radio="";
$grp = array();
$seg = array();
$hst = array();
$gh = array($grp,$seg,$hst);
$user=$_GET['user'];
//$glayout=$_GET['glayout'];
$laynick=$_GET['laynick'];
//$laynick=explode('_',$glayout);
if (isset($_GET['radio'])) {
  $radio = $_GET['radio'];
}else{
  $msg="#error#".$user."#".$laynick."/グループを選択して下さい";
  $nextpage="layouts.php";
  branch($nextpage,$msg);
}
$rarr=explode(',',$radio);
$gname=$rarr[0];
$gseq=$rarr[1]; ///group sequence number
$ghost=$rarr[2];
$gseg=$rarr[3];
$sumi=$rarr[4]; 
if($sumi=='1'){
  $gid='g'.$gseq.'%';
  $selsql="select * from layout_".$laynick." where gshid like '".$gid."'";
  $hdata=getdata($selsql);
  $hdatac=count($hdata);
  print '<form name=myname action=layoutsdb.php method=get>';
  print "<input type=hidden name=laynick value={$laynick}>";
  //print "<input type=hidden name=layoutname value={$glayout}>";
  print "<h4>グループ名：{$gname}<input type=hidden name=gseq value={$gseq}></h4>";
  print '<table border=1>';
  print '<tr>';
  $dn=intval($gseg);
  $hs=intval($ghost);
  for($hsc=0;$hsc<$hs;$hsc++){
    print '<th>ホスト名</th>';
  }
  print '</tr>';
  
  $hcc=0;
  while($hcc<$hdatac){    
    for($dnc=0;$dnc<$dn;$dnc++){ 
      print '<tr>';
      for($hsc=0;$hsc<$hs;$hsc++){
        $hdarr=explode(',',$hdata[$hcc]);
        print "<td><input type=text name=host[{$dnc}][{$hsc}] size=20 value={$hdarr[1]}></td>";
        $hcc++;
      }	
      print '</tr>';  
    }    
  }
  print '</table>';
  print "<input type=hidden name=user value={$user}>";
  print '<br><input class=button type=submit name=go value="実行">';
  print '</form>';
}else{
  $dn=intval($gseg);
  $hs=intval($ghost);
  print '<form name=myname action=layoutsdb.php method=get>';
  print "<input type=hidden name=laynick value={$laynick}>";
  //print "<input type=hidden name=layoutname value={$glayout}>";
  print "<h4>グループ名：{$gname}<input type=hidden name=gseq value={$gseq}></h4>";
  print '<table border=1>';
  print '<tr>';
  for($hsc=0;$hsc<$hs;$hsc++){
    print'<th>ホスト名</th>';
  }
  print '</tr>';
  for($dnc=0;$dnc<$dn;$dnc++){ 
    print '<tr>';
    for($hsc=0;$hsc<$hs;$hsc++){
      print "<td><input type=text name=host[{$dnc}][{$hsc}] size=20 value=''></td>";
    }	
    print '</tr>';  
  }
  print '</table>';
  print "<input type=hidden name=user value={$user}>";
  print '<br><input class=button type=submit name=go value="実行">';
  print '</form>';
}
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body></html>';
?>
