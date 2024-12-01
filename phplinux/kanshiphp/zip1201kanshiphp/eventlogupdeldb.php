<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "mailsendevent.php";
///

$pgm = "eventlogupdeldb.php";
$user = $_GET['user'];
$checkRadio = $_GET['cradio'];    /// 選択されたボタン
if (!isset($checkRadio)){
  $msg='checkbox unknown data found:'.$checkRadio;
  writeloge($pgm,$msg);
  $msg="#error#".$user."#チェックボックスにチェックをして下さい";
  $nextpage='EventLogPage.php';
  branch($nextpage,$msg);
}
$eventLogRow = $_GET['fckbox'];  /// 選択されたeventlog
$adminId = $_GET['kanrimei']; /// 障害種類
$cnfClsNum = $_GET['kanrino']; /// 障害番号
$memoMsg = $_GET['memomsg'];
if (is_null($memoMsg)){
  $memoMsg = '';
}
///----------------------------------------------------------------------------------
///  "0:host" "1:eventtime" "2:eventtype" "3:snmptype" "4:snmpvalue(NULL)"
///  "5:kanrisha(NULL)" "6:kanrino(NULL)" "7:confclose" "8:mailsend" "9:message(NULL)"
///-----------------------------------------------------------------------------------
///--- checkbox ボタン処理 -----------
$confClose = "0";
$statType = "";
$eventStamp = date('ymdHis');
/// イベントデータ
$eventLogArr=explode(',',$eventLogRow);
$ev_host = $eventLogArr[0];  /// host
$ev_snmpType = $eventLogArr[3];
$ev_snmpVal = $eventLogArr[4];
$ev_cnfCls = $eventLogArr[7];
$ev_msg = $eventLogArr[9];
///
/// ラヂオボタン処理
///
if ($checkRadio=='confirm'){ /// 「障害確認」ボタン ---> statistics gtype=5
  $statType = "5"; /// 確認
  /// あれば更新 
  $stat_sql="select gtype from statistics where host='".$ev_host."'";
  $statRows=getdata($stat_sql);
  if (isset($statRows)){ 
    $stat_sql='update statistics set gtype="'.$statType.'" where host="'.$ev_host.'"';
    putdata($stat_sql);
    writelogd($pgm,$stat_sql);
  }
  $eventType="3"; ///監視管理
  $confClose = "1"; ///確認
  $mailOpt="0";
  $event_sql='insert into eventlog values("'.$ev_host.'","'.$eventStamp.'","'.$eventType.'","'.$ev_snmpType.'","'.$ev_snmpVal.'","'.$adminId.'","'.$cnfClsNum.'","'.$confClose.'","'.$mailOpt.'","'.$msg.'")';
  putdata($event_sql);
  writeloge($pgm,$event_sql);
  $host_sql='update host set result="8" where host="'.$ev_host.'"';
  putdata($host_sql); 
  writelogd($pgm,$host_sql);
}elseif ($checkRadio=='close'){
  /// 「処置完了」ボタン ---> statistics gtype=7
  if ($ev_cnfCls!='2'){
    $msg="#error#".$user."#クローズ処理は「確認済」ログを選択して下さい";
    $nextpage="EventLogPage.php";
    branch($nextpage,$msg);
  }
  $confClose = "2";          /// confclose=2
  mailsendevent($eventLogRow,$adminId,$cnfClsNum,$confClose,$memoMsg);
  $event_sql="delete from eventlog where host='".$ev_host."'";
  putdata($event_sql);
  $eventType="3"; ///監視管理
  $confClose = "3";  /// クローズ  
  $mailOpt = "1";
  $event_sql='insert into eventlog values("'.$ev_host.'","'.$eventStamp.'","'.$eventType.'","'.$stype.'","'.$svalue.'","'.$user.'","'.$cnfClsNum.'","'.$confClose.'","'.$mailOpt.'","'.$msg.'")';
  putdata($event_sql);
  writelogd($pgm,$event_sql);
  /// あれば更新
  $stat_sql="select gtype from statistics where host='".$ev_host."'";
  $statRows=getdata($stat_sql);
  if (isset($statRows)){
    $stat_sql="delete from statistics where host='".$ev_host."'";
    putdata($stat_sql);
    $stat_sql='insert into statistics (host,tstamp,gtype) values("'.$ev_host.'","000000000000","9")';
    putdata($stat_sql);
    writelogd($pgm,$stat_sql);
  }   
  $memo_sql='insert into eventmemo values("'.$eventStamp.'","'.$ev_host.'","'.$adminId.'","'.$cnfClsNum.'","'.$memoMsg.'")';
  putdata($memo_sql);
  writeloge($pgm,$memo_sql);

}elseif ($checkRadio=='memo'){  /// 「メモ保存」ボタン
  $confClose="5";               /// confclose=5
  $memo_sql='insert into eventmemo values("'.$eventStamp.'","'.$ev_host.'","'.$adminId.'","'.$cnfClsNum.'","'.$memoMsg.'")';
  putdata($memo_sql);
}

$nextpage="EventLogPage.php";
branch($nextpage,$user);


?>
