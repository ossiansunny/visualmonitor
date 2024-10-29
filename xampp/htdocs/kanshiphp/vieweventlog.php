<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

function arraycheck($_data){
  $rtnDataArr=array();
  if (is_array($_data)){
    $rtnDataArr=$_data;
  }else{
    $rtnDataArr[0]=$_data;
  }
  return $rtnDataArr;
}
$pgm = "vieweventlog.php";
$user=$_GET['user'];
$auth=$_GET['authcd'];
$alertMsg="";
///-----------------------------------------------------------
///---- fckbox delete,fromtime,totime select
///-----------------------------------------------------------
///-----eventlog ---------------------------------------------
///---- "0:host" "1:eventtime" "2:eventtype" "3:snmptype" 
///---  "4:snmpvalue(NULL)" "5:kanrisha(NULL)" "6:kanrino(NULL)"  
///---  "7:confclose" "8:mailsend" "9:message(NULL)"
///-----------------------------------------------------------
if (! isset($_GET['evdata'])){
  if (! isset($_GET['rangedel'])){
    $nextpage='EventLogPage.php';
    $alertMsg='#error#'.$user.'#データを選択して下さい';
    branch($nextpage,$alertMsg);
  }
}
$ev_host = "";
$ev_time = "";
$ev_type = "";
$ev_snmptype = "";
$ev_snmpval = "";
$ev_adminId = "";
$ev_closeno = "";
$ev_cnfcls = "";
$ev_mlsend = "";
$ev_msg = "";
if (isset($_GET['evdata'])){
  $eventRow=$_GET['evdata'];
  $eventArr=explode(',',$eventRow);
  $ev_host = $eventArr[0];
  $ev_time = $eventArr[1];
  $ev_type = $eventArr[2]; /// 3
  $ev_snmptype = $eventArr[3]; /// 7
  $ev_snmpval = $eventArr[4]; /// 7
  $ev_adminId = $eventArr[5];
  $ev_closeno = $eventArr[6];
  $ev_cnfcls = $eventArr[7];  /// 2
  $ev_mlsend = $eventArr[8];  /// 0
  $ev_msg = $eventArr[9];
  
}
if (isset($_GET['delete'])){
  $event_sql='delete from eventlog where host="'.$ev_host.'" and eventtime="'.$ev_time.'"';
  putdata($event_sql);
  $nextpage='EventLogPage.php';
  $alertMsg='#notic#'.$user.'#データが正常に選択削除されました';
  branch($nextpage,$alertMsg);
  /// 範囲指定削除処理
}elseif(isset($_GET['rangedel'])){
  if (isset($_GET['fromtime']) and isset($_GET['totime'])){
    $patt='/^[0-9]{2}-[0-9]{2}-[0-9]{2}$/';
    if (preg_match($patt,$_GET['fromtime']) && preg_match($patt,$_GET['totime'])){
      $fromVal=str_replace('-','',$_GET['fromtime']).'000000'; 
      $toVal=str_replace('-','',$_GET['totime']).'999999';
      $event_sql='delete from eventlog where eventtime between "'.$fromVal.'" and "'.$toVal.'"';
      putdata($event_sql);
      $nextpage='EventLogPage.php';
      $alertMsg='#notic#'.$user.'#データが正常に範囲削除されました';
      writelogd($pgm,$alertMsg.' '.$delsql); 
      branch($nextpage,$alertMsg);
    }else{
      $nextpage='EventLogPage.php';
      $alertMsg='#error#'.$user.'#指定した範囲が不正です';
      branch($nextpage,$alertMsg);
    }
  }else{
    $nextpage='EventLogPage.php';
    $alertMsg='#error#'.$user.'#指定した範囲が不正です';
    branch($nextpage,$alertMsg);    
  }
}
print '<html><head><meta>';
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
print '</head><body>';
print '<h2><img src="header/php.jpg" width="30" height="30">&nbsp;&nbsp;▽　ログデータの表示と処理　▽</h2>';
print '<h4>選択したデータに下記の「必要な処理」をします</h4>';
///---- 画面表示処理 ---
print '<table border="1">';
print '<tr><th>対象ホスト</th><th>イベント種類</th><th>snmp結果</th><th>snmp状態</th><th>管理者</th><th>障害管理番号</th><th>確認</th><th>メール送信</th><th>メッセージ</th></tr>';
///--- イベントタイプ処理 
if ($ev_type=='1'){
  $evTypeView ="監視正常";
  $cssBgColor = "trblk";
}elseif ($ev_type=='2'){
  $evTypeView ="監視異常";
  $cssBgColor = "trred";
}elseif ($ev_type=='3'){
  $evTypeView ="監視管理";
  $cssBgColor = "trylw";
}elseif ($ev_type=='4'){
  $evTypeView ="対象削除";
  $cssBgColor = "trylw";
}elseif ($ev_type=='5'){
  $evTypeView ="新規作成";
  $cssBgColor = "trylw";
}elseif ($ev_type=='6'){
  $evTypeView ="内容修正";
  $cssBgColor = "trylw";
}elseif ($ev_type=='7'){
  $evTypeView ="監視開始";
  $cssBgColor = "trylw";
}elseif ($ev_type=='0'){
  $evTypeView ="Login/Out";
  $cssBgColor = "trblk";
}else{
  $evTypeView ="不明";
  $cssBgColor = "trred";
}
///--- snmpタイプ処理
if ($ev_snmptype=='2'){
  $snmpTypeView ="CPU警告";
  $evTypeView="監視注意";
  $cssBgColor="trpnk";
}elseif ($ev_snmptype=='3'){
  $snmpTypeView ="RAM警告";
  $evTypeView="監視注意";
  $cssBgColor="trpnk";
}elseif ($ev_snmptype=='4'){
  $snmpTypeView ="HDD警告";
  $evTypeView="監視注意";
  $cssBgColor="trpnk";
}elseif ($ev_snmptype=='5'){
  $snmpTypeView ="Process不在";    
}elseif ($ev_snmptype=='6'){
  $snmpTypeView ="PORT閉鎖";
}elseif ($ev_snmptype=='7'){
  $snmpTypeView ="";
  $cnfClsView = "";
  if ($ev_cnfcls == "2"){
    $cnfClsView = "確認済";
  }
    
  if ($ev_mlsend == "1"){
    $mlSendView= "送信済";
  }else{
    $mlSendView= "未送信";
  }
}else{
  $snmpTypeView ='';
}
///--- snmp測定値処理
if (is_null($ev_snmpval) or $ev_snmpval=='' or $ev_snmptype=='7'){
  $snmpVal='';
}else{
  $snmpVal=$ev_snmpval; 
  
}
///--- 報告者、障害番号処理
if (is_null($ev_adminId) or $ev_adminId==''){
  $adminName = 'admin';
}else{
  $adminName = $ev_adminId;
}
if (is_null($ev_closeno) or $ev_closeno==''){
  
  $clsNo = '';
}else{
  $clsNo = $ev_closeno;
}
/// 確認、未確認処理
if ($ev_cnfcls=='1'){ 
  $cnfClsView ="確認";
}elseif ($ev_cnfcls=='2'){ 
  $cnfClsView ="確認済";
}elseif ($ev_cnfcls=='3'){ 
  $cnfClsView ="クローズ";
}else{
  $cnfClsView= "未確認";
}  
/// メール送信、未送信処理 
if ($ev_mlsend=='0'){
  $mlSendView="未送信";
}elseif ($ev_mlsend=='1'){
  $mlSendView="送信済";
}else{
  $mlSendView= "不明";
}  
/// メッセージ処理
if (is_null($ev_msg) || $ev_msg==''){
  $memoMsg = 'None';
}else{
  $memoMsg = $ev_msg; 
}
print '<tr>';
print "<td class={$cssBgColor}>{$ev_host}</td>";
print "<td class={$cssBgColor}>{$evTypeView}</td>";
print "<td class={$cssBgColor}>{$snmpTypeView}</td>";
print "<td class={$cssBgColor}>{$snmpVal}</td>";
print "<td class={$cssBgColor}>{$adminName}</td>";
print "<td class={$cssBgColor}>{$clsNo}</td>";
print "<td class={$cssBgColor}>{$cnfClsView}</td>";
print "<td class={$cssBgColor}>{$mlSendView}</td>";
print "<td class={$cssBgColor}>{$memoMsg}</td>";
print '</tr>';
print '</table>';

