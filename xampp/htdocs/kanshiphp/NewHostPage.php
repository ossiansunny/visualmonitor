<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "serverimagedisplay.php";
require_once "mailsendany.php";
require_once "phpsnmpactive.php";
require_once "phpsnmpprocessset.php";
require_once "winhostping.php";

$pgm="NewHostPage.php";

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
function hostcreate(){
  global $pgm; 
  $user=$_GET['user'];
  $hostmei=$_GET['hostname']; 
  $c_sql="select * from host where host='".$hostmei."'";
  $c_data=getdata($c_sql);
  if (! empty($c_data)){
    $msg = "#error##ホスト".$hostmei."が既に存在しています";
    $nextpage = "NewHostPage.php";
    branch($nextpage,$msg);
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
  //print "cpu:".$cpulimit." ram:".$ramlimit." disk:".$disklimit."<br>";
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
    }
  } elseif ($action=="1"){
    $status=hostping($hostmei);
    if ($status==1){
      $msg = "#error##ホスト".$hostmei."がpingに応答しない、更新無効";
      $nextpage = "NewHostPage.php";
      writelogd($pgm,$msg);
      branch($nextpage,$msg);
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
  $insql="insert into host values('".$hostmei."','".$groupname."','".$ostype."','".$result."','".$action."','".$viewname."','".$mailopt."','".$tcpport."','".$cpulimit."','".$ramlimit."','".$disklimit."','".$process."','".$image."','".$comm."','')";
  putdata($insql); 
  ///
  /// statisticsレコードの削除と作成
  ///
  $delsql="delete from statistics where host='".$hostmei."'";
  putdata($delsql);
  $msg = $hostmei . " 既存SNMP状態レコード削除完了";
  writeloge($pgm,$msg);
  $insql="insert into statistics (host,tstamp,gtype) values('".$hostmei."','000000000000','9')";
  putdata($insql); 
  $dbrc=putdata($insql);
  $msg = $hostmei . " SNMP状態レコード作成完了";
  writeloge($pgm,$msg);
  ///
  /// eventレコードの作成
  ///
  $etime = date('ymdHis');
  $etype='5'; //新規作成
  $insql="insert into eventlog (host,eventtime,eventtype,kanrisha) values('".$hostmei."','".$etime."','".$etype."','".$user."')";
  putdata($insql); 
  $msg = $insql . " イベントレコード作成完了";
  writeloge($pgm,$msg);
  ///
  if ($trapsw=='1'){
    $processx=mb_substr($process,1); //top char strip
    $status=snmpprocessset($hostmei,$comm,$processx);
    if ($status==1){
      $msg='プロセス＝'.$processx.' 登録 snmpset 無応答';
      writelogsendmail($msg,$hostmei);
    } 
  }
  ///
  //alert('終了前に中断');
  ///
  $msg="#notic#".$user."#新しいホスト".$hostmei."が作成されました";
  $nextpage="NewHostPage.php"; 
  branch($nextpage,$msg);
  //exit;
}
//-------------------------------------------------
//---------------変数初期化------------------------
//-------------------------------------------------
$brcode=""; // global
$user="";   // global
$brmsg="";  // global;
$cpulimit="";
$disklimit="";
$ramlimit="";
//-------------------------------------------------
//---------------新規ホスト追加処理----------------
//-------------------------------------------------
if (isset($_GET['create'])){  
  hostcreate();

///-------------------------------------------------
///--------セッションデータのユーザ取得-------------
///-------------------------------------------------
}elseif (!isset($_GET['param'])){   
  paramGet($pgm);
///-------------------------------------------------
///------ユーザおよび渡された情報の解析-------------
///--param=<user> 又は　----------------------------
///--param=#<code>#<user>#<message>-----------------
///
}else{ 
  paramSet();
  

///-------------------------------------------------
///-----ホスト新規追加入力画面----------------------
///-------------------------------------------------
  print '<html><head>';
  print '<meta><link rel="stylesheet" href="kanshi1.css">';
  print '</head><body>';
  if ($brcode!=""){
    print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }
  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　新規監視対象ホスト作成　▽</h2>';
  hostimagelist(); 
  print '<h4>&emsp;&emsp;&emsp;☆各項目の文字列間に空白を入れないこと（例：[abc def]はNG, [abcdef]または[abc_def]はOK）</h4>';
  print '<form name="newhost" method="get" action="NewHostPage.php">';
  print '&emsp;<span class=kom>ホスト名：</span>&ensp;<input type="text" name="hostname" placeholder="ホスト名又はIPアドレス" size="25" maxlength="25" value="" required/>';
  $sql='select * from serverimage order by image';
  $rows=getdata($sql);
  $rowcnt=count($rows);
  print '&emsp;<span class=kom>モニター画像：</span>&ensp;<select name="image">';
  for ($cnt=0;$cnt<$rowcnt;$cnt++){
    $iitemlist=explode(',',$rows[$cnt]);
    print "<option value={$iitemlist[0]}>{$iitemlist[1]}</option>";
  }
  print '</select>';
  print '<br>';
  print '&emsp;<span class=kom>表示名：</span>&ensp;<input type="text" name="viewname" placeholder="監視画像上の名前" size="15" maxlength="15" value="" required/>';
  print '&emsp;<span class=kom>コミュニティ名：</span>&ensp;<input type="text" name="comm" placeholder="snmp監視時必須" size="10" maxlength="10" value="" /><br>';
  print '&emsp;<span class=kom>OS種類：</span>&ensp;<select name="ostype">';
  print '<option value="0">Windows</option>';
  print '<option value="1">Unix/Linux</option>';
  print '<option value="2">Others</option>';
  print '</select>';
  print '&emsp;<span class=kom>死活動作：</span>&ensp;<select name="action">';
  print '<option value="0">非監視</option>';
  print '<option value="1">PING監視</option>';
  print '<option value="2">SNMP監視</option>';
  print '<option value="3">SNMP通知なし</option>';
  print '<option value="4">Agent監視</option>';  
  print '</select>';
  print '&emsp;<span class=kom>メール：</span>&ensp;<select name="mailopt">';
  print '<option value="0">メール非送信</option>';
  print '<option value="1">メール自動送信</option>';
  print '</select>';
  print '&emsp;&emsp;&emsp;<h4>以下、入力オプション</h4>';
  print '&emsp;<span class=kom>TCPチェックポート：</span>&ensp;<input type="text" name="tcpport" placeholder="80;443;1521の様にセミコロンで区切る" size="40" maxlength="50" value="" />';
  print '&emsp;&emsp;&emsp;<h4>☆閾値の前半は警告値、後半は危険値、これを：（コロン）で区切ります<br>';
  print '☆グラフが表示出来ますので入力して下さい。デフォルトは 80:90です</h4>';
  print '&emsp;<span class=kom>CPU閾値：</span>&ensp;<input type="text" name="cpulimit" size="2" maxlength="5" value="80:90" />';
  print '&emsp;<span class=kom>メモリ閾値：</span>&ensp;<input type="text" name="ramlimit" size="2" maxlength="5" value="80:90" />';
  print '&emsp;<span class=kom>ディスク閾値：</span>&ensp;<input type="text" name="disklimit" size="2" maxlength="17" value="80:90"/><br>';
  print '&emsp;<span class=kom>監視プロセス：</span>&ensp;<input type="text" name="process" placeholder="apache:sendmailの様にセミコロンで区切る、exe拡張子不要" size="60" maxlength="60" value="" />';
  print '&emsp;&emsp;&emsp;<h4>☆インターネット内サーバーのプロセスには、先頭に「&」を入力します（例：&apache;sendmail）<br>';
  print '&emsp;&emsp;ただし、監視対象サーバには、プライベートMIBとCRONTABの設定が必要です。</h4>';
  print '<br>';
  print '&emsp;&emsp;&emsp;<input class=button type="submit" name="create" value="作成" />';
  print "<input type=hidden name=user value={$user}>";
  print '</form>';
}
//-------------------------------------------------
//---------------監視モニターへ--------------------
//-------------------------------------------------
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body></html>';

?>

