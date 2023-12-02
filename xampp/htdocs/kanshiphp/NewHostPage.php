<?php
require_once "mysqlkanshi.php";
require_once "serverimagedisplay.php";
require_once "mailsendany.php";
require_once "phpsnmpactive.php";
require_once "phpsnmpprocessset.php";
require_once "winhostping.php";

$pgm="NewHostPage.php";

function branch($_page,$_param){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo "<form name='F' action={$_page} method='get'>";
  echo "<input type=hidden name=param value={$_param}>";
  echo '<input type="submit" name="next" value="Waiting...">';
  echo '</form>';
}
function writelogsendmail($msg,$host){
  $msg=msg.' host '.$host;
  writelogd('NewHostPage.php',$msg);
  $sql='select * from admintb';
  $rows=getdata($sql);
  $sdata=explode(',',$rows[0]);
  $toaddr=$sdata[3];
  $fromaddr=$sdata[4];
  $subj='ホスト '.$host.' 保守アラート';
  mailsendany('hostupdate',$fromaddr,$toaddr,$subj,$msg);
}

function cksemicolon($_data,$host){
  if (preg_match("/,/",$_data)){
    $rdata=str_replace(',',';',$_data);
    writelogsendmail('PORT,PROCESS列区分をセミコロンへ変換',$host);
  }elseif (preg_match("/:/",$_data)){
    $rdata=str_replace(':',';',$_data);
    writelogsendmail('PORT,PROCESS列区分をセミコロンへ変換',$host);
  }else{
    $rdata=$_data;
  }
  return $rdata;
}
function ckcolon($data,$host){
  if (false !== strpos($data, ',')){
    $rdata=str_replace(',',':',$_data);
    writelogsendmail('CPU,RAM,DISK制限値をコロンへ変換',$host);
  }elseif (false !== strpos($data, ';')){
    $rdata=str_replace(';',':',$data);
    writelogsendmail('CPU,RAM,DISK制限値をコロンへ変換',$host);
  }else{
    $rdata=$data;
  }
  return $rdata;
}
function cknotype($_data){
  if (is_null($_data)){
    $rdata='';
  }else{
    $rdata=$_data;
  }
  return $rdata;
}
function ckcnotype($_data){
  if (is_null($_data)){
    $rdata='';
  }else{
    $rdata=$_data;
    if (!preg_match("/:/",$rdata)){
      $rdata='';
    } 
  }
  return $rdata;
}

