<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

function arraycheck($data){
  $dataarr=array();
  if (is_array($data)){
    $dataarr=$data;
  }else{
    $dataarr[0]=$data;
  }
  return $dataarr;
}
$pgm = "vieweventlog.php";
$user=$_GET['user'];
$auth=$_GET['authcd'];
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
    $msg='#error#'.$user.'#データを選択して下さい';
    branch($nextpage,$msg);
  }
}
$ev_host = "";
$ev_time = "";
$ev_type = "";
$ev_snmptype = "";
$ev_snmpval = "";
$ev_kanri = "";
$ev_kanrino = "";
$ev_cnfcls = "";
$ev_mlsend = "";
$ev_msg = "";
if (isset($_GET['evdata'])){
  $fckrec=$_GET['evdata'];
  $sdata=explode(',',$fckrec);
  $ev_host = $sdata[0];
  $ev_time = $sdata[1];
  $ev_type = $sdata[2]; /// 3
  $ev_snmptype = $sdata[3]; /// 7
  $ev_snmpval = $sdata[4]; /// 7
  $ev_kanri = $sdata[5];
  $ev_kanrino = $sdata[6];
  $ev_cnfcls = $sdata[7];  /// 2
  $ev_mlsend = $sdata[8];  /// 0
  $ev_msg = $sdata[9];
  //var_dump($_GET['evdata']);
}
if (isset($_GET['delete'])){
  $delsql='delete from eventlog where host="'.$ev_host.'" and eventtime="'.$ev_time.'"';
  putdata($delsql);
  $nextpage='EventLogPage.php';
  $msg='#notic#'.$user.'#データが正常に選択削除されました';
  branch($nextpage,$msg);
  /// 範囲指定削除処理
}elseif(isset($_GET['rangedel'])){
  if (isset($_GET['fromtime']) and isset($_GET['totime'])){
    $patt='/^[0-9]{2}-[0-9]{2}-[0-9]{2}$/';
    if (preg_match($patt,$_GET['fromtime']) && preg_match($patt,$_GET['totime'])){
      $fromval=str_replace('-','',$_GET['fromtime']).'000000'; 
      $toval=str_replace('-','',$_GET['totime']).'999999';
      $delsql='delete from eventlog where eventtime between "'.$fromval.'" and "'.$toval.'"';
      putdata($delsql);
      $nextpage='EventLogPage.php';
      $msg='#notic#'.$user.'#データが正常に範囲削除されました';
      writeloge($pgm,$msg.' '.$delsql); 
      branch($nextpage,$msg);
    }else{
      $nextpage='EventLogPage.php';
      $msg='#error#'.$user.'#指定した範囲が不正です';
      branch($nextpage,$msg);
    }
  }else{
    $nextpage='EventLogPage.php';
    $msg='#error#'.$user.'#指定した範囲が不正です';
    branch($nextpage,$msg);    
  }
}
print '<html><head><meta>';
print '<link rel="stylesheet" href="kanshi1_py.css">';
print '</head><body>';
print '<h2><img src="header/php.jpg" width="30" height="30">&nbsp;&nbsp;▽　ログデータの表示と処理　▽</h2>';
print '<h4>選択したデータに下記の「必要な処理」をします</h4>';
///---- 画面表示処理 ---
print '<table border="1">';
print '<tr><th>対象ホスト</th><th>イベント種類</th><th>snmp結果</th><th>snmp状態</th><th>管理者</th><th>障害管理番号</th><th>確認</th><th>メール送信</th><th>メッセージ</th></tr>';
$host=$ev_host;
  ///--- イベントタイプ処理 
  if ($ev_type=='1'){
    $ctype ="監視正常";
    $triro = "trblk";
  }elseif ($ev_type=='2'){
    $ctype ="監視異常";
    $triro = "trred";
  }elseif ($ev_type=='3'){
    $ctype ="監視管理";
    $triro = "trylw";
  }elseif ($ev_type=='4'){
    $ctype ="対象削除";
    $triro = "trylw";
  }elseif ($ev_type=='5'){
    $ctype ="新規作成";
    $triro = "trylw";
  }elseif ($ev_type=='6'){
    $ctype ="内容修正";
    $triro = "trylw";
  }elseif ($ev_type=='7'){
    $ctype ="監視開始";
    $triro = "trylw";
  }elseif ($ev_type=='0'){
    $ctype ="Login/Out";
    $triro = "trblk";
  }else{
    $ctype ="不明";
    $triro = "trred";
  }
  ///--- snmpタイプ処理
  if ($ev_snmptype=='2'){
    $stype ="CPU警告";
    $ctype="監視注意";
    $triro="trpnk";
  }elseif ($ev_snmptype=='3'){
    $stype ="RAM警告";
    $ctype="監視注意";
    $triro="trpnk";
  }elseif ($ev_snmptype=='4'){
    $stype ="HDD警告";
    $ctype="監視注意";
    $triro="trpnk";
  }elseif ($ev_snmptype=='5'){
    $stype ="Process不在";    
  }elseif ($ev_snmptype=='6'){
    $stype ="PORT閉鎖";
  }elseif ($ev_snmptype=='7'){
    $stype ="";
    $cc = "";
    if ($ev_cnfcls == "2"){
      $cc = "確認済";
    }
    
    if ($ev_mlsend == "1"){
      $ms = "送信済";
    }else{
      $ms = "未送信";
    }
  }else{
    $stype ='';
  }
  ///--- snmp測定値処理
  if (is_null($ev_snmpval) or $ev_snmpval=='' or $ev_snmptype=='7'){
    $okspp='';
  }else{
    $snmpval=$ev_snmpval; 
    $okspp=$snmpval;
  }
  ///--- 報告者、障害番号処理
  if (is_null($ev_kanri) or $ev_kanri==''){
    $kmei = '21001';
  }else{
    $kmei = $ev_kanri;
  }
  if (is_null($ev_kanrino) or $ev_kanrino==''){
    /*
    $rdsql="select * from admintb";
    $rows=getdata($rdsql);
    $admindata=$rows[0];
    $rtnkno=$admindata[10]; // admintbの管理者 
    $kno=date('ymd').sprintf('%04d',$rtnkno);
    $upkno=intval($rtnkno)+1;
    $svkno=sprintf('%04d',$upkno);
    $upsql='update admintb set kanrino='.$svkno;
    putdata($upsql);
    */
    $kno = '';
  }else{
    $kno = $ev_kanrino;
  }
  /// 確認、未確認処理
  if ($ev_cnfcls=='1'){ 
    $cc ="確認";
  }elseif ($ev_cnfcls=='2'){ 
    $cc ="確認済";
  }elseif ($ev_cnfcls=='3'){ 
    $cc ="クローズ";
  }else{
    $cc= "未確認";
  }  
  /// メール送信、未送信処理 
  if ($ev_mlsend=='0'){
    $ms ="未送信";
  }elseif ($ev_mlsend=='1'){
    $ms ="送信済";
  }else{
    $ms = "不明";
  }  
  /// メッセージ処理
  if (is_null($ev_msg) || $ev_msg==''){
    $msg = 'None';
  }else{
    $msg = $ev_msg; 
  }
  print '<tr>';
  print "<td class={$triro}>{$host}</td>";
  print "<td class={$triro}>{$ctype}</td>";
  print "<td class={$triro}>{$stype}</td>";
  print "<td class={$triro}>{$okspp}</td>";
  print "<td class={$triro}>{$kmei}</td>";
  print "<td class={$triro}>{$kno}</td>";
  print "<td class={$triro}>{$cc}</td>";
  print "<td class={$triro}>{$ms}</td>";
  print "<td class={$triro}>{$msg}</td>";
  print '</tr>';
  
 // enf of for
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
  print "<input type='hidden' name='fckbox' value={$fckrec} />";
  print "<input type='hidden' name='user' value={$user} />";
  print '<hr>';
  print '<h4>☆　障害確認は、<span class=trylw>「障害確認」〇</span>を選択し、<span class=trblk>「実行」</span>をクリックします</h4>';  
  print '<hr>';
  print '<h4>☆　障害解決は、障害種類、障害管理番号、メモメッセージを入力し、<span class=trylw>「処置完了」〇</span>を選択し、<span class=trblk>「実行」</span>をクリックします</h4>';
  print '<h4>☆　メモを残したい場合は、障害種類、障害管理番号、メモメッセージを入力し、<span class=trylw>「メモ保存」〇</span>を選択し、<span class=trblk>「実行」</span>をクリックします</h4>';
  print "&emsp;障害種類：<input type='text' name='kanrimei' size='8' maxlength='8' placeholder='例：無応答'/>";
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

