<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "serverimagedisplay.php";
require_once "winhostping.php";
require_once "phpsnmpprocessset.php";
require_once "phpsnmpactive.php";
require_once "mailsendany.php";

$pgm="hostupdel.php";

function writelogsendmail($msg,$host){
  global $pgm;
  $msg=$msg.' host '.$host;
  writelogd($pgm,$msg);
  $sql='select * from admintb';
  $rows=getdata($sql);
  $sdata=explode(',',$rows[0]);
  $toaddr=$sdata[3];
  $fromaddr=$sdata[4];
  $subj='ホスト '.$host.' 保守アラート';
  mailsendany('hostupdate',$fromaddr,$toaddr,$subj,$msg);
}
function snmpcheck($host,$ostype,$comm){
  /// 監視対象いSNMPがインストールされているかチェック
  $status=0;  
  if ($ostype=='0' || $ostype=='1' || $ostype=='2'){
    $status=snmpactive($host,$comm); // phpsnmpactive.php
    if ($status==1){
      writelogsendmail('SNMP応答チェック無応答',$host);
    }
  }
  return $status;
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
function ckcolon($_data,$host){
  if (preg_match("/,/",$_data)){
    $rdata=str_replace(',',':',$_data);
    writelogsendmail('CPU,RAM,DISK制限値をコロンへ変換',$host);
  }elseif (preg_match("/;/",$_data)){
    $rdata=str_replace(';',':',$_data);
    writelogsendmail('CPU,RAM,DISK制限値をコロンへ変換',$host);
  }else{
    $rdata=$_data;
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
print '<html><head><meta>';
print '<link rel="stylesheet" href="kanshi1.css">';
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
///------------------
///--削除処理--------
///------------------
if (isset($_GET['delete'])){
  $host=$_GET['host'];
  $user=$_GET['user'];
  $updel=$_GET['delete']; 
  $rddata=$_GET['fdata']; /// host table データ
  $rdarray=explode(',',$rddata);
  /// delete host record
  $delsql="delete from host where host='".$host."'";
  putdata($delsql);
  /// delete statistics record
  $delsql="delete from statistics where host='".$host."'";
  putdata($delsql); 
  /// イベントに残す
  /// insert eventlog
  $etime = date('ymdHis');
  $etype='4'; //削除
  $insql="insert into eventlog (host,eventtime,eventtype,kanrisha) values('".$host."','".$etime."','".$etype."','".$user."')";
  putdata($insql); 
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
  $trapsw = '0'; 
  $host=$_GET['host'];
  $user=$_GET['user'];
  $updel=$_GET['update']; //update host record to host table
  $rddata=$_GET['fdata']; // host レコードデータ
  $rdarray=explode(',',$rddata);
  $groupname='notused';
  $ostype=$_GET['ostype'];
  $action=$_GET['action'];
  $oldaction=$_GET['oldaction'];  
  $oldaction=$_GET['oldaction'];
  $comm="";
  if ($action=='2' || $action=='3'){
    if (isset($_GET['comm']) && $_GET['comm']!=""){
      $comm=$_GET['comm'];
    }else{
      $comm='public';
    }
    if ($action!=$oldaction){
      snmpcheck($host,$ostype,$comm);
    }
  }
  $view = $_GET['viewname'];
  $mailopt=$_GET['mailopt'];
  $tcpportb=cknotype($_GET['tcpport']);
  $tcpport=cksemicolon($tcpportb,$host);
  $cpulimb=ckcnotype($_GET['cpulim']);
  $cpulim=ckcolon($cpulimb,$host);
  $ramlimb=ckcnotype($_GET['ramlim']);
  $ramlim=ckcolon($ramlimb,$host);
  $disklimb=ckcnotype($_GET['disklim']);
  $disklim=ckcolon($disklimb,$host);
  $processb=cknotype($_GET['process']);
  $process=cksemicolon($processb,$host);
  if (substr($process,0,1) == '&'){
    $trapsw = '1';
  }
  if ($action=='2' || $action=='3'){
    if ($tcpport==""){
      $tcpport="22";
    }
  }
  $image=$_GET['image'];
  if ($image==''){
    if ($ostype=='0'){
      $image="pc.jpg";
    }elseif ($ostype=='1'){
      $image="server.jpg";
    }elseif ($ostype=='2'){
      $image="router.jpg";
    }else{
      $image="pc.jpg";
    }
  }
  $wtarray=array("","","","","","","","","","","","","","");
  $wtarray[0]='0|host|'.$host; 
  $wtarray[1]='0|groupname|'.$groupname; 
  $wtarray[2]='0|ostype|'.$ostype; 
  $wtarray[3]='0|result|'.$rdarray[3]; 
  $wtarray[4]='0|action|'.$action; 
  $wtarray[5]='0|viewname|'.$view; 
  $wtarray[6]='0|mailopt|'.$mailopt; 
  $wtarray[7]='0|tcpport|'.$tcpport; 
  $wtarray[8]='0|cpulim|'.$cpulim; 
  $wtarray[9]='0|ramlim|'.$ramlim; 
  $wtarray[10]='0|disklim|'.$disklim;
  if ($trapsw == '1'){ 
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
  /// action 0から1に変化したらping、action 1が2へ又は3から2へ変化したらsnmp
  $wtval4=explode('|',$wtarray[4]);
  if ($wtval4[0]=='2' && $wtval4[2]=='1') {  // actionの1列目=2(inactive) and 4
    $hrc=hostping($host); // winhostping.php
    if ($hrc==1){
       $msg = '#error#'.$user.'#ホスト'.$host.'がpingに応答しない、更新無効';
       $nextpage = "HostListPage.php";
       writelogd($pgm,$msg);
    }
  }elseif($wtval4[0]=='2' && $wtval4[2]=='2') {
    $hrc=snmpactive($host,$comm);
    if ($hrc==1){
      $msg = "#error".$user."#ホスト".$host ."がsnmp対応でないか無応答、更新無効";
      $nextpage = "HostListPage.php";
      writelogd($pgm,$msg);
    }
  }
  /// 
  $sql="update host set result='1',";
  $svalue="";
  $issw='0';
  foreach ($wtarray as $wtrec){
    $wtval = explode('|',$wtrec);
    if ($wtval[0]=='2'){ //## update target = 2
      $svalue=$svalue.$wtval[1]. "='" .$wtval[2]. "',";
      $issw='1';
      if ($wtval[1]=='process' && $trapsw=='1'){
        $trapsw = '2';
      }
    }
  }
  $oksval=rtrim($svalue,',');
  $upsql=$sql.$oksval;
  $upsql=$upsql." where host='".$host."'";
  if ($issw!='0'){
    putdata($upsql);
    //------------------------------------------
    // statisticsの削除と作成
    //------------------------------------------ 
    $delsql="delete from statistics where host='".$host."'";
    putdata($delsql);
    $insql='insert into statistics (host,tstamp,gtype) values("'.$host.'","000000000000","9")';
    putdata($insql);
    
    //------------------------------------------
    // イベントログ作成
    //------------------------------------------ 
    $etime = date('ymdHis');
    $etype='6';  //内容修正
    $stype="";
    $snmpval="";
    $kanri=$user;
    $kno="";
    $cfcl="";
    $msend="";
    $msg="";
    $insql="insert into eventlog values('".$host."','".$etime."','".$etype."','".$stype."','".$snmpval."','".$kanri."','".$kno."','".$cfcl."','".$msend."','".$msg."')";
    putdata($insql);    
  }
  ///------------------------------
  ///----------snmpprocessset------
  ///------------------------------
  if ($trapsw=='2'){
    if ($comm=='' || is_null($comm)){
      $comm="public";
    }
    $processx=mb_substr($process,1); //##top char strip
    $status=snmpprocessset($host,$comm,$processx);
    if ($status==1){
      $msg='プロセス＝'.$processx.' 登録 snmpset 無応答';
      writelogsendmail($msg,$host);
      $msg = "ホスト".$host."にプロセス登録できない、登録スキップ";
      writeloge($pgm,$msg);
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
  $data = $_GET['fradio'];
  $sdata = explode(',',$data);  // host fdata array
  $host = $sdata[0]; //host 
  $groupname=$sdata[1];
  $ostype=$sdata[2];
  $result=$sdata[3];
  $action=$sdata[4];
  $oldaction=$sdata[4];
  $viewname=$sdata[5];
  $mailopt=$sdata[6];
  $tcpport=$sdata[7];
  $cpulim=$sdata[8];
  $ramlim=$sdata[9];
  $disklim=$sdata[10];
  $process=$sdata[11];
  $image=$sdata[12];
  $comm=$sdata[13];
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
  $ot = array('','','','');
  $ot[intval($ostype)]="selected";
  print '<td><select name=ostype>';
  print "<option value='0'{$ot[0]}>Windows</option>";
  print "<option value='1'{$ot[1]}>Unix/Linux</option>";
  print "<option value='2'{$ot[2]}>Gateway</option>";
  print "<option value='3'{$ot[3]}>Others</option>";
  print '</select></td>';
  print "<td><input type=text name=result size=3 value={$result} readonly></td>";
  $ot=array('','','','','');
  $ot[intval($action)]="selected";
  print '<td><select name=action>';
  print "<option value='0'{$ot[0]}>監視なし</option>";
  print "<option value='1'{$ot[1]}>PING監視</option>";
  print "<option value='2'{$ot[2]}>SNMP監視</option>";
  print "<option value='3'{$ot[3]}>SNMP通知なし</option>";
  print "<option value='4'{$ot[4]}>Agent監視</option>";
  print '</select></td>';
  $ot=array('','');
  print "<td><input type=text name=viewname size=14 value={$viewname}></td>";
  $ot[intval($mailopt)]="selected";
  print '<td><select name=mailopt>';
  print "<option value='0'{$ot[0]}>非送信</option>";
  print "<option value='1'{$ot[1]}>自動送信</option>";
  print '</select></td>';
  $sql='select * from serverimage order by image';
  $rows=getdata($sql);
  $rowcnt=count($rows);
  print '<td><select name="image">';
  for ($cnt=0;$cnt<$rowcnt;$cnt++){
    $iitemlist=explode(',',$rows[$cnt]);
    if ($iitemlist[0]==$image){
      print "<option value={$iitemlist[0]} selected>{$iitemlist[1]}</option>";
    }else{
      print "<option value={$iitemlist[0]}>{$iitemlist[1]}</option>";
    }
  }
  print '</select></td>';
  print '</tr>';
  ///
  print '<tr><th>TCPポート</th><th>CPU警告</th><th>メモリ警告</th><th>ディスク警告</th><th colspan="2">監視プロセス</th><th>SNMPコミュニティ</th></tr>';
  print '<tr>';
  print "<td><input type=text name=tcpport value={$tcpport}></td>";
  print "<td><input type=text name=cpulim size=10 value={$cpulim}></td>";
  print "<td><input type=text name=ramlim size=8  value={$ramlim}></td>";
  print "<td><input type=text name=disklim size=10  value={$disklim}></td>";
  print "<td colspan='2'><input type=text name=process  size=30 value={$process}></td>";
  print "<td><input type=text name=comm size=10 value={$comm}></td>";
  print "<input type=hidden name=fdata value={$data}>";    ##// all data
  print "<input type=hidden name=oldaction value={$oldaction}>";    ##// old action
  print '</tr>';
  print '</table>';
  ///
  print '<h4>ＴＣＰポート欄&emsp;&emsp;ポート番号；区切 <br>ＣＰＵ警告欄&emsp;&emsp;&emsp;警告値：危険値<br>メモリ警告欄&emsp;&emsp;&emsp;警告値：危険値<br>ディスク警告欄&emsp;&emsp;警告値：危険値<br>監視プロセス欄&emsp;&emsp;プロセス名；区切、インターネット内のサーバーは先頭に「&」</h4>';
  print "<input type=hidden name=user value={$user}>";
  print '&emsp;&emsp;&emsp;<input class=button type="submit" name="update" value="更新実行">';
  print '</form>';
  print '<br>';
  print '<font color=red>*****「削除」を実行すると、ホスト情報が消えるので注意 *****</font><br>';
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

