<?php
print '<html><head>';
print '<link rel="stylesheet" href="kanshi1_py.css">';
print '<title>ホストデータの表示</title>';
print '</head><body>';

require_once "mysqlkanshi.php";

function reform($reformval){
  $reformarr = explode(':',$reformval);
  switch ($reformarr[0]) {
    case 'n': 
      $rtnval = $reformarr[1] . "% 正常";
      break;
    case 'w':
      $rtnval = $reformarr[1] . "% 警戒";
      break;
    case 'c':  
      $rtnval = $reformarr[1] . "% 危険";
      break;
    default:
      $rtnval = "なし";
  }
  return $rtnval;
}

function ccolor($iroval){
  $iroarr = explode(':',$iroval);
  switch ($iroarr[0]) {
    case 'n': 
      $rtnval = "snorm";
      break;
    case 'w':
      $rtnval = "swarn";	
      break;
    case 'c':  
      $rtnval = "scrit";
      break;
    default:
      $rtnval = "sunko";
  }
  return $rtnval;
}

$pgm="viewhostspec.php";
$host = $_GET['host'];
$user = $_GET['user'];
$sql="select * from host where host='".$host."'";
$data = getdata($sql);
if(empty($data)){
  print "ホストデータがありません<br>";
}else{
  $sdata = explode(',',$data[0]);
  $groupname = $sdata[1];
  switch ($sdata[2]){  // ostype
    case '0': $ostype='Windows';break;
    case '1': $ostype="Unix/Linux";break;
    case '2': $ostype="Gateway";break;
    case '3': $ostype="Others";break;
    default: $ostype="データ異常";break;
  }
  $resultsw="0";
  switch ($sdata[3]){ // result
    case '0': $result="非監視";$resultsw="0";break;
    case '1': $result="正常";$resultsw="0";break;
    case '2': $result="異常";$resultsw="1";break;
    default: $result="異常";$resultsw="1";break;
  }
  switch ($sdata[4]){
    case '0': $action='監視なし';$result="非監視";break; 
    case '1': $action="PING監視";break;
    case '2': $action='SNMP監視';break;
    case '3': $action='SNMP通知なし';break;
    case '4': $action='Agent監視';break;
    default: $action="データ異常";break;
  }
  $viewname = $sdata[5];
    switch ($sdata[6]){
    case '0': $mailopt="非送信";break;
    case '1': $mailopt="自動送信";break;
    default: $mailopt="データ異常";break;
  }
  $tcpport = $sdata[7];
  if($sdata[8]==""){$cpulim="";}else{$cpulim=$sdata[8]."%";}
  if($sdata[9]==""){$ramlim="";}else{$ramlim=$sdata[9]."%";}
  if($sdata[10]==""){$disklim="";}else{$disklim=$sdata[10]."%";}
  $process = $sdata[11];
  $image = $sdata[12];

  if($resultsw == "0") {
    print "<h2><img src='header/php.jpg' width='30' height='30'>&emsp;&emsp;▽　監視対象ホスト：{$viewname}　▽</h2>";
  }else{
    print "<h2><font color='red'><img src='header/php.jpg' width='30' height='30'>&emsp;&emsp;▽　監視対象ホスト：{$viewname}　▽</font></h2>";
  }
  print '<table border=1>';
  print '<tr><th>ホスト名</th><th>グループ名</th><th>OS種類</th><th>結果</th><th>死活</th><th>表示名</th><th>メール</th></tr>';
  print '<tr>';
  print "<td>{$host}</td>";
  print "<td>{$groupname}</td>";
  print "<td>{$ostype}</td>";
  print "<td>{$result}</td>";
  print "<td>{$action}</td>";
  print "<td>{$viewname}</td>";
  print "<td>{$mailopt}</td>";
  print '</tr>';
  print '<tr><th>TCPポート</th><th>CPU警告</th><th>メモリ警告</th><th>ディスク警告</th><th colspan="2">監視プロセス</th><th>画像</th></tr>';
  print '<tr>';
  print "<td>{$tcpport}</td>";
  print "<td>{$cpulim}</td>";
  print "<td>{$ramlim}</td>";
  print "<td>{$disklim}</td>";
  print "<td colspan='2'>{$process}</td>";
  print "<td>{$image}</td>";
  print '</tr>';
  print '</table>';

  print '<br><br>';

  if($sdata[4]=="2"){
    $sql="select * from statistics where host='".$host."'";
    $tdata=getdata($sql);
    if($tdata[0]=="error"){
      $msg=$host . " getstatushost error return";
      writeloge($pgm,$msg);
    } else {  
      $udata = explode(',',$tdata[0]);
      /// format tstamp[1] cpuval[3] ramval[4] diskval[6] process[7] tcpport[8]
      $ucpu = reform($udata[3]);
      $uram = reform($udata[4]);
      $udisk = reform($udata[6]);
      $iroc = ccolor($udata[3]);
      $iror = ccolor($udata[4]);
      $irod = ccolor($udata[6]);
      $upro=$udata[7];
      $irop="snorm"; 
      if($upro=="" || $upro=="empty"){
        $upro="なし";
      }elseif($upro!="allok"){
        $irop="scrit";
      }
      $utcp=$udata[8];
      $irot="snorm"; 
      if($utcp=="" || $utcp=="empty"){ 
        $utcp="なし";
      }elseif($utcp!="allok"){
        $irot="scrit";
      }
      $tsh = substr($udata[1],6,2);
      $tsm = substr($udata[1],8,2);
      $tss = substr($udata[1],10,2);
      $tstamp = $tsh . "時" . $tsm . "分" . $tss . "秒　現在";
      if($resultsw=="1"){
        $iroc="sunko";
        $iror="sunko";
        $irod="sunko";
        $irop="sunko";
        $irot="sunko";
      }
      print "<h3>SNMP取得情報 {$tstamp} </h3>";
      print '<table border=1><tr><th>CPU使用率</th><th>メモリ使用率</th><th>ディスク使用率</th><th>停止監視プロセス</th><th>閉鎖TCPポート</th><tr>';
      print "<tr><td class={$iroc}>{$ucpu}</td><td class={$iror}>{$uram}</td><td class={$irod}>{$udisk}</td><td class={$irop}>{$upro}</td><td class={$irot}>{$utcp}</td></tr></table>";
    }
  }
  print '<br><br>';
}
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</button></a>";
print '</body></html>';
?>
