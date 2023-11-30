<?php
require_once "mysqlkanshi.php";

function branch($_page,$_param){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo "<form name='F' action={$_page} method='get'>";
  echo "<input type=hidden name=param value={$_param}>";
  echo '<input type="submit" name="next" value="Waiting...">';
  echo '</form>';
}

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
$userid=$_GET['userid'];
$auth=$_GET['authcd'];
///-----------------------------------------------------------
///---- fckbox delete,fromtime,totime select
///-----------------------------------------------------------
///-----eventlog ---------------------------------------------
///---- "0:host" "1:eventtime" "2:eventtype" "3:snmptype" 
///---  "4:snmpvalue(NULL)" "5:kanrisha(NULL)" "6:kanrino(NULL)"  
///---  "7:confclose" "8:mailsend" "9:message(NULL)"
///-----------------------------------------------------------
if (isset($_GET['delete'])){
  /// delete 処理
  if (isset($_GET['ckdata'])){
    $ffckbox=$_GET['ckdata'];
    $fckbox=arraycheck($ffckbox);
    foreach ($fckbox as $fckrec){
      $sdata=explode(',',$fckrec);
      $delsql='delete from eventlog where host="'.$sdata[0].'" and eventtime="'.$sdata[1].'"';
      putdata($delsql);
    }
    $nextpage='EventLogPage.php';
    branch($nextpage,$userid);
    echo '</body></html>';
  /// 範囲指定削除処理
  }elseif (isset($_GET['fromtime']) && isset($_GET['totime'])){
    $patt='/^[0-9]{2}-[0-9]{2}-[0-9]{2}$/';
    if (preg_match($patt,$_GET['fromtime']) && preg_match($patt,$_GET['totime'])){
      $fromval=str_replace('-','',$_GET['fromtime']).'000000'; 
      $toval=str_replace('-','',$_GET['totime']).'235959';
      $delsql='delete from eventlog where eventtime between '.$fromval.' and '.$toval;
      putdata($delsql);
      $nextpage='EventLogPage.php';
      branch($nextpage,$userid);
      echo '</body></html>';
    }else{
      echo "<a href='EventLogPage.php?param={$userid}'>削除範囲が不正です</a>";
      exit();
    }
  }else{
    echo "<a href='EventLogPage.php?param={$userid}'>クリックして、ホストを選択して下さい</a>";
    exit();
  }
}

echo '<html><head><meta>';
echo '<link rel="stylesheet" href="kanshi1.css">';
echo '</head><body>';
echo '<h2><img src="header/php.jpg" width="30" height="30">&nbsp;&nbsp;▽　ログデータの表示と処理　▽</h2>';
echo '<h4>選択したデータ全てに下記の「必要な処理」をします</h4>';

if (! isset($_GET['ckdata'])){
  /// 選択なしの処理
  echo "<a href='EventLogPage.php?=param={$userid}'>クリックして、ホストを選択して下さい</a>";
  exit();
}else{
  /// 選択ありの処理
  $ffckbox=$_GET['ckdata'];
  $fckbox=arraycheck($ffckbox);
}