if ($auth=='1'){
  print '<h4>　必要な処理<br>';
  print '　注：障害確認、障害解決は、対象ホスト単位で行われます<br>';
  print '　　●障害確認（コンファーム）　障害発生を確認したときの処理<br>';
  print '　　　イベントログに確認を表示<br>';
  print '　　●障害解決（クローズ）　　　障害処置を完了したときの処理<br>';
  print '　　　対象ホストイベント全データを削除、メモに障害情報を保存<br>';
  print '　　●メモを保存　　　　　　　　メモを残すときの処理<br>';
  print '　　　任意の情報をメモに保存';
  print '</h4>';

  print '<form name="logdbupform" method="get" action="eventlogupdeldb.php">';
  print "<input type='hidden' name='fckbox' value={$eventRow} />";
  print "<input type='hidden' name='user' value={$user} />";
  print '<hr>';
  print '<h4>☆　障害確認は、<span class=trylw>「障害確認」〇</span>を選択し、<span class=trblk>「実行」</span>をクリックします</h4>';  
  print '<hr>';
  print '<h4>☆　障害解決は、障害種類、障害管理番号、メモメッセージを入力し、<span class=trylw>「処置完了」〇</span>を選択し、<span class=trblk>「実行」</span>をクリックします</h4>';
  print '<h4>☆　メモを残したい場合は、障害種類、障害管理番号、メモメッセージを入力し、<span class=trylw>「メモ保存」〇</span>を選択し、<span class=trblk>「実行」</span>をクリックします</h4>';
  print '&emsp;障害種類：<input type="text" name="kanrimei" size="8" maxlength="8" value="" placeholder="例：無応答"/>';
  print "&emsp;障害管理番号：<input type='text' name='kanrino' size='12' maxlength='12' placeholder='例：2310260001'/><br>";
  print '&emsp;メモメッセージ：<br>';
  print '&emsp;<textarea name="memomsg" maxlength="200" placeholder="半角200、全角100文字以内、改行可能" cols="101"></textarea><br>';
  print '<hr>';
  print '<h4>☆　処置選択</h4>';
  print '&emsp;<span class=trylw>障害確認</span><input type="radio" name="cradio" value="confirm" />';
  print '&emsp;<span class=trylw>処置完了</span><input type="radio" name="cradio" value="close" />';
  print '&emsp;<span class=trylw>メモ保存</span><input type="radio" name="cradio" value="memo" /> ';
  print '&emsp;&emsp;<input class=button type="submit" name="go" value="実行" />';
  print '</form>';
}
print '<br>';
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>"; 
print '</body></html>';
?>

