﻿<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
$pgm="SnmpStatPage.php";
$value="";
$host="";
$timeStamp="";
$user="";
$brcode="";
$brmsg="";

if (!isset($_GET['param'])){
  paramGet($pgm);
  ///
}else{
  paramSet();
  $user_sql="select authority,bgcolor from user where userid='".$user."'";
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    branch('logout.php',$msg);
  }
  $userArr=explode(',',$userRows[0]);
  $authority=$userArr[0];
  $bgColor=$userArr[1];
  ///  
  print '<html><head>';
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';
  print "</head><body class={$bgColor}>";
  ///
  if ($brcode=='erroe' or $brcode=='alert' or $brcode=='notic'){
    print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }
  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　SNMP監視結果表示　▽</h2>';
  ///
  ///---------------画面表示処理 --------------------
  ///
  
  print '<h3>CPU Limit,RAM Limit,Disk Limit欄のn:正常、w:警告閾値超、c:危険閾値超<br>';
  print 'プロセス欄 &nbsp; allok:全プロセス稼働 &nbsp; empty:指定なし &nbsp; 番号:非稼働プロセス<br>';
  print 'TCPポート蘭 &nbsp; allok:全ポートオープン &nbsp; empty:指定なし &nbsp; 番号:クローズポート<br>';
  print 'タイプ欄 &nbsp; 0:未監視 &nbsp; 1:無応答 &nbsp; 3:正常 &nbsp; 4:一部異常 &nbsp; 9:ホストスタンバイ<br>';
  print 'エージェント欄 &nbsp; ok:監視範囲正常 &nbsp; ng:監視範囲異常 &nbsp; sb:	エージェントスタンバイ</h3>';
  print '<table border=1>';
  print '<tr><th >ホスト</th><th>タイムスタンプ</th><th>タイプ</th><th>CPU Limit</th><th>RAM Limit</th><th>Disk Limit</th><th>プロセス</th><th>TCPポート</th><th>エージェント</th></tr>';
  $host_sql="select host,tstamp,gtype,ifnull(cpuval,''),ifnull(ramval,''),ifnull(agent,''),ifnull(diskval,''),ifnull(process,''),ifnull(tcpport,'') from statistics";
  $hostRows=getdata($host_sql);
  print '<form name="rform" method="get" action="snmpstatdeldb.php">';
  foreach ($hostRows as $hostRowsRec){
    $hostArr=explode(',',$hostRowsRec);
    $host=$hostArr[0];
    $timeStamp=$hostArr[1];
    print "<tr><td><input type='checkbox' name='ckdata[]' value={$hostRowsRec} >{$host}</td>";
    print "<td> &nbsp;{$timeStamp}</td>";
    print "<td> &nbsp;{$hostArr[2]}</td>";
    print "<td> &nbsp;{$hostArr[3]}</td>";
    print "<td> &nbsp;{$hostArr[4]}</td>";
    print "<td> &nbsp;{$hostArr[6]}</td>";
    print "<td> &nbsp;{$hostArr[7]}</td>";
    print "<td> &nbsp;{$hostArr[8]}</td>";
    print "<td> &nbsp;{$hostArr[5]}</td>";
    print '</tr>';
  }
  print '</table>';
  print '</form>';
  print '<br><br>';
  print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>"; 
  print '</body></html>';  
}
?>