$pgm='NewHostPage.php';
$brcode="";
$user="";
$brmsg="";
$cpulimit="";
$disklimit="";
$ramlimit="";
///-------------------------------------------------
///---------------新規ホスト追加処理----------------
///-------------------------------------------------
if (isset($_GET['create'])){  
  $user=$_GET['user'];
  $hostmei=$_GET['hostname']; 
  $c_sql="select * from host where host='".$hostmei."'";
  $c_data=getdata($c_sql);
  if (! empty($c_data)){
    $msg = "#error##ホスト".$hostmei."が既に存在しています";
    $nextpage = "NewHostPage.php";
    branch($nextpage,$msg);
    exit;
  }
  $groupname='unkown';
  $ostype=$_GET['ostype'];
  $result="0";
  $action=$_GET['action'];
  $viewname = $_GET['viewname'];
  $mailopt=$_GET['mailopt'];
  $image=$_GET['image'];
  $comm=cknotype($_GET['comm']);
  if ($comm==""){
    $comm="public";
  }
  $tcpportb=cknotype($_GET['tcpport']);
  $tcpport=cksemicolon($tcpportb,$hostmei);
  if ($action=="2" || $action=="3"){
    if ($tcpport==""){
      $tcpport="22";
    }
  }
  if ($action=="2" || $action=="3"){
    if ($comm==""){
      $comm=="public";
    }
  }
  
  $cpulimb=ckcnotype($_GET['cpulimit']);
  $cpulimit=ckcolon($cpulimb,$hostmei);
  $ramlimb=ckcnotype($_GET['ramlimit']);
  $ramlimit=ckcolon($ramlimb,$hostmei);
  $disklimb=ckcnotype($_GET['disklimit']);
  $disklimit=ckcolon($disklimb,$hostmei);
  $processb=cknotype($_GET['process']);
  $process=cksemicolon($processb,$hostmei);
  //echo "cpu:".$cpulimit." ram:".$ramlimit." disk:".$disklimit."<br>";
  $trapsw='0';
  if (substr($process,0,1) == '&'){
    $trapsw = '1';
  }
///
  if ($action=='2' || $action=='3'){
    $status=snmpactive($hostmei,$comm);
    if ($status==1){
      $msg = "#error##ホスト".$hostmei."がsnmp対応でないか無応答、更新無効、入力値チェック";
      $nextpage = "NewHostPage.php";
      writelogd($pgm,$msg);
      branch($nextpage,$msg);
      exit;
    }
  } elseif ($action=="1"){
    $status=hostping($hostmei);
    if ($status==1){
      $msg = "#error##ホスト".$hostmei."がpingに応答しない、更新無効";
      $nextpage = "NewHostPage.php";
      writelogd($pgm,$msg);
      branch($nextpage,$msg);
      exit;
    }
  }

  if ($image == ''){
    if ($ostype=='0'){
      $image="pc.png";
    }elseif ($ostype=='1'){
      $image="server.png";
    }elseif ($ostype=="2"){
      $image="router.png";
    }else{
      $image="pc.png";
    }
  }
  $delsql="delete from host where host='".$hostmei."'";
  putdata($delsql);
  $insql="insert into host values('".$hostmei."','".$groupname."','".$ostype."','".$result."','".$action."','".$viewname."','".$mailopt."','".$tcpport."','".$cpulimit."','".$ramlimit."','".$disklimit."','".$process."','".$image."','".$comm."')";
  var_dump($insql);
  putdata($insql); 
  ///
  /// statisticsレコードの削除と作成
  ///
  $delsql="delete from statistics where host='".$hostmei."'";
  putdata($delsql);
  $msg = $hostmei . " 既存SNMP状態レコード削除完了";
  writelogd($pgm,$msg);
  $insql="insert into statistics (host,tstamp,gtype) values('".$hostmei."','000000000000','9')";
  putdata($insql); 
  $dbrc=putdata($insql);
  $msg = $hostmei . " SNMP状態レコード作成完了";
  writelogd($pgm,$msg);
  ///
  /// eventレコードの作成
  ///
  $etime = date('ymdHis');
  $etype='5'; ///新規作成
  $insql="insert into eventlog (host,eventtime,eventtype,kanrisha) values('".$hostmei."','".$etime."','".$etype."','".$user."')";
  putdata($insql); 
  $msg = $insql . " イベントレコード作成完了";
  writelogd($pgm,$msg);
  ///
  if ($trapsw=='1'){
    $processx=mb_substr($process,1); //先頭文字削除
    $status=snmpprocessset($hostmei,$comm,$processx);
    if ($status==1){
      $msg='プロセス＝'.$processx.' 登録 snmpset 無応答';
      writelogsendmail($msg,$hostmei);
    } 
  }
  $nextpage="MonitorManager.php";
  branch($nextpage,$user);
  exit;

///-------------------------------------------------
///--------セッションデータのユーザ取得-------------
///-------------------------------------------------
}elseif (!isset($_GET['param'])){ 
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="NewHostPage.php" method="get">';
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
///------------------------------------------------------------------
///------ユーザおよび渡された情報の解析------------------------------
///--param=<user> 又は　#<code>#<user>#<message>のフォーマット-------
///
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
  
///-------------------------------------------------
///-----ホスト新規追加入力画面----------------------
///-------------------------------------------------
  echo '<html><head>';
  echo '<meta><link rel="stylesheet" href="kanshi1.css">';
  echo '</head><body>';
  if ($brcode!=""){
    echo "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }
  ///
  echo '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　新規監視対象ホスト作成　▽</h2>';
  ///
  hostimagelist(); 
  ///
  echo '<h4>&emsp;&emsp;&emsp;☆各項目の文字列間に空白を入れないこと（例：[abc def]はNG, [abcdef]または[abc_def]はOK）</h4>';
  echo '<form name="newhost" method="get" action="NewHostPage.php">';
  echo '&emsp;<span class=kom>ホスト名：</span>&ensp;<input type="text" name="hostname" placeholder="ホスト名又はIPアドレス" size="25" maxlength="25" value="" required/>';
  $sql='select * from serverimage order by image';
  $rows=getdata($sql);
  $rowcnt=count($rows);
  echo '&emsp;<span class=kom>モニター画像：</span>&ensp;<select name="image">';
  for ($cnt=0;$cnt<$rowcnt;$cnt++){
    $iitemlist=explode(',',$rows[$cnt]);
    echo "<option value={$iitemlist[0]}>{$iitemlist[1]}</option>";
  }
  echo '</select>';
  echo '<br>';
  echo '&emsp;<span class=kom>表示名：</span>&ensp;<input type="text" name="viewname" placeholder="監視画像上の名前" size="15" maxlength="15" value="" required/>';
  echo '&emsp;<span class=kom>コミュニティ名：</span>&ensp;<input type="text" name="comm" placeholder="snmp監視時必須" size="10" maxlength="10" value="" /><br>';
  echo '&emsp;<span class=kom>OS種類：</span>&ensp;<select name="ostype">';
  echo '<option value="0">Windows</option>';
  echo '<option value="1">Unix/Linux</option>';
  echo '<option value="2">Others</option>';
  echo '</select>';
  echo '&emsp;<span class=kom>死活動作：</span>&ensp;<select name="action">';
  echo '<option value="0">非監視</option>';
  echo '<option value="1">PING監視</option>';
  echo '<option value="2">SNMP監視</option>';
  echo '<option value="3">SNMP通知なし</option>';
  echo '<option value="4">Agent監視</option>';  
  echo '</select>';
  echo '&emsp;<span class=kom>メール要非：</span>&ensp;<select name="mailopt">';
  echo '<option value="0">メール非送信</option>';
  echo '<option value="1">メール自動送信</option>';
  echo '</select>';
  echo '&emsp;&emsp;&emsp;<h4>以下、入力オプション</h4>';
  echo '&emsp;<span class=kom>TCPチェックポート：</span>&ensp;<input type="text" name="tcpport" placeholder="80;443;1521の様にセミコロンで区切る" size="40" maxlength="50" value="" />';
  echo '&emsp;&emsp;&emsp;<h4>☆閾値の前半は警告値、後半は危険値、これを：（コロン）で区切ります<br>';
  echo '☆グラフが表示出来ますので入力して下さい。デフォルトは 80:90です</h4>';
  echo '&emsp;<span class=kom>CPU閾値：</span>&ensp;<input type="text" name="cpulimit" size="2" maxlength="5" value="80:90" />';
  echo '&emsp;<span class=kom>メモリ閾値：</span>&ensp;<input type="text" name="ramlimit" size="2" maxlength="5" value="80:90" />';
  echo '&emsp;<span class=kom>ディスク閾値：</span>&ensp;<input type="text" name="disklimit" size="2" maxlength="17" value="80:90"/><br>';
  echo '&emsp;<span class=kom>監視プロセス：</span>&ensp;<input type="text" name="process" placeholder="apache:sendmailの様にセミコロンで区切る、exe拡張子不要" size="60" maxlength="60" value="" />';
  echo '&emsp;&emsp;&emsp;<h4>☆インターネット内サーバーのプロセスには、先頭に「&」を入力します（例：&apache;sendmail）<br>';
  echo '&emsp;&emsp;ただし、監視対象サーバには、プライベートMIBとCRONTABの設定が必要です。</h4>';
  echo '<br>';
  echo '&emsp;&emsp;&emsp;<input class=button type="submit" name="create" value="作成" />';
  echo "<input type=hidden name=user value={$user}>";
  echo '</form>';
}
echo "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
echo '</body></html>';

?>
