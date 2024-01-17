<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "mailsendevent.php";

print '<html><head><meta>';
print '<link rel="stylesheet" href="kanshi1.css">';
print '</head><body>';
$pgm = "eventlogupdeldb.php";
$user = $_GET['user'];
if (!isset($_GET['cradio'])){
  $msg='checkbox unknown data found:'.$ck_radio;
  writeloge($pgm,$msg);
  $msg="#error#".$user."#チェックボックスにチェックをして下さい";
  $nextpage='EventLogPage.php';
  branch($nextpage,$msg);
}
$fckbox = $_GET['fckbox'];  /// 選択されたeventlog
$ck_radio = $_GET['cradio'];    /// 選択されたボタン
$kanrimei = $_GET['kanrimei']; /// 障害種類
$kanrino = $_GET['kanrino']; /// 障害番号
$memomsg = $_GET['memomsg'];
if (is_null($memomsg)){
  $memomsg = '';
}
///----------------------------------------------------------------------------------
///  "0:host" "1:eventtime" "2:eventtype" "3:snmptype" "4:snmpvalue(NULL)"
///  "5:kanrisha(NULL)" "6:kanrino(NULL)" "7:confclose" "8:mailsend" "9:message(NULL)"
///-----------------------------------------------------------------------------------
///--- checkbox ボタン処理 -----------
$noupdatesw = "0";
$mailsend = "0";
$close = "0";
$confclose = "0";
$memosw = "0";
$gtype = "";
$evtime = date('ymdHis');
/// イベントデータ
$sdata=explode(',',$fckbox);
$ev_host = $sdata[0];  // host
$ev_evtime = $sdata[1]; // eventtime
$ev_evtype = $sdata[2];
$ev_stype = $sdata[3];
$ev_svalue = $sdata[4];
$ev_ksha = $sdata[5];
$ev_kno = $sdata[6];
$ev_cfcs = $sdata[7];
$ev_msend = $sdata[8];
$ev_msg = $sdata[9];
/// ラヂオボタン処理
if ($ck_radio=='confirm'){     // 「障害確認」ボタン ---> statistics gtype=5 
  $confclose = "1";          // confclose=1         
  $gtype = "5"; /// 確認
  /// あれば更新 
  $gsql="select gtype from statistics where host='".$ev_host."'";
  $gdata=getdata($gsql);
  if (isset($gdata)){ 
    $usql='update statistics set gtype="'.$gtype.'" where host="'.$ev_host.'"';
    putdata($usql);
    writelogd($pgm,$usql);
  }
  $evtype="3"; ///監視管理
  $cfcs = "1";  ///確認
  $msend="0";
  $inssql='insert into eventlog values("'.$ev_host.'","'.$evtime.'","'.$evtype.'","'.$ev_stype.'","'.$ev_svalue.'","'.$kanrimei.'","'.$kanrino.'","'.$cfcs.'","'.$msend.'","'.$msg.'")';
  putdata($inssql);
  writeloge($pgm,$inssql);
  $usql='update host set result="8" where host="'.$ev_host.'"';
  putdata($usql); 
  writelogd($pgm,$usql);
}elseif ($ck_radio=='close'){
  /// 「処置完了」ボタン ---> statistics gtype=7
  if ($ev_cfcs!='2'){
    $msg="#error#".$user."#クローズ処理は「確認済」ログを選択して下さい";
    $nextpage="EventLogPage.php";
    branch($nextpage,$msg);
  }
  $confclose = "2";          // confclose=2
  mailsendevent($fckbox,$kanrimei,$kanrino,$confclose,$memomsg);
  $dsql="delete from eventlog where host='".$ev_host."'";
  putdata($dsql);
  $evtype="3"; ///監視管理
  $cfcs = "3";  /// クローズ  
  $msend = "1";
  $inssql='insert into eventlog values("'.$ev_host.'","'.$evtime.'","'.$evtype.'","'.$stype.'","'.$svalue.'","'.$user.'","'.$kanrino.'","'.$cfcs.'","'.$msend.'","'.$msg.'")';
  putdata($inssql);
  writelogd($pgm,$inssql);
  // あれば更新
  //$gtype = "9"; /// スタンバイ
  $gsql="select gtype from statistics where host='".$ev_host."'";
  $gdata=getdata($gsql);
  if (isset($gdata)){
    $delsql="delete from statistics where host='".$ev_host."'";
    putdata($delsql);
    $insql='insert into statistics (host,tstamp,gtype) values("'.$ev_host.'","000000000000","9")';
    putdata($insql);
    writelogd($pgm,$usql);
  }   
  $inssql='insert into eventmemo values("'.$evtime.'","'.$ev_host.'","'.$kanrimei.'","'.$kanrino.'","'.$memomsg.'")';
  putdata($inssql);
  writeloge($pgm,$usql);
}elseif ($ck_radio=='logdel'){ // 「ログ削除」ボタン
  $confclose = "4";          // confclose=4
  /// gtype=7あれば削除 
  $gsql="select gtype from statistics where host='".$ev_host."'";
  $gdata=getdata($gsql);
  if (isset($gdata)){
    $delsql = "delete from eventlog where host='".$ev_host."'";
    putdata($delsql);
  }  
}elseif ($ck_radio=='memo'){   // 「メモ保存」ボタン
  $confclose="5";            // confclose=5
  $inssql='insert into eventmemo values("'.$evtime.'","'.$ev_host.'","'.$kanrimei.'","'.$kanrino.'","'.$memomsg.'")';
  putdata($inssql);
}
/// 確認済、クローズ済のみ削除
  /// 削除処理
    

    ///---------------------------------------------------
    /// 更新処理 正常データ以外を更新
    ///--- メール送信 ------------------------------------
    
    
    



$nextpage="EventLogPage.php";
branch($nextpage,$user);

//echo '<a href="MonitorManager.php">監視モニターへ戻る</a>';
?>
