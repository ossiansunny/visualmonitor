<?php
require_once "mysqlkanshi.php";
require_once "mailsendevent.php";

function branch($_page,$_param){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo "<form name='F' action={$_page} method='get'>";
  echo "<input type=hidden name=param value={$_param}>";
  echo '<input type="submit" name="next" value="Waiting...">';
  echo '</form>';
}
echo '<html><head><meta>';
echo '<link rel="stylesheet" href="kanshi1.css">';
echo '</head><body>';
$pgm = "eventlogupdeldb.php";
$userid = $_GET['user'];
if (!isset($_GET['ckbox'])){
  $nextpage="MonitorManager.php";
  branch($nextpage,userid);
  exit();
}
$fckbox = $_GET['fckbox'];  /// 選択されたeventlog
$ckbox = $_GET['ckbox'];    /// 選択されたボタン配列
$kanrimei = $_GET['kanrimei']; /// 障害種類
$kanrino = $_GET['kanrino']; /// 障害番号
$message = $_GET['message'];
if (is_null($message)){
  $message = '';
}
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
foreach ($ckbox as $chkbox){  /// 選択されたボタンデータ処理
  if ($chkbox=='confirm'){     // 「障害確認」ボタン ---> statistics gtype=5 
    $confclose = "1";          // confclose=1         
    $gtype = "5";
  }elseif ($chkbox=='close'){  // 「処置完了」ボタン ---> statistics gtype=7
    $confclose = "2";          // confclose=2
    $gtype = "7";
  }elseif ($chkbox=='mlsend'){ // 「メール送信」ボタン
    $confclose="3";            // confclose=3 後の判定で必要
    $mailsend = "1";
  }elseif ($chkbox=='logdel'){ // 「ログ削除」ボタン
    $confclose = "4";          // confclose=4
    $noupdatesw = "1";
  }elseif ($chkbox=='memo'){   // 「メモ保存」ボタン
    $confclose="5";            // confclose=5
    $memosw = "1";
  }else{
    $msg='checkbox unknown data found:'.$chkbox;
    writeloge($pgm,$msg);
    echo '「チェックボックスにチェックをして下さい<br>';
    echo '<a href="EventLogPage.php">イベントログページへ戻る</a>';
    exit();
  }
}
///--------- 選択されたeventlog レコード処理 -----------
///
$kbox=[];
if (is_array($fckbox)){
  $kbox=$fckbox;
}else{
  $kbox[0]=$fckbox;
}
foreach ($kbox as $krec){  /// event record
  $sdata=explode(',',$krec);
  $host = $sdata[0];  // host
  $evtime = $sdata[1]; // eventtime
  $evtype = $sdata[2];
  $stype = $sdata[3];
  $svalue = $sdata[4];
  $ksha = $sdata[5];
  $kno = $sdata[6];
  $cfcs = $sdata[7];
  $msend = $sdata[8];
  $msg = $sdata[9];
  $u_data="select usercode from user where userid='".$userid."'";
  if ($u_data[0]!='error'){
    $kno=$u_data[0];
  }
  if ($confclose=='0'){
    $confclose=$sdata[7]; ///confclose
  } elseif($confclose=="1") { /// 確認の場合
    /// update statistics set gtype"5"
    $evtime = date('ymdHis');
    $usql='update statistics set gtype="5" where host="'.$host.'"';
    $rtndb=putdata($usql);
    if(!empty($rtndb)){
      $inssql='insert into eventlog values("'.$host.'","'.$evtime.'","a","'.$stype.'","'.$svalue.'","'.$kanrimei.'","'.$kanrino.'","'.$cfcs.'","'.$msend.'","'.$msg.'")';
      putdata($inssql);
      writeloge($pgm,"Failed DB Access: ".$usql); 
    } 
    writelogd($pgm,$usql);
    $evtype="3"; ///監視管理
    $cfcs = "1";  ///確認
    $inssql='insert into eventlog values("'.$host.'","'.$evtime.'","'.$evtype.'","'.$stype.'","'.$svalue.'","'.$kanrimei.'","'.$kanrino.'","'.$cfcs.'","'.$msend.'","'.$msg.'")';
    putdata($inssql);
    writelogd($pgm,$inssql);
    $usql='update host set result="8" where host="'.$host.'"';
    putdata($usql); 
    writelogd($pgm,$usql);
  } elseif($confclose=="2") {
    $mailsend="1";
    $memosw="1"; 
  }
  if ($mailsend=='0'){
    $mailsend=$sdata[8];  /// mail送信フラグ
  }
  if ($memosw=="1"){  ///監視メモ出力
    $inssql='insert into eventmemo values("'.$evtime.'","'.$host.'","'.$kanrimei.'","'.$kanrino.'","'.$memomsg.'")';
    putdata($inssql);
  }
  if ($noupdatesw == "1" && $confclose != "0"){
    /// 確認済、クローズ済のみ削除
    /// 削除処理
    $delsql = "delete from eventlog where host='".$host."' and eventtime='".$evtime."'";
    putdata($delsql);    
  }else{
    ///---------------------------------------------------
    /// 更新処理 正常データ以外を更新
    ///--- メール送信 ------------------------------------
    if ($mailsend=='1'){
      mailsendevent($krec,$kanrimei,$kanrino,$confclose,$message);
    }
    $gsql="select gtype from statistics where host='".$host."'";
    $gdata=getdata($gsql);
    if ($gdata[0]=='error'){
      $s_gtype='';
    } else {
      $s_gtype=$gdata[0];
    }
    if ($s_gtype=='6'){ // イベント削除、クローズイベント作成
      $dsql="delete from eventlog where host='".$host."'";
      putdata($dsql);
      $evtype="3"; ///監視管理
      $cfcs = "3";  /// クローズ  
      $evtime = date('ymdHis');
      $inssql='insert into eventlog values("'.$host.'","'.$evtime.'","'.$evtype.'","'.$stype.'","'.$svalue.'","'.$kanrimei.'","'.$kanrino.'","'.$cfcs.'","'.$msend.'","'.$msg.'")';
      putdata($inssql);
      writelogd($pgm,$inssql);
      $usql='update statistics set gtype="9" where host="'.$host.'"';
      $rtn=putdata($usql);
      if(!empty($rtn)){
        $inssql='insert into eventlog values("'.$host.'","'.$evtime.'","a","'.$stype.'","'.$svalue.'","'.$kanrimei.'","'.$kanrino.'","'.$cfcs.'","'.$msend.'","'.$msg.'")';
        putdata($inssql);
        writeloge($pgm,"Failed DB Access: ".$usql); 
      } 
      writelogd($pgm,$inssql);
    }
  }
}

$nextpage="MonitorManager.php";
branch($nextpage,$userid);
?>