///---- 選択処理 ---
echo '<table border="1">';
echo '<tr><th>対象ホスト</th><th>イベント種類</th><th>snmp結果</th><th>snmp状態</th><th>管理者</th><th>管理者番号</th><th>確認</th><th>メール送信</th><th>メッセージ</th></tr>';
foreach ($fckbox as $fckrec){
  $sdata=explode(',',$fckrec);
  $host = $sdata[0];
  $evtime = $sdata[1];
  $evtype = $sdata[2];
  $snmptype = $sdata[3];
  ///--- イベントタイプ処理 
  if ($evtype=='1'){
    $ctype ="監視正常";
    $triro = "trblk";
  }elseif ($evtype=='2'){
    $ctype ="監視異常";
    $triro = "trred";
  }elseif ($evtype=='4'){
    $ctype ="対象削除";
    $triro = "trylw";
  }elseif ($evtype=='5'){
    $ctype ="新規作成";
    $triro = "trylw";
  }elseif ($evtype=='6'){
    $ctype ="内容修正";
    $triro = "trylw";
  }elseif ($evtype=='7'){
    $ctype ="監視開始";
    $triro = "trylw";
  }elseif ($evtype=='0'){
    $ctype ="Login/Out";
    $triro = "trblk";
  }else{
    $ctype ="不明";
    $triro = "trred";
  }
  ///--- snmpタイプ処理
  if ($snmptype=='2'){
    $stype ="CPU警告";
  }elseif ($snmptype=='3'){
    $stype ="RAM警告";
  }elseif ($snmptype=='4'){
    $stype ="HDD警告";
  }elseif ($snmptype=='5'){
    $stype ="Process警告";
  }elseif ($snmptype=='6'){
    $stype ="PORT警告";
  }else{
    $stype ='';
  }
  ///--- snmp測定値処理
  if (is_null($sdata[4]) || $sdata[4]==''){
    $okspp='';
  }else{
    $snmpval=$sdata[4]; 
    $okspp=$snmpval;
  }
  ///--- 報告者、障害番号処理
  if (is_null($sdata[5]) || $sdata[5]==''){
    $kmei = '21001';
  }else{
    $kmei = $sdata[5];
  }
  if (is_null($sdata[6]) || $sdata[6]==''){
    $rdsql="select * from admintb";
    $rows=getdata($rdsql);
    $adata=$rows[0];
    $rtnkno=$adata[10]; 
    $kno=date('ymd').sprintf('%04d',$rtnkno);
    $upkno=intval($rtnkno)+1;
    $svkno=sprintf('%04d',$upkno);
    $upsql='update admintb set kanrino='.$svkno;
    putdata($upsql);
  }else{
    $kno = $sdata[6];
  }
  /// 確認、未確認処理
  $concl=$sdata[7];
  if ($concl=='1'){ 
    $cc ="確認済";
  }elseif ($concl=='2'){ 
    $cc ="確認？";
  }elseif ($concl=='3'){ 
    $cc ="クローズ";
  }else{
    $cc= "未確認";
  }  
  /// メール送信、未送信処理 
  $mlsend=$sdata[8];
  if ($mlsend=='0'){
    $ms ="未送信";
  }elseif ($mlsend=='1'){
    $ms ="送信済";
  }else{
    $ms = "不明";
  }  
  /// メッセージ処理
  if (is_null($sdata[9]) || $sdata[9]==''){
    $msg = 'None';
  }else{
    $msg = $sdata[9]; 
  }
  echo '<tr>';
  echo "<td class={$triro}>{$host}</td>";
  echo "<td class={$triro}>{$ctype}</td>";
  echo "<td class={$triro}>{$stype}</td>";
  echo "<td class={$triro}>{$okspp}</td>";
  echo "<td class={$triro}>{$kmei}</td>";
  echo "<td class={$triro}>{$kno}</td>";
  echo "<td class={$triro}>{$cc}</td>";
  echo "<td class={$triro}>{$ms}</td>";
  echo "<td class={$triro}>{$msg}</td>";
  echo '</tr>';
  
} // enf of for
echo '</table>';

if ($auth=='1'){
  echo '<h4>　必要な処理<br>';

  echo '　　□障害種類(5桁)、障害番号(10桁)を付与したい場合に入力する<br>';
  echo '　　●障害を確認：&emsp;&emsp;&emsp;&emsp;&emsp;「障害確認」、「実行」<br>';
  echo '　　●障害をクローズ：&emsp;&emsp;&emsp;障害種類、障害番号、メモメッセージ入力、「処置完了」、「実行」<br>';
  echo '　　●メールを送信：&emsp;&emsp;&emsp;&emsp;障害種類、障害番号、メッセージ入力、「Mail送信」、「実行」<br>';
  echo '　　●ログを削除：&emsp;&emsp;&emsp;&emsp;&emsp;「処置完了」後、「ログ削除」、「実行」<br>';
  echo '　　●メモを保存：&emsp;&emsp;&emsp;&emsp;&emsp;障害種類、障害番号、メモメッセージ入力、「メモ保存」、「実行」<br>';
  echo '　　複数チェック可能<br>';
  echo '</h4>';

  echo '<form name="logdbupform" method="get" action="eventlogupdeldb.php">';
  foreach ($fckbox as $data){ //
    echo "<input type='hidden' name='fckbox[]' value={$data} />"; 
  }
  $skind="例：無応答";
  $snum="例：2310260001";
  echo "&emsp;障害種類：<input type='text' name='kanrimei' size='8' maxlength='8' value={$skind} />";
  echo "&emsp;障害番号：<input type='text' name='kanrino' size='12' maxlength='12' value={$snum} /><br>";
  echo '&emsp;メッセージ：<input type="text" name="message" size="40" maxlength="60" value="" /><br><br>';
  echo '&emsp;メモメッセージ：&emsp;';
  echo '&emsp;<textarea name="memomsg" maxlength="200" placeholder="半角200、全角100文字以内、改行可能" cols="101"></textarea><br>';
  echo "<input type=hidden name=user value={$userid}>";
  echo '<br>&emsp;<span class=trylw>障害確認：</span><input type="checkbox" name="ckbox[]" value="confirm" />';
  echo '&emsp;<span class=trylw>処置完了：</span><input type="checkbox" name="ckbox[]" value="close" />';
  echo '&emsp;<span class=trylw>Mail送信：</span><input type="checkbox" name="ckbox[]" value="mlsend" />';
  echo '&emsp;<span class=trylw>メモ保存：</span><input type="checkbox" name="ckbox[]" value="memo" /> ';
  echo '&emsp;<span class=trred>ログ削除：</span><input type="checkbox" name="ckbox[]" value="logdel" /><br><br>';
  echo '&emsp;&emsp;<input class=button type="submit" name="go" value="実行" />';
  echo '</form>';
}
echo '<br>';
echo "<a href='MonitorManager.php?param={$userid}'><span class=button>監視モニターへ戻る</span></a>"; 
echo '</body></html>';
?>
