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
if (isset($_GET['evdata']) and isset($_GET['select'])){
  $eventRow=$_GET['evdata'];
  $eventArr=explode(',',$eventRow);
  $ev_host = $eventArr[0];
  $ev_time = $eventArr[1];
  $ev_type = $eventArr[2];
  $ev_snmptype = $eventArr[3];
  $ev_snmpval = $eventArr[4];
  $ev_adminId = $eventArr[5];
  $ev_kanrimei= $enentArr[6];
  $ev_closeno = $eventArr[7];
  $ev_cnfcls = $eventArr[8];
  $ev_mlsend = $eventArr[9];
  $ev_msg = $eventArr[10];
  if (!($ev_type=='2' or ($ev_type=='3' and $ev_cnfcls=='1') or ($ev_type=3 and $ev_cnfcls=='2'))){
    $nextpage='EventLogPage.php';
      $alertMsg='#error#'.$user.'#選択可能イベントは「監視異常」か「監視管理」の確認・確認済です';
      branch($nextpage,$alertMsg);
  }
  ///
  /// gtypeで現在正常か確認
  ///
  $statSql="select gtype from statistics where host='{$ev_host}'";
  $statRows=getdata($statSql);
  $statGtype=$statRows[0];
  $eventArr[8]='2';  /// eventlogupdeldb.phpでクローズ処理をさせるため
  $eventRow=implode(',',$eventArr); /// 「確認」に修正して$eventRowをfckboxで渡す
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
$user_sql="select authority,bgcolor from user where userid='".$user."'";
$userRows=getdata($user_sql);
$userArr=explode(',',$userRows[0]);
if(empty($userRows)){
  $msg="#error#unkown#ユーザを見失いました";
  branch('logout.php',$msg);
}
$authority=$userArr[0];
$bgColor=$userArr[1];
print '<html><head><meta>';
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
print "</head><body class={$bgColor}>";
print '<h2><img src="header/php.jpg" width="30" height="30">&nbsp;&nbsp;▽　ログデータの表示と処理　▽</h2>';
print '<h4>選択したデータに下記の「必要な処理」をする</h4>';
///---- 画面表示処理 ---
print '<table border="1">';
print '<tr><th>対象ホスト</th><th>イベント種類</th><th>監視種類</th><th>snmp状態</th><th>管理者</th><th>障害種類</th><th>障害管理番号</th><th>確認</th><th>メール送信</th><th>メッセージ</th></tr>';
///--- イベントタイプ処理 
switch($ev_type){
  case '1':
    $evTypeView ="監視正常";
    $cssBgColor = "trblk";
    break;
  case '2':
    $evTypeView ="監視異常";
    $ev_snmptype = ''; 
    $cssBgColor = "trred";
    break;
  case '3':
    $evTypeView ="監視管理";
    $ev_snmptype = '';
    $cssBgColor = "trylw";
    break;
  case '4':
    $evTypeView ="対象削除";
    $cssBgColor = "trylw";
    break;
  case '5':
    $evTypeView ="新規作成";
    $cssBgColor = "trylw";
    break;
  case '6':
    $evTypeView ="内容修正";
    $cssBgColor = "trylw";
    break;
  case '7':
    $evTypeView ="監視開始";
    $cssBgColor = "trylw";
    break;
  case '0':
    $evTypeView ="ログイン";
    $cssBgColor = "trblk";
    break;
  case '9':
    $evTypeView ="ログイン";
    $cssBgColor = "trblk";
    break;
  case 'a':
    $eventTypView='DBアクセス';
    $bgColor = "trred";
    break;
  default:
    $evTypeView ="不明";
    $cssBgColor = "trred";
}

///--- snmpタイプ処理
switch($ev_snmptype){
  case '1':
    $snmpTypeView ="snmp監視";
    $snmpVal ="応答なし";
    $evTypeView="監視注意";
    
    break;
  case '2':
    $snmpTypeView ="CPU警告";
    $evTypeView="監視注意";
    $cssBgColor="trpnk";
    $snmpVal=$ev_snmpval; 
    break;
  case '3':
    $snmpTypeView ="RAM警告";
    $evTypeView="監視注意";
    $cssBgColor="trpnk";
    $snmpVal=$ev_snmpval; 
    break;
  case '4':
    $snmpTypeView ="HDD警告";
    $evTypeView="監視注意";
    $cssBgColor="trpnk";
    $snmpVal=$ev_snmpval; 
    break;
  case '5':
    $snmpTypeView ="プロセス未稼働";
    $snmpVal=$ev_snmpval; 
    break;
  case '6':
    $snmpTypeView ="ポート閉鎖";
    $snmpVal=$ev_snmpval; 
    break;
  case '7':
    $snmpTypeView ="クローズ待ち";
    $cnfClsView = "";
    if ($ev_cnfcls == "1"){
      $cnfClsView = "確認";
    }elseif($ev_cnfcls == '2'){
      $cnfClsView = "確認済";
    }
    $mlSendView= "";
    if ($ev_mlsend == "1"){
      $mlSendView= "送信済";
    }
    break;
  case 'P':
    $snmpTypeView ="Ping";
    break;
  case 'N':
    $snmpTypeView ="Ncat";
    break;
  
}              

///--- 報告者、障害番号処理
if (empty($ev_adminId)){
  $adminName = 'admin';
}else{
  $adminName = $ev_adminId;
}
if (empty($ev_closeno)){  
  $clsNo = '';
}else{
  $clsNo = $ev_closeno;
}
/// 確認、未確認処理
switch($ev_cnfcls){
  case '1':
    $cnfClsView ="確認";
    break;
  case '2':
    $cnfClsView ="確認済";
    break;
  case '3':
    $cnfClsView ="クローズ";
    break;
}

/// メール送信、未送信処理 
if ($ev_mlsend=='0'){
  $mlSendView="";
}elseif ($ev_mlsend=='1'){
  $mlSendView="送信済";
}else{
  $mlSendView= "";
}  
/// メッセージ処理
if (empty($ev_msg)){
  $memoMsg = '';
}else{
  $memoMsg = $ev_msg; 
}
print '<tr>';
print "<td class={$cssBgColor}>{$ev_host}</td>";
print "<td class={$cssBgColor}>{$evTypeView}</td>";
print "<td class={$cssBgColor}>{$snmpTypeView}</td>";
print "<td class={$cssBgColor}>{$snmpVal}</td>";
print "<td class={$cssBgColor}>{$adminName}</td>";
print "<td class={$cssBgColor}>{$kanrimei}</td>";
print "<td class={$cssBgColor}>{$clsNo}</td>";
print "<td class={$cssBgColor}>{$cnfClsView}</td>";
print "<td class={$cssBgColor}>{$mlSendView}</td>";
print "<td class={$cssBgColor}>{$memoMsg}</td>";
print '</tr>';
print '</table>';

if ($auth=='1'){
  print '<h3>　処理と操作<br>';
  print '　';
  print '　　●障害確認（コンファーム）　障害発生を確認する<br>';
  print '　　　　処理：<br>';
  print '　　　　　イベントログに確認を表示<br>';
  print '　　　　操作：<br>';
  print '　　　　　<span class=trblk>「障害確認」</span>をクリック><br>';  
  print '　　●メモ保存　　　　　　　　障害解決の前にメモを残す<br>';
  print '　　　　処理：<br>';
  print '　　　　　障害種類、障害管理番号、メモメッセージをデータベースへ保存<br>';
  print '　　　　操作：<br>';
  print '　　　　　障害種類、障害管理番号、メモメッセージを入力し、<span class=trblk>「メモ保存」</span>をクリック<br>';
  print '　　　　注意：<br>';
  print '　　　　　<span class=trred> 「メモ保存」は、「障害解決」の前に実行すること</span><br>';
  print '　　　　　<span class=trred> 障害管理番号の自動採番は行いません、必要なら手動で入力すること</span><br>';
  print '　　●障害解決（クローズ）　　　障害処置を完了する<br>';
  print '　　　　処理：<br>';
  print '　　　　　対象ホストイベント全データを削除、障害種類、障害管理番号、メモメッセージを障害情報として保存<br>';
  print '　　　　操作：<br>';
  print '　　　　　障害種類、障害管理番号、メモメッセージを入力し、<span class=trblk>「処置完了」</span>をクリック<br>';
  print '　　　　注意：<br>';
  print '　　　　　<span class=trred> 正常復帰は「確認確認」をせず、「障害解決」のみ実行すること</span><br>';
  print '</h3>';
  print '<form name="logdbupform" method="get" action="eventlogupdeldb.php">';
  print "<input type='hidden' name='fckbox' value={$eventRow} />";
  print "<input type='hidden' name='user' value={$user} />";
  print '<hr>';
  ///
  /// 現在正常復帰なら、確認処理をバイパス
  ///
  if($statGtype!='3'){
    print '「障害確認」<br>';
    print '&emsp;<font size=3><input class="trblk" type="submit" name="confirm" value="　障害確認　"></font>';
    print '<hr>';
  }
  print '「メモ保存」または「処置完了」<br>';
  print '&emsp;障害種類：<input type="text" name="kanrimei" size="8" maxlength="8" value="" placeholder="例：無応答"/>';
  print "&emsp;障害管理番号：<input type='text' name='kanrino' size='12' maxlength='12' placeholder='例：2310260001'/>";
  print '（障害管理番号が空白の場合、「処置完了」のみyymmdd9xxxの番号を自動採番する）<br>';
  print '&emsp;メモメッセージ：<br>';
  print '&emsp;<textarea name="memomsg" maxlength="200" placeholder="半角200、全角100文字以内、改行可能" cols="101"></textarea><br>';
  print '&emsp;<font size=3><input class="trblk" type="submit" name="memo" value="　メモ保存　" ></font>';
  print '&emsp;<font size=3><input class="trblk" type="submit" name="close" value="　処置完了　"></font>';
  print '<hr>';
  print '</form>';
}
print '<br>';
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>"; 
print '</body></html>';
?>

