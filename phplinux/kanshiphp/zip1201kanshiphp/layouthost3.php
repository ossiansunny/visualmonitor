<?php
require_once 'BaseFunction.php';
require_once 'mysqlkanshi.php';
error_reporting(E_ALL & ~E_NOTICE);

print '<html>';
print '<body><head>';
print '<link rel="stylesheet" href="css/kanshi1.css">';
print '</head><body>';
print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　ホストレイアウト作成　その３　▽</h2>';
print '<h4>ホスト配置情報入力</h4>';
print '<h4>☆各々のホスト名下欄に<b>IPアドレス</b>または名前解決できる<b>ホスト名</b>を入力する<br>';
print '&ensp;&ensp;入力の無い場合は空欄でレイアウトします</h4><br>';

$radio="";
$grp = array();
$seg = array();
$hst = array();
$user=$_GET['user'];
$layoutNick=$_GET['laynick'];
if (isset($_GET['radio'])) {
  $radio = $_GET['radio'];
}else{
  $msg="#error#".$user."#".$layoutNick."/グループを選択して下さい";
  $nextpage="layouthost2.php";
  branch($nextpage,$msg);
}
$grpLayoutArr=explode(',',$radio);
$grpName=$grpLayoutArr[0];
$grpSeq=$grpLayoutArr[1]; ///group sequence number
$grpHostNum=$grpLayoutArr[2];
$grpSegNum=$grpLayoutArr[3];
$finishFlag=$grpLayoutArr[4]; 
if($finishFlag=='1'){
  $gid='g'.$grpSeq.'%';
  $layout_sql="select * from layout_".$layoutNick." where gshid like '".$gid."'";
  $hostLayoutRows=getdata($layout_sql);
  $hostLayoutCount=count($hostLayoutRows);
  print '<form name=myname action=layouthostdb.php method=get>';
  print "<input type=hidden name=laynick value={$layoutNick}>";
  print "<h4>グループ名：{$grpName}<input type=hidden name=gseq value={$grpSeq}></h4>";
  print '<table border=1>';
  print '<tr>';
  $dn=intval($grpSegNum);
  $hs=intval($grpHostNum);
  for($hsc=0;$hsc<$hs;$hsc++){
    print '<th>ホスト名</th>';
  }
  print '</tr>';
  
  $hcc=0;
  while($hcc<$hostLayoutCount){    
    for($dnc=0;$dnc<$dn;$dnc++){ 
      print '<tr>';
      for($hsc=0;$hsc<$hs;$hsc++){
        $hostLayoutArr=explode(',',$hostLayoutRows[$hcc]);
        print "<td><input type=text name=host[{$dnc}][{$hsc}] size=20 value={$hostLayoutArr[1]}></td>";
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
  $dn=intval($grpSegNum);
  $hs=intval($grpHostNum);
  print '<form name=myname action=layouthostdb.php method=get>';
  print "<input type=hidden name=laynick value={$layoutNick}>";
  print "<h4>グループ名：{$grpName}<input type=hidden name=gseq value={$grpSeq}></h4>";
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

