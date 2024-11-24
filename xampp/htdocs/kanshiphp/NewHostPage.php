<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "serverimagedisplay.php";
require_once "mailsendany.php";
require_once "phpsnmpprocessset.php";
require_once "phpsnmptrapset.php";
require_once "phpsnmptcpportset.php";

$pgm="NewHostPage.php";

function writelogsendmail($msg,$host){
  $msg=msg.' host '.$host;
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
  }elseif (preg_match("/:/",$_data)){
    $rdata=str_replace(':',';',$_data);
  }else{
    $rdata=$_data;
  }
  return $rdata;
}
function ckcolon($data,$host){
  if (false !== strpos($data, ',')){
    $rdata=str_replace(',',':',$_data);
  }elseif (false !== strpos($data, ';')){
    $rdata=str_replace(';',':',$data);
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
///
/// 新規ホスト作成関数
///
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
  //writeloge($pgm,"select host ok");                  ////////
  $groupname='unkown';
  $ostype=$_GET['ostype'];
  $result="0";
  $action=$_GET['action'];
  $viewname = $_GET['viewname'];
  $mailopt=$_GET['mailopt'];
  $eventlog=$_GET['eventlog'];
  $image=$_GET['image'];
  $comm=cknotype($_GET['comm']);
  $agenthost=cknotype($_GET['agenthost']);
  $tcpportb=cknotype($_GET['tcpport']);
  $tcpport=cksemicolon($tcpportb,$hostmei);
  $trapswt='0';
  /// TCP拡張機能チェック 
  if (substr($tcpport,0,1) == '&'){
    if ($ostype!='1'){
      $msg = "#error#".$user."#ホスト".$hostmei."のWindowsでは使えません";
      $nextpage = "NewHostPage.php";
      branch($nextpage,$msg);
    }
    $trapswt = '1';
  }
  /// コミュニティチェック
  if ($action=="2" || $action=="3"){
    if ($comm==""){
      $msg = "#error#".$user."#ホスト".$hostmei."のSNMPコミュニティがありません";
      $nextpage = "NewHostPage.php";
      branch($nextpage,$msg);
    }
  }
  $cpulimb=ckcnotype($_GET['cpulimit']);
  $cpuLim=ckcolon($cpulimb,$hostmei);
  $ramlimb=ckcnotype($_GET['ramlimit']);
  $ramLim=ckcolon($ramlimb,$hostmei);
  $disklimb=ckcnotype($_GET['disklimit']);
  $diskLim=ckcolon($disklimb,$hostmei);
  $processb=cknotype($_GET['process']);
  $process=cksemicolon($processb,$hostmei);
  $trapswp='0';
  /// プロセス拡張機能チェック
  if (substr($process,0,1) == '&'){
    if ($ostype!='1'){
      $msg = "#error#".$user."#ホスト".$hostmei."のWindowsでは使えません";
      $nextpage = "NewHostPage.php";
      branch($nextpage,$msg);
    }
    $trapswp = '1';
  }
  if (substr($process,0,1) == '%'){
    $msg = "#error#".$user."#この機能は使えません";
    $nextpage = "NewHostPage.php";
    branch($nextpage,$msg);
  }
  
///
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
///
/// vmmib process & tcpport
  if ($trapswt=='1'){ /// &tcpport
    $tcpportx=mb_substr($tcpport,1); ///top char strip
    $status=snmptcpportset($hostmei,$comm,$tcpportx);
    if ($status==1){
      $msg = "#error#".$user."#ホスト".$hostmei."へsnmpsetでTCPport登録失敗しました";
      $nextpage = "NewHostPage.php";
      branch($nextpage,$msg);
    } 
  }
  if ($trapswp=='1'){ /// $process
    $processx=mb_substr($process,1); ///top char strip
    $status=snmpprocessset($hostmei,$comm,$processx);
    if ($status==1){
      $msg = "#error#".$user."#ホスト".$hostmei."へsnmpsetでProcess登録失敗しました";
      $nextpage = "NewHostPage.php";
      branch($nextpage,$msg);
    } 
  }
  /*
  if ($trapswp=='2'){ /// %process
    $processx=mb_substr($process,1); ///top char strip
    $status=snmptrapset($hostmei,$comm,$processx);
    if ($status==1){
      $msg = "#error#".$user."#ホスト".$hostmei."へsnmpsetでProcess登録失敗しました";
      $nextpage = "NewHostPage.php";
      branch($nextpage,$msg);
    } 
  }
  */
  $delsql="delete from host where host='".$hostmei."'";
  putdata($delsql); 
  $insql="insert into host values('".$hostmei."','".$groupname."','".$ostype."','".$result."','".$action."','".$viewname."','".$mailopt."','".$tcpport."','".$cpuLim."','".$ramLim."','".$diskLim."','".$process."','".$image."','".$comm."','".$agenthost."','".$eventlog."')";
  putdata($insql); 
  ///
  /// statisticsレコードの削除と作成
  ///
  $delsql="delete from statistics where host='".$hostmei."'";
  putdata($delsql);
  $msg = $hostmei . " 既存SNMP状態レコード削除完了";
  $insql="insert into statistics (host,tstamp,gtype) values('".$hostmei."','000000000000','9')";
  putdata($insql); 
  $dbrc=putdata($insql);
  $msg = $hostmei . " SNMP状態レコード作成完了";
  ///
  /// eventレコードの作成
  ///
  $etime = date('ymdHis');
  $etype='5'; ///新規作成
  $insql="insert into eventlog (host,eventtime,eventtype,kanrisha) values('".$hostmei."','".$etime."','".$etype."','".$user."')";
  putdata($insql); 
  $msg = $insql . " イベントレコード作成完了";
  ///
  ///alert('終了前に中断');
  ///
  $msg="#notic#".$user."#新しいホスト".$hostmei."が作成されました";
  $nextpage="NewHostPage.php"; 
  branch($nextpage,$msg);
}
///-------------------------------------------------
///---------------変数初期化------------------------
///-------------------------------------------------
$brcode=""; /// global
$user="";   /// global
$brmsg="";  /// global;
$cpuLim="";
$diskLim="";
$ramLim="";
///-------------------------------------------------
///---------------新規ホスト追加処理----------------
///-------------------------------------------------
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
  print '<meta><link rel="stylesheet" href="css/kanshi1.css">';
  print '</head><body>';
  if ($brcode!=""){
    print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }
  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　新規監視対象ホスト作成　▽</h2>';
  hostimagelist(); 
  print '<h4>☆各項目の入力文字列間に空白を入れないこと（例：[abc def]はNG, [abcdef]または[abc_def]はOK）<br>';
  print '☆[監視他サイトホスト名]は他サイトのAgent監視先実ホスト名を指定します<br>';
  print '☆死活動作の[snmp通知なし]はイベントログなし、メールなし<br>';
  print '☆イベントログの[snmpログ抑止]は、snmpエラー連続出力を止める</h4>';
  print '<form name="newhost" method="get" action="NewHostPage.php">';
  print '&emsp;<span class=kom>ホスト名：</span>&ensp;<input type="text" name="hostname" placeholder="ホスト名又はIPアドレス" size="25" maxlength="25" value="" required/>';
  $image_sql='select * from serverimage';
  $imageRows=getdata($image_sql);
  $rowcnt=count($imageRows);
  print '&emsp;<span class=kom>モニター画像：</span>&ensp;<select name="image">';
  for ($cnt=0;$cnt<$rowcnt;$cnt++){
    $iitemlist=explode(',',$imageRows[$cnt]);
    print "<option value={$iitemlist[0]}>{$iitemlist[1]}</option>";
  }
  print '</select>';
  print '<br>';
  print '&emsp;<span class=kom>表示名：</span>&ensp;<input type="text" name="viewname" placeholder="監視画像上の名前" size="20" maxlength="20" value="" required/>';
  print '&emsp;<span class=kom>メール：</span>&ensp;<select name="mailopt">';
  print '<option value="0">メール非送信</option>';
  print '<option value="1">メール自動送信</option>';
  print '</select>';
  print '&emsp;<span class=kom>イベントログ：</span>&ensp;<select name="eventlog">';
  print '<option value="0">ログ出力</option>';
  print '<option value="1">snmpログ抑止</option>';
  print '</select><br>';

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
  ///  print '<option value="5">Ncat監視</option>';  
  print '</select>';
  print '&emsp;<span class=kom>コミュニティ名：</span>&ensp;<input type="text" name="comm" placeholder="snmp監視時必須" size="10" maxlength="10" value="" /><br>';
  
  print '&emsp;<span class=kom>監視他サイトホスト名：</span>&ensp;<input type="text" name="agenthost" placeholder="他監視サイトのホスト名" size="20" maxlength="20" value="" />';
  print '&emsp;&emsp;&emsp;<h4>以下、入力オプション<br>';
  print '☆CPU警告欄&emsp;&emsp;&emsp;警告値：危険値 デフォルト80;90<br>';
  print '☆メモリ警告欄&emsp;&emsp;警告値：危険値<br>';
  print '☆ディスク警告欄&emsp;警告値：危険値</h4>';
  print '&emsp;<span class=kom>CPU警告：</span>&ensp;<input type="text" name="cpulimit" size="2" maxlength="5" value="80:90" />';
  print '&emsp;<span class=kom>メモリ警告：</span>&ensp;<input type="text" name="ramlimit" size="2" maxlength="5" value="80:90" />';
  print '&emsp;<span class=kom>ディスク警告：</span>&ensp;<input type="text" name="disklimit" size="2" maxlength="17" value="80:90"/><br>';
  print '<h4>☆TCPポート欄&emsp;&emsp;ポート番号；区切<br>'; 
  print '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;UNIX系監視対象ホストをプライベートMIB方式で行うには先頭に「&」を付与<br>';
  print '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;監視対象ホストをNCAT方式で行うには先頭に「%」を付与<br>';
  print '☆監視プロセス欄&emsp;プロセス名；区切<br>';
  print '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;UNIX系監視対象ホストをプライベートMIB方式で行うに先頭に「&」を付与</h4>';
    
  print '&emsp;<span class=kom>TCPチェックポート：</span>&ensp;<input type="text" name="tcpport" placeholder="80;443;1521の様にセミコロンで区切る" size="40" maxlength="50" value="" /><br>';
  print '&emsp;<span class=kom>監視プロセス：</span>&ensp;<input type="text" name="process" placeholder="apache:sendmailの様にセミコロンで区切る、exe拡張子不要" size="60" maxlength="60" value="" />';
  print '<br><br>';
  print '&emsp;&emsp;&emsp;<input class=button type="submit" name="create" value="作成" />';
  print "<input type=hidden name=user value={$user}>";
  print '</form>';

  ///-------------------------------------------------
  ///---------------監視モニターへ--------------------
  ///-------------------------------------------------
  print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
  print '</body></html>';
}
?>

