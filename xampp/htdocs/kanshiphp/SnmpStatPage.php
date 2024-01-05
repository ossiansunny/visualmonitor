<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
$pgm="SnmpStatPageex.php";
$value="";
$host="";
$tstamp="";
$user="";
$brcode="";
$brmsg="";
$auth="";
if (!isset($_GET['param'])){
  paramGet($pgm);
  ///
}else{
  paramSet();
  ///  
  print '<html><head>';
  print '<link rel="stylesheet" href="kanshi1_py.css">';
  print '</head><body>';
  ///
  if ($brcode=='erroe' or $brcode=='alert' or $brcode=='notic'){
    print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }
  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　SNMP監視結果表示　▽</h2>';
  ///
  ///---------------画面表示処理 --------------------
  ///
  
  print '<h4>CPU,RAM,Disk欄のn:は正常、w:は警告閾値超、c:は危険閾値超、プロセス欄は停止、TCPポート欄はクロースを示す<br>';
  print 'タイプ欄 &nbsp; 0:未監視 &nbsp; 1:無応答 &nbsp; 4:一部異常 &nbsp; 9:スタンバイ<br>';
  print 'エージェント欄 &nbsp; ok:監視範囲正常 &nbsp; ng:監視範囲異常</h4>';
  print '<table border=1>';
  print '<tr><th >ホスト</th><th>タイムスタンプ</th><th>タイプ</th><th>CPU Limit</th><th>RAM Limit</th><th>Disk Limit</th><th>プロセス</th><th>TCPポート</th><th>エージェント</th></tr>';
  $sql="select host,tstamp,gtype,ifnull(cpuval,''),ifnull(ramval,''),ifnull(agent,''),ifnull(diskval,''),ifnull(process,''),ifnull(tcpport,'') from statistics";
  $rows=getdata($sql);
  print '<form name="rform" method="get" action="snmpstatdeldb.php">';
  foreach ($rows as $strdata){
    $sdata=explode(',',$strdata);
    $host=$sdata[0];
    $tstamp=$sdata[1];
    print "<tr><td><input type='checkbox' name='ckdata[]' value={$strdata} >{$host}</td>";
    print "<td> &nbsp;{$tstamp}</td>";
    print "<td> &nbsp;{$sdata[2]}</td>";
    print "<td> &nbsp;{$sdata[3]}</td>";
    print "<td> &nbsp;{$sdata[4]}</td>";
    print "<td> &nbsp;{$sdata[6]}</td>";
    print "<td> &nbsp;{$sdata[7]}</td>";
    print "<td> &nbsp;{$sdata[8]}</td>";
    print "<td> &nbsp;{$sdata[5]}</td>";
    print '</tr>';
  }
  print '</table>';
  $selsql='select authority from user where userid="'.$user.'"';
  $udata=getdata($selsql);
  $sdata=explode(',',$udata[0]);
  $auth=$sdata[0];
  print '</form>';
  print '<br><br>';
  print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>"; 
  print '</body></html>';
  
}
?>

