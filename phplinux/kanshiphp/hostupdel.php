<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "serverimagedisplay.php";
require_once "hostping.php";
//require_once "winhostncat.php";
require_once "phpsnmpprocessset.php";
require_once "phpsnmptrapset.php";
require_once "phpsnmptcpportset.php";
require_once "phpsnmpactive.php";
require_once "mailsendany.php";

$pgm="hostupdel.php";

function writelogsendmail($_msg,$_host){
  global $pgm;
  $body=$_msg.' host '.$_host;
  writelogd($pgm,$body);
  $admin_sql='select * from admintb';
  $adminRows=getdata($admin_sql);
  $hostArr=explode(',',$adminRows[0]);
  $mailToAddr=$hostArr[3];
  $mailFromAddr=$hostArr[4];
  $subj='ホスト '.$_host.' 保守アラート';
  mailsendany('hostupdate',$mailFromAddr,$mailToAddr,$subj,$body);
}
function snmpcheck($_host,$_ostype,$_comm){
  /// 監視対象にSNMPがインストールされているかチェック
  /// snmpactiveでロケーションデータをアクセス
  $status=0;  
  if ($_ostype=='0' || $_ostype=='1' || $_ostype=='2'){
    $status=snmpactive($_host,$_comm); // phpsnmpactive.php
    if ($status==1){
      writelogsendmail('SNMP応答チェック無応答',$host);
    }
  }
  return $status; /// 0:ok 1:ng
}

function cksemicolon($_data,$_host){
  $okData="";
  if (preg_match("/,/",$_data)){
    $okData=str_replace(',',';',$_data);
    //writelogsendmail('PORT,PROCESS列区分をセミコロンへ変換',$_host);
  }elseif (preg_match("/:/",$_data)){
    $okData=str_replace(':',';',$_data);
    //writelogsendmail('PORT,PROCESS列区分をセミコロンへ変換',$_host);
  }else{
    $okData=$_data;
  }
  return $okData;
}
function ckcolon($_data,$_host){
  $okData="";
  if (preg_match("/,/",$_data)){
    $okData=str_replace(',',':',$_data);
    //writelogsendmail('CPU,RAM,DISK制限値をコロンへ変換',$_host);
  }elseif (preg_match("/;/",$_data)){
    $okData=str_replace(';',':',$_data);
    //writelogsendmail('CPU,RAM,DISK制限値をコロンへ変換',$_host);
  }else{
    $okData=$_data;
  }
  return $okData;
}
function cknotype($_data){
  $okData="";
  if (is_null($_data)){
    $okData='';
  }else{
    $okData=$_data;
  }
  return $okData;
}
function ckcnotype($_data){
  $okData="";
  if (is_null($_data)){
    $okData='';
  }else{
    $okData=$_data;
    if (!preg_match("/:/",$okData)){
      $okData='';
    } 
  }
  return $okData;
}
/// 
print '<html><head><meta>';
print '<link rel="stylesheet" href="css/kanshi1.css">';
print '<script type="text/javascript">';
print '<!--';
print 'function check(host){';
print 'if(window.confirm( host + " を削除してよろしいですか？")){';
print 'return true;';
print '}';
print 'else{';
print 'window.alert("キャンセルされました");';
print 'return false;';
print '}';
print '}';
print '// -->';
print '</script>';

print '</head><body>';

