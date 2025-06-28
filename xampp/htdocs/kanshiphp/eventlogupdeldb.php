<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "mailsend.php";
///
function getautokanrino(){
  /// yymmdd9001から自動で管理番号を取得
  $admin_sql="select kanriautodate, kanriautono from admintb";
  $adminRows=getdata($admin_sql);
  $adminArr=explode(',',$adminRows[0]);
  $autokanridate=$adminArr[0];
  $autokanrino=$adminArr[1];
  $nowdate=date('ymd');
  if($autokanridate != $nowdate){
    $autokanridate=$nowdate;
    $nextautokanrino='9001';
  }else{
    $nextautokanrino=strval(intval($autokanrino)+1);
  }
  $admin_sql="update admintb set kanriautodate='{$nowdate}',kanriautono='{$nextautokanrino}'";
  putdata($admin_sql);
  return $nowdate.$autokanrino;
}
///
$pgm = "eventlogupdeldb.php";
$user = $_GET['user'];

$eventLogRow = $_GET['fckbox'];  /// 選択されたeventlog

///----------------------------------------------------------------------------------
///  "0:host" "1:eventtime" "2:eventtype" "3:snmptype" "4:snmpvalue(NULL)"
///  "5:kanrisha(NULL)" "6:kanrimei" "7:kanrino(NULL)" "8:confclose" "9:mailsend" "10:message(NULL)"
///-----------------------------------------------------------------------------------
///--- ボタン処理 -----------
$confClose = "0";
$statType = "";
$eventStamp = date('ymdHis');
/// イベントデータ
$eventLogArr=explode(',',$eventLogRow);
$ev_host = $eventLogArr[0];
$ev_type = $eventLogArr[2];
$ev_snmpType = $eventLogArr[3];
$ev_snmpVal = $eventLogArr[4];
$ev_kanrisha = $eventLogArr[5];
$ev_kanrimei = $eventLogArr[6];
$ev_kanrino = $eventLogArr[7];
$ev_cnfCls = $eventLogArr[8];
$ev_mailsend = $eventLogArr[9];
$ev_msg = $eventLogArr[10];
///
/// ボタン処理
///
if(isset($_GET['confirm'])){ /// 「障害確認」ボタン ---> statistics gtype=5
  $statType = "5"; /// 確認
  /// あれば更新 
  $stat_sql="select gtype from statistics where host='{$ev_host}'";
  $statRows=getdata($stat_sql);
  if (!(empty($statRows))){ 
    $stat_sql="update statistics set gtype='{$statType}', status='1' where host='{$ev_host}'";
    putdata($stat_sql);
    writelogd($pgm,$stat_sql);
  }
  $eventType="3"; ///監視管理
  $confClose = "1"; ///確認
  $mailSend="0";
  $event_sql='insert into eventlog values("'.$ev_host.'","'.$eventStamp.'","3","'.$ev_snmpType.'","'.$ev_snmpVal.'","'.$ev_kanrisha.'","'.$kanrimei.'","'.$kanrino.'","1","'.$mailSend.'","'.$msg.'")';
  putdata($event_sql);
  writelogd($pgm,$event_sql);
  $host_sql='update host set result="8" where host="'.$ev_host.'"';
  putdata($host_sql); 
  writelogd($pgm,$host_sql);
}elseif (isset($_GET['close'])){  
  ///
  /// 「処置完了」ボタン ---> statistics gtype=7
  ///
  if ($ev_cnfCls!='2'){
    $msg="#error#".$user."#クローズ処理は「確認済」ログを選択して下さい";
    $nextpage="EventLogPage.php";
    branch($nextpage,$msg);
  }
  $kanrimei = $_GET['kanrimei']; /// 障害種類
  $kanrino = $_GET['kanrino']; /// 障害番号
  $memoMsg = $_GET['memomsg'];
  if (is_null($memoMsg)){
    $memoMsg = '';
  }
  ///
  /// 関連ホストのログ削除
  ///
  $event_sql="delete from eventlog where host='".$ev_host."'";
  putdata($event_sql);
  ///
  /// クローズログ作成
  ///
  $eventType="3"; ///監視管理
  $confClose = "3";  /// クローズ  
  $mailSend = "1";
  if($kanrino==''){
    $kanrino=getautokanrino();
  }  
  $event_sql='insert into eventlog values("'.$ev_host.'","'.$eventStamp.'","3","'.$ev_snmpType.'","'.$ev_snmpVal.'","'.$ev_kanrisha.'","'.$kanrimei.'","'.$kanrino.'","3","'.$mailSend.'","'.$msg.'")';
  putdata($event_sql);
  writelogd($pgm,$event_sql);
  ///
  /// statistics更新
  ///
  $statType = "9"; /// スタンバイ
  $stat_sql="select gtype from statistics where host='".$ev_host."'";
  $statRows=getdata($stat_sql);
  if (!(empty($statRows))){
    $stat_sql="update statistics set gtype='{$statType}', status='0' where host='{$ev_host}'";
    putdata($stat_sql);
    writelogd($pgm,$stat_sql);
  }
  ///
  /// イベントメモ作成
  ///　　   
  $memo_sql='insert into eventmemo values("'.$eventStamp.'","'.$ev_host.'","'.$kanrimei.'","'.$kanrino.'","'.$memoMsg.'")';
  putdata($memo_sql);
  writelogd($pgm,$memo_sql);
  /// 障害対応終了メール送信
  $hostSql="select host,groupname,ostype,result,action,viewname,mailopt,tcpport,cpulim,ramlim,disklim,process,image,snmpcomm,agenthost,eventlog,standby from host where host='".$ev_host."'";
  $hostRows=getdata($hostSql);
  $hostArr=explode(',',$hostRows[0]);
  ///
  /// メール送信
  ///
  mailsend($hostArr,$user,'9','障害処理終了',$kanrimei,$kanrino,$memoMsg);  

}elseif (isset($_GET['memo'])){ /// 「メモ保存」
  $kanrimei = $_GET['kanrimei']; /// 障害種類
  $kanrino = $_GET['kanrino']; /// 障害番号
  $memoMsg = $_GET['memomsg'];
  if (is_null($memoMsg)){
    $msg="#error#".$user."#メモ保存はメモメッセージが必須です";
    $nextpage="EventLogPage.php";
    branch($nextpage,$msg);
  }
  $confClose="5";               /// confclose=5
  $memo_sql='insert into eventmemo values("'.$eventStamp.'","'.$ev_host.'","'.$kanrimei.'","'.$kanrino.'","'.$memoMsg.'")';
  putdata($memo_sql);
}

$nextpage="EventLogPage.php";
branch($nextpage,$user);


?>
