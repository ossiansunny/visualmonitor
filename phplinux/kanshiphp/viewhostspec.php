<?php
print '<html><head>';
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
print '<title>ホストデータの表示</title>';

require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "phpsnmpprocessset.php";
require_once "phpsnmptcpportset.php";

function reform($_reformVal){
  $reformArr = explode(':',$_reformVal);
  switch ($reformArr[0]) {
    case 'n': 
      $rtnVal = $reformArr[1] . "% 正常";
      break;
    case 'w':
      $rtnVal = $reformArr[1] . "% 警戒";
      break;
    case 'c':  
      $rtnVal = $reformArr[1] . "% 危険";
      break;
    default:
      $rtnVal = "なし";
  }
  return $rtnVal;
}

function bgColor($_cssVal){
  $cssArr = explode(':',$_cssVal);
  switch ($cssArr[0]) {
    case 'n': 
      $rtnVal = "snorm";
      break;
    case 'w':
      $rtnVal = "swarn";	
      break;
    case 'c':  
      $rtnVal = "scrit";
      break;
    default:
      $rtnVal = "sunko";
  }
  return $rtnVal;
}
///
$pgm="viewhostspec.php";
$host="";
$user="";
///------------ main --------------
$get_host = $_GET['host'];
$host = $get_host;
$get_user = $_GET['user'];
$user = $get_user;
$user_sql="select authority,bgcolor from user where userid='".$user."'";
$userRows=getdata($user_sql);
if(empty($userRows)){
  $msg="#error#unkown#ユーザを見失いました";
  branch('logout.php',$msg);
}
$userArr=explode(',',$userRows[0]);
$authority=$userArr[0];
$bgColor=$userArr[1];
$host_sql="select host,groupname,ostype,result,action,viewname,mailopt,tcpport,cpulim,ramlim,disklim,process,image,snmpcomm,agenthost,eventlog,standby  from host where host='".$host."'";
$hostRows = getdata($host_sql);
print "</head><body class={$bgColor}>";
if(empty($hostRows)){
  print "ホストデータがありません<br>";
}else{
  $hostArr = explode(',',$hostRows[0]);
  $groupName = $hostArr[1];
  switch ($hostArr[2]){  /// ostype
    case '0': $osType='Windows';break;
    case '1': $osType="Unix/Linux";break;
    case '2': $osType="Gateway";break;
    case '3': $osType="Others";break;
    default: $osType="データ異常";break;
  }
  $resultSw="0";
  switch ($hostArr[3]){ /// result
    case '0': $result="非監視";$resultSw="0";break;
    case '1': $result="正常";$resultSw="0";break;
    case '2': $result="異常";$resultSw="1";break;
    default: $result="異常";$resultSw="1";break;
  }
  switch ($hostArr[4]){
    case '0': $action='監視なし';$result="非監視";break; 
    case '1': $action="PING監視";break;
    case '2': $action='SNMP監視';break;
    case '3': $action='SNMP通知なし';break;
    case '4': $action='Agent監視';break;
    default: $action="データ異常";break;
  }
  $viewName = $hostArr[5];
    switch ($hostArr[6]){
    case '0': $mailOpt="非送信";break;
    case '1': $mailOpt="自動送信";break;
    default: $mailOpt="データ異常";break;
  }
  $tcpPort = $hostArr[7];
  if($hostArr[8]==""){$cpuLim="";}else{$cpuLim=$hostArr[8]."%";}
  if($hostArr[9]==""){$ramLim="";}else{$ramLim=$hostArr[9]."%";}
  if($hostArr[10]==""){$diskLim="";}else{$diskLim=$hostArr[10]."%";}
  $process = $hostArr[11];
  $image = $hostArr[12];

  if($resultSw == "0") {
    print "<h2><img src='header/php.jpg' width='30' height='30'>&emsp;&emsp;▽　監視対象ホスト：{$viewName}　▽</h2>";
  }else{
    print "<h2><font color='red'><img src='header/php.jpg' width='30' height='30'>&emsp;&emsp;▽　監視対象ホスト：{$viewName}　▽</font></h2>";
  }
  print '<table border=1>';
  print '<tr><th>ホスト名</th><th>グループ名</th><th>OS種類</th><th>結果</th><th>死活</th><th>表示名</th><th>メール</th></tr>';
  print '<tr>';
  print "<td>{$host}</td>";
  print "<td>{$groupName}</td>";
  print "<td>{$osType}</td>";
  print "<td>{$result}</td>";
  print "<td>{$action}</td>";
  print "<td>{$viewName}</td>";
  print "<td>{$mailOpt}</td>";
  print '</tr>';
  print '<tr><th>TCPポート</th><th>CPU警告</th><th>メモリ警告</th><th>ディスク警告</th><th colspan="2">監視プロセス</th><th>画像</th></tr>';
  print '<tr>';
  print "<td>{$tcpPort}</td>";
  print "<td>{$cpuLim}</td>";
  print "<td>{$ramLim}</td>";
  print "<td>{$diskLim}</td>";
  print "<td colspan='2'>{$process}</td>";
  print "<td>{$image}</td>";
  print '</tr>';
  print '</table>';
  if (strpos($host,'127.0.0.') !== false){
    if ($host!='127.0.0.1'){
      print '<table border=1>';
      print '<tr><th>リモートエージェントホスト</th></tr>';
      $agentHost=$hostArr[14];
      print "<tr><td>{$agentHost}</td></tr>";
      print '</table>';
    }
  }
  print '<br><br>';
  if($hostArr[4]=="2" or $hostArr[4]=="3" or $hostArr[4]=="4"){
    $statis_sql="select host,tstamp,gtype,cpuval,ramval,agent,diskval,process,tcpport from statistics where host='".$host."'";
    $statisRows=getdata($statis_sql);
    if($statisRows[0]=="error"){
      $msg=$host . " getstatushost error return";
      writeloge($pgm,$msg);
    } else {  
      $statisArr = explode(',',$statisRows[0]);
      /// format tstamp[1] cpuval[3] ramval[4] diskval[6] process[7] tcpport[8]
      $editCpu = reform($statisArr[3]);
      $editRam = reform($statisArr[4]);
      $editDisk = reform($statisArr[6]);
      $cssCpu = bgColor($statisArr[3]);
      $cssRam = bgColor($statisArr[4]);
      $cssDisk = bgColor($statisArr[6]);
      $process=$statisArr[7];
      $cssProc="snorm"; 
      if($process=="" or $process=="empty"){
        $process="なし";
      }elseif($process!="allok"){
        $cssProc="scrit";
      }
      $tcpPort=$statisArr[8];
      $cssTcp="snorm"; 
      if($tcpPort=="" or $tcpPort=="empty"){ 
        $tcpPort="なし";
      }elseif($tcpPort!="allok"){
        $cssTcp="scrit";
      }
      $timeH = substr($statisArr[1],6,2);
      $timeM = substr($statisArr[1],8,2);
      $timeS = substr($statisArr[1],10,2);
      $timeStamp = $timeH . "時" . $timeM . "分" . $timeS . "秒　現在";
      if($resultSw=="1"){
        $cssCpu="sunko";
        $cssRam="sunko";
        $cssDisk="sunko";
        $cssProc="sunko";
        $cssTcp="sunko";
      }
      print "<h3>SNMP取得情報 {$timeStamp} </h3>";
      print '<table border=1><tr><th>CPU使用率</th><th>メモリ使用率</th><th>ディスク使用率</th><th>停止監視プロセス</th><th>閉鎖TCPポート</th><tr>';
      print "<tr><td class={$cssCpu}>{$editCpu}</td><td class={$cssRam}>{$editRam}</td><td class={$cssDisk}>{$editDisk}</td><td class={$cssProc}>{$process}</td><td class={$cssTcp}>{$tcpPort}</td></tr></table>";
    }
  }
  print '<br><br>';
}

print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</button></a>";
print '</body></html>';
?>

