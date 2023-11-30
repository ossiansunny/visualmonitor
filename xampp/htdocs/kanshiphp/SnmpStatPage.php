<?php
require_once "mysqlkanshi.php";

$value="";
$host="";
$tstamp="";
$user="";
$auth="";
if (!isset($_GET['param'])){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="SnmpStatPage.php" method="get">';
  echo '<input type="hidden" name="param" value="">';
  echo '<input type="submit" name="next" style="display:none;" />';
  echo '</form></body></html>';
  echo '<script type="text/javascript">';
  echo 'var keyvalue = sessionStorage.getItem("user");';
  echo 'if (!keyvalue) {';
  echo '  keyvalue = "unknown";';
  echo '}';
  echo 'document.forms["F"].elements["param"].value = keyvalue;';
  echo '</script>';
}else{
  $inform=$_GET['param'];
  if (substr($inform,0,1)=="#"){
    $brarr=explode("#",ltrim($inform,"#"),4);
    $brcode=$brarr[0];
    $user=$brarr[1];
    $brmsg=$brarr[2];
  }else{
    $user=$inform;    
  }
  echo '<html><head>';
  echo '<link rel="stylesheet" href="kanshi1_py.css">';
  echo '</head><body>';
  if ($brcode!=""){
    echo "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }
  echo '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　SNMP監視結果表示　▽</h2>';

  ///
  ///---------------画面表示処理 --------------------
  ///
  
  echo '<h4>CPU,RAM,Disk欄のn:は正常、w:は警告閾値超、c:は危険閾値超、プロセス欄は停止、TCPポート欄はクロースを示す<br>';
  echo 'タイプ欄 &nbsp; 0:未監視 &nbsp; 1:無応答 &nbsp; 4:一部異常 &nbsp; 9:スタンバイ<br>';
  echo 'エージェント欄 &nbsp; ok:監視範囲正常 &nbsp; ng:監視範囲異常</h4>';
  echo '<table border=1>';
  echo '<tr><th >ホスト</th><th>タイムスタンプ</th><th>タイプ</th><th>CPU Limit</th><th>RAM Limit</th><th>Disk Limit</th><th>プロセス</th><th>TCPポート</th><th>エージェント</th></tr>';
  $sql="select host,tstamp,gtype,ifnull(cpuval,''),ifnull(ramval,''),ifnull(agent,''),ifnull(diskval,''),ifnull(process,''),ifnull(tcpport,'') from statistics";
  $rows=getdata($sql);
  echo '<form name="rform" method="get" action="snmpstatdeldb.php">';
  foreach ($rows as $strdata){
    $sdata=explode(',',$strdata);
    $host=$sdata[0];
    $tstamp=$sdata[1];
    echo "<tr><td><input type='checkbox' name='ckdata[]' value={$strdata} >{$host}</td>";
    echo "<td> &nbsp;{$tstamp}</td>";
    echo "<td> &nbsp;{$sdata[2]}</td>";
    echo "<td> &nbsp;{$sdata[3]}</td>";
    echo "<td> &nbsp;{$sdata[4]}</td>";
    echo "<td> &nbsp;{$sdata[6]}</td>";
    echo "<td> &nbsp;{$sdata[7]}</td>";
    echo "<td> &nbsp;{$sdata[8]}</td>";
    echo "<td> &nbsp;{$sdata[5]}</td>";
    echo '</tr>';
  }
  echo '</table>';
  $selsql='select authority from user where userid="'.$user.'"';
  $udata=getdata($selsql);
  $sdata=explode(',',$udata[0]);
  $auth=$sdata[0];
/*
  if ($auth=='1'){    
    echo "<input type='hidden' name='userid' value={$user}>";
    echo "<input type='hidden' name='authcd' value={$auth}>";
    echo '<br><input class=buttondel type="submit" name="delete" value="削除" >';
    
  }
*/
  echo '</form>';
  echo '<br><br>';
  echo "<a href='MonitorManager.php?param={$user}'><span class=button>監視モニターへ戻る</span></a>"; 
  echo '</body></html>';
  
}
?>