$user = $_GET['user'];
///------------------------------
///--ホストデータ削除処理--------
///------------------------------
if (isset($_GET['delete'])){
  $host=$_GET['host'];
  $user=$_GET['user'];
  $updel=$_GET['delete']; 
  $hostRow=$_GET['fdata']; /// host table データ
  $rdarray=explode(',',$hostRow);
  /// delete host record
  $host_sql="delete from host where host='".$host."'";
  putdata($host_sql);
  /// delete statistics record
  $stat_sql="delete from statistics where host='".$host."'";
  putdata($stat_sql); 
  /// イベントに残す
  /// insert eventlog
  $eventTime = date('ymdHis');
  $eventType='4'; //削除
  $event_sql="insert into eventlog (host,eventtime,eventtype,kanrisha) values('".$host."','".$eventTime."','".$eventType."','".$user."')";
  putdata($event_sql); 
  /// 実行通知　ホスト一覧の前に
  $msg = '#notic#'.$user.'#ホスト'.$host .'が正常に削除されました';
  $nextpage = "HostListPage.php";
  writelogd($pgm,$msg);
  branch($nextpage,$msg);
  exit;
///-------------------------------
///-- ホストデータ更新処理 -------
///-------------------------------
}elseif (isset($_GET['update'])){
  $trapswt = '0'; 
  $trapswp = '0';
  $trapswp2 = '0';
  $host=$_GET['host'];
  $user=$_GET['user'];
  $updel=$_GET['update']; //update host record to host table
  $hostRow=$_GET['fdata']; // host レコードデータ
  $rdarray=explode(',',$hostRow);
  $groupName='notused';
  $osType=$_GET['ostype'];
  $action=$_GET['action'];
  $oldAction=$_GET['oldaction']; 

  $comm="";
  if ($action=='2' or $action=='3' or $action=='4'){
    if (is_null($_GET['comm']) or empty($_GET['comm'])){
      $msg = '#error#'.$user.'#ホスト'.$host.'SNMPコミュニティの設定がなし';
      $nextpage = "HostListPage.php";
      writelogd($pgm,$msg);
      branch($nextpage,$msg);
    }else{
      $comm=$_GET['comm'];
      if ($action!=$oldAction){
        $rtnck=snmpcheck($host,$osType,$comm);
        if ($rtnck!=0){
          $msg = '#error#'.$user.'#ホスト'.$host.'SNMPコミュニティがアクセス不可、更新無効';
          $nextpage = "HostListPage.php";
          writelogd($pgm,$msg);
          branch($nextpage,$msg);
        }
      }
    }
  }else{ /// action=0(監視なし),1(Ping監視),5(Ncat監視)
    if (isset($_GET['comm']) and $_GET['comm']!=""){
      $comm=$_GET['comm'];
    }
  }
  /// 更新共通
  $viewName = $_GET['viewname'];
  $mailOpt=$_GET['mailopt'];
  $tcpPortb=cknotype($_GET['tcpport']);
  $tcpPort=cksemicolon($tcpPortb,$host);
  if (substr($tcpPort,0,1) == '&'){
    $trapswt = '1';
  }
  $cpuLimb=ckcnotype($_GET['cpulim']);
  $cpuLim=ckcolon($cpuLimb,$host);
  $ramLimb=ckcnotype($_GET['ramlim']);
  $ramLim=ckcolon($ramLimb,$host);
  $diskLimb=ckcnotype($_GET['disklim']);
  $diskLim=ckcolon($diskLimb,$host);
  $processb=cknotype($_GET['process']);
  $process=cksemicolon($processb,$host);
  if (substr($process,0,1) == '&'){
    $trapswp = '1';
  }
  if (substr($process,0,1) == '%'){
    $trapswp2 = '1';
  }
  if ($action=='2' || $action=='3'){
    if ($tcpPort==""){
      $tcpPort="22";
    }
  }
  $image=$_GET['image'];
  if ($image==''){
    if ($osType=='0'){
      $image="pc.jpg";
    }elseif ($osType=='1'){
      $image="server.jpg";
    }elseif ($osType=='2'){
      $image="router.jpg";
    }else{
      $image="pc.jpg";
    }
  }
  $wtarray=array("","","","","","","","","","","","","","");
  $wtarray[0]='0|host|'.$host; 
  $wtarray[1]='0|groupname|'.$groupName; 
  $wtarray[2]='0|ostype|'.$osType; 
  $wtarray[3]='0|result|'.$rdarray[3]; 
  $wtarray[4]='0|action|'.$action; 
  $wtarray[5]='0|viewname|'.$viewName; 
  $wtarray[6]='0|mailopt|'.$mailOpt; 
  if ($trapswt == '1'){ 
    $wtarray[7]='1|tcpport|'.$tcpPort;
  }else{
    $wtarray[7]='0|tcpport|'.$tcpPort;
  }
  $wtarray[8]='0|cpulim|'.$cpuLim; 
  $wtarray[9]='0|ramlim|'.$ramLim; 
  $wtarray[10]='0|disklim|'.$diskLim;
  if ($trapswp == '1' or $trapswp2 == '1'){ 
    $wtarray[11]='1|process|'.$process;
  }else{
    $wtarray[11]='0|process|'.$process;
  }
  $wtarray[12]='0|image|'.$image;
  $wtarray[13]='0|snmpcomm|'.$comm; 
  $cct=0;
  foreach ($wtarray as $wtrec){  
    $wtval=explode('|',$wtrec);
    if (cknotype($rdarray[$cct]) != $wtval[2]){ /// table_dataｔと 入力データが違うか
      $wtarray[$cct]='2|'.$wtval[1].'|'.$wtval[2];
    }
    $cct++;
  }
  /// action 0->1に変化したらping、
  /// action 1->2 又は3->2へ変化したらsnmp
  $wtval4=explode('|',$wtarray[4]);
  if ($wtval4[0]=='2' && $wtval4[2]=='1') {  // actionの1列目=2(inactive) and 4
    $hrc=hostping($host); // winhostping.php
    if ($hrc==1){
       $msg = '#error#'.$user.'#ホスト'.$host.'がpingに応答しない、更新無効';
       $nextpage = "HostListPage.php";
       writelogd($pgm,$msg);
       branch($nextpage,$msg);
    }
  }elseif($wtval4[0]=='2' && $wtval4[2]=='2') {
    $hrc=snmpactive($host,$comm);
    if ($hrc==1){
      $msg = "#error".$user."#ホスト".$host ."がsnmp対応でないか無応答、更新無効";
      $nextpage = "HostListPage.php";
      writelogd($pgm,$msg);
      branch($nextpage,$msg);
    }
  }
  ///  action 5(Ncat)以外から5へ変更したら
  if ($wtval4[0]=='5' && $wtval4[2]!='5') {
    $hrc=hostncat($host);
    if ($hrc==1){
       $msg = '#error#'.$user.'#ホスト'.$host.'がNcatに応答しない、更新無効';
       $nextpage = "HostListPage.php";
       writelogd($pgm,$msg);
       branch($nextpage,$msg);
    }
  }
  ///
  $host_sql="update host set result='1',";
  $svalue="";
  $issw='0';
  foreach ($wtarray as $wtrec){
    $wtval = explode('|',$wtrec);
    if ($wtval[0]=='2'){ //## update target = 2
      $svalue=$svalue.$wtval[1]. "='" .$wtval[2]. "',";
      //writeloge($pgm,'temporary '.$svalue); /////////////////
      $issw='1';
      if ($wtval[1]=='tcpport' && $trapswt=='1'){
        $trapswt = '2';
      }
      if ($wtval[1]=='process' && $trapswp=='1'){
        $trapswp = '2';
      }
      if ($wtval[1]=='process' && $trapswp2=='1'){
        $trapswp2 = '2';
      }
    }
  }
  $oksval=rtrim($svalue,',');
  $upsql=$host_sql.$oksval;
  if(substr($host,0,3)=='127'){
    $agentHost=$_GET['agenthost'];
    $upsql=$upsql.",agenthost='".$agentHost."'";
    $issw='1';
  }
  $upsql=$upsql." where host='".$host."'";
  //writeloge($pgm,$upsql);   /////////////////////////////
  if ($issw!='0'){
    putdata($upsql);
    //------------------------------------------
    // statisticsの削除と作成
    //------------------------------------------ 
    $stat_sql="delete from statistics where host='".$host."'";
    putdata($stat_sql);
    $stat_sql='insert into statistics (host,tstamp,gtype) values("'.$host.'","000000000000","9")';
    putdata($stat_sql);
    
    //------------------------------------------
    // イベントログ作成
    //------------------------------------------ 
    $eventTime = date('ymdHis');
    $eventType='6';  //内容修正
    $snmpType="";
    $snmpVal="";
    $admin=$user;
    $adminNum="";
    $cnfCls="";
    $mailSend="";
    $msg="";
    $event_sql="insert into eventlog values('".$host."','".$eventTime."','".$eventType."','".$snmpType."','".$snmpVal."','".$admin."','".$adminNum."','".$cnfCls."','".$mailSend."','".$msg."')";
    putdata($event_sql);    
  }
  ///------------------------------
  ///----------snmp tcpport & processset------
  ///------------------------------
  if ($trapswt=='2' and $action=='2'){
    $tcpPortx=mb_substr($tcpPort,1); //##top char strip
    $status=snmptcpportset($host,"remote",$tcpPortx);
    if ($status==1){
      $msg = "#error#".$user."#ホスト".$hostmei."へsnmpsetでTCPport登録失敗しました";
      $nextpage = "NewHostPage.php";
      branch($nextpage,$msg);
    } 
  }
  if ($trapswp=='2' and $action=='2'){ // &process
    $processx=mb_substr($process,1); //##top char strip
    $status=snmpprocessset($host,"remote",$processx);
    if ($status==1){
      $msg = "#error#".$user."#ホスト".$hostmei."へsnmpsetでProcess登録失敗しました";
      $nextpage = "NewHostPage.php";
      branch($nextpage,$msg);
    } 
  }
  if ($trapswp2=='2' and $action=='2'){ // %process
    $processx=mb_substr($process,1); //##top char strip
    $status=snmptrapset($host,"remote",$processx);
    if ($status==1){
      $msg = "#error#".$user."#ホスト".$hostmei."へsnmpsetでProcess登録失敗しました";
      $nextpage = "NewHostPage.php";
      branch($nextpage,$msg);
    } 
  } 
  $msg = '#notic#'.$user.'#ホスト'.$host.'情報が正常に更新されました';
  $nextpage = "HostListPage.php";
  writelogd($pgm,$msg);
  branch($nextpage,$msg);

}elseif (!isset($_GET['fradio'])){
  $msg = '#alert#'.$user.'#ホストを選択して下さい';
  $nextpage = "HostListPage.php";
  branch($nextpage,$msg);
  
}else{
///-------------------------------
///-- ホストデータ表示処理 -------
///-------------------------------
  $hostRow = $_GET['fradio'];
  $hostArr = explode(',',$hostRow);  // host fdata array
  $host = $hostArr[0]; //host 
  $groupName=$hostArr[1];
  $osType=$hostArr[2];
  $result=$hostArr[3];
  $action=$hostArr[4];
  $oldAction=$hostArr[4];
  $viewName=$hostArr[5];
  $mailOpt=$hostArr[6];
  $tcpPort=$hostArr[7];
  $cpuLim=$hostArr[8];
  $ramLim=$hostArr[9];
  $diskLim=$hostArr[10];
  $process=$hostArr[11];
  $image=$hostArr[12];
  $comm=$hostArr[13];
  $agentHost=$hostArr[14];
  ///
  print "<h2><img src='header/php.jpg' width='30' height='30'>&emsp;&emsp;▽　更新/削除 対象ホスト： {$host} 　▽</h2>";
  ///
  hostimagelist();
  ///
  print '<h4>変更する箇所のみ入力して下さい</h4>';
  print '<form name="updatedb" type="get" action="hostupdel.php">';
  print '<table border=1>';
  print '<tr><th>ホスト名</th><th>OS種類</th><th>結果</th><th>死活</th><th>表示名</th><th>メール</th><th>画像</th></tr>';
  print '<tr>';
  print "<td><input type=text name=host value={$host} readonly></td>";
  $selOptArr = array('','','','');
  $selOptArr[intval($osType)]="selected";
  print '<td><select name=ostype>';
  print "<option value='0'{$selOptArr[0]}>Windows</option>";
  print "<option value='1'{$selOptArr[1]}>Unix/Linux</option>";
  print "<option value='2'{$selOptArr[2]}>Gateway</option>";
  print "<option value='3'{$selOptArr[3]}>Others</option>";
  print '</select></td>';
  print "<td><input type=text name=result size=3 value={$result} readonly></td>";
  $selOptArr=array('','','','','');
  $selOptArr[intval($action)]="selected";
  print '<td><select name=action>';
  print "<option value='0'{$selOptArr[0]}>監視なし</option>";
  print "<option value='1'{$selOptArr[1]}>PING監視</option>";
  print "<option value='2'{$selOptArr[2]}>SNMP監視</option>";
  print "<option value='3'{$selOptArr[3]}>SNMP通知なし</option>";
  print "<option value='4'{$selOptArr[4]}>Agent監視</option>";
  print '</select></td>';
  $selOptArr=array('','');
  print "<td><input type=text name=viewname size=14 value={$viewName}></td>";
  $selOptArr[intval($mailOpt)]="selected";
  print '<td><select name=mailopt>';
  print "<option value='0'{$selOptArr[0]}>非送信</option>";
  print "<option value='1'{$selOptArr[1]}>自動送信</option>";
  print '</select></td>';
  $image_sql='select * from serverimage order by image';
  $imageRows=getdata($image_sql);
  $rowcnt=count($imageRows);
  print '<td><select name="image">';
  for ($cnt=0;$cnt<$rowcnt;$cnt++){
    $iitemlist=explode(',',$imageRows[$cnt]);
    if ($iitemlist[0]==$image){
      print "<option value={$iitemlist[0]} selected>{$iitemlist[1]}</option>";
    }else{
      print "<option value={$iitemlist[0]}>{$iitemlist[1]}</option>";
    }
  }
  print '</select></td>';
  print '</tr>';
  ///
  if (substr($host,0,3)!='127'){
    print '<tr><th>TCPポート</th><th>CPU警告</th><th>メモリ警告</th><th>ディスク警告</th><th colspan="2">監視プロセス</th><th>SNMPコミュニティ名</th></tr>';
    print '<tr>';
    print "<td><input type=text name=tcpport value={$tcpPort}></td>";
    print "<td><input type=text name=cpulim size=10 value={$cpuLim}></td>";
    print "<td><input type=text name=ramlim size=8  value={$ramLim}></td>";
    print "<td><input type=text name=disklim size=10  value={$diskLim}></td>";
    print "<td colspan='2'><input type=text name=process  size=30 value={$process}></td>";
    print "<td><input type=text name=comm size=10 value={$comm}></td>";
  }else{
    print '<tr><th colspan=2>監視他サイトホスト名</th><th>SNMPコミュニティ名</th><th colspan=4></th></tr>';
    print '<tr>';
    print "<td colspan=2><input type=text name=agenthost size=35 value={$agentHost}></td>";
    print "<td><input type=text name=comm size=10 value={$comm}></td>";
    print '<td colspan=4><input type=text name=dummy size=60 value=""></td>';
  }  
  /// 
  print "<input type=hidden name=fdata value={$data}>";    ##// all data
  print "<input type=hidden name=oldaction value={$oldAction}>";    ##// old action
  print '</tr>';
  print '</table>';
  ///
  if (substr($host,0,3)!='127'){
    print '<h4>ＣＰＵ警告欄&emsp;&emsp;&emsp;警告値：危険値<br>メモリ警告欄&emsp;&emsp;&emsp;警告値：危険値<br>ディスク警告欄&emsp;&emsp;警告値：危険値<br>';
    print 'ＴＣＰポート欄&emsp;&emsp;ポート番号；区切 UNIX系ホストのプライベートMIBサーバーは先頭に「&」可能<br>';
    print '監視プロセス欄&emsp;&emsp;プロセス名；区切 UNIX系ホストのプライベートMIBサーバーは先頭に「&」可能</h4>';
  } else {
    print '<h4>エージェントホスト名欄&emsp;&emsp;他監視サイトのエージェントホスト名<br>SNMPコミュニティ名欄&emsp;&emsp;エージェントホストのコミュニティ名</h4>';
  }
  print "<input type=hidden name=user value={$user}>";
  print '&emsp;&emsp;&emsp;<input class=button type="submit" name="update" value="更新実行">';
  print '</form>';
  print '<br>';
  print '<font color=red>「削除」を実行すると、ホスト情報が消えるので注意 </font><br>';
  ///
  print '<form name="deletedb" type="get" action="hostupdel.php" onSubmit="return check(\''.$host.'\';">';
  print "<input type=hidden name=user value={$user}>";
  print "<input type=hidden name=host value={$host}>";
  print "<td><input type=hidden name=fdata value={$data}></td>";  ##// all data
  print '&emsp;&emsp;&emsp;<input class=buttondel type="submit" name="delete" value="削除実行">';
  print '</form>';
  print '<br>';
}
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body></html>';

?>

