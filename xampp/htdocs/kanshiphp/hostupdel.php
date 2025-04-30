<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "serverimagedisplay.php";
require_once "hostping.php";
require_once "phpsnmpprocessset.php";
require_once "phpsnmptrapset.php";
require_once "phpsnmptcpportset.php";
require_once "phpsnmpactive.php";
require_once "mailsend.php";
require_once "snmpagent.php";

$pgm="hostupdel.php";
$mailToAddr="";
$mailFromAddr="";

function branchtarget($_page,$_param,$_target,$_jump){
  //echo 'jump;'.$_jump.'<br>';
  print '<html lang="ja">';
  print '<meta http-equiv="Content-Type" content="text/html;charset=utf-8">';
  //print '<meta http-equiv="refresh" content="0;URL=http://localhost/kanshiphpv2/TestListPage.php?param=_notic_admin_情報が正常に更新されました">';
  //print '<meta http-equiv="refresh" content="1;TestListPage.php?param=_notic_admin_情報が正常に更新されました">';
  print "<meta http-equiv='refresh' content='0;".$_jump."'>";
  print '<body onLoad="document.F.submit();">';
  print "<form name='F' action={$_page} target={$_target} method='get'>";
  print '<input type=hidden name=param value="'.$_param.'">';
  print '<input type="submit" name="next" value="お待ち下さい...">';
  print '</form>';
  exit();
}

function writelogsendmail($_msg,$_host){
  global $pgm;
  $msg='ホスト '.$_host.' 監視管理 '.$_msg;
  writelogd($pgm,$msg);
  ///
  mailsend($_host,$user,'0','ホスト更新：削除',$_msg,'','');   
  
}
function snmpcheck($_host,$_ostype,$_comm){
  /// 監視対象にSNMPがインストールされているかチェック
  /// snmpactiveでロケーションデータをアクセス
  $status=0;  
  if ($_ostype=='0' || $_ostype=='1' || $_ostype=='2'){
    $status=snmpactive($_host,$_comm); /// phpsnmpactive.php
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
    
  }elseif (preg_match("/:/",$_data)){
    $okData=str_replace(':',';',$_data);
    
  }else{
    $okData=$_data;
  }
  return $okData;
}
function ckcolon($_data,$_host){
  $okData="";
  if (preg_match("/,/",$_data)){
    $okData=str_replace(',',':',$_data);
    
  }elseif (preg_match("/;/",$_data)){
    $okData=str_replace(';',':',$_data);
    
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
 
print '<html><head><meta>';
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
print '<script src="js/inputCheck.js"></script>';

///
/// admintb データ取得 
///
$admin_sql="select receiver,sender,snmpintval from admintb";
$adminRows=getdata($admin_sql);
$adminArr=explode(',',$adminRows[0]);
$mailToAddr=$adminArr[0];
$mailFromAddr=$adminArr[1];
$snmpintval=$adminArr[2];
///
$user = $_GET['user'];
$user_sql="select authority,bgcolor from user where userid='".$user."'";
$userRows=getdata($user_sql);
if(empty($userRows)){
  $msg="#error#unkown#ユーザを見失いました";
  branch('logout.php',$msg);
}
$userArr=explode(',',$userRows[0]);
$authority=$userArr[0];
$bgColor=$userArr[1];
///
print "</head><body class={$bgColor}>";

///------------------------------
///--ホストデータ削除処理--------
///------------------------------
if (isset($_GET['delete'])){
  $host=$_GET['host'];
  $user=$_GET['user'];
  $updel=$_GET['delete']; 
  /// delete host record
  $host_sql="delete from host where host='".$host."'";
  putdata($host_sql);
  /// delete statistics record
  $stat_sql="delete from statistics where host='".$host."'";
  putdata($stat_sql); 
  /// イベントに残す
  /// insert eventlog
  $eventTime = date('ymdHis');
  $eventType='4'; ///削除
  $event_sql="insert into eventlog (host,eventtime,eventtype,kanrisha) values('".$host."','".$eventTime."','".$eventType."','".$user."')";
  putdata($event_sql); 
  /// 実行通知　ホスト一覧の前に
  $msg = '#notic#'.$user.'#ホスト'.$host .'が正常に削除されました';
  $nextpage = "HostListPage.php";
  writelogd($pgm,$msg);
  branch($nextpage,$msg);
  //exit;
///-------------------------------
///-- ホストデータ更新処理 -------
///-------------------------------
}elseif (isset($_GET['update'])){
  $host=$_GET['host'];
  $user=$_GET['user'];
  $updel=$_GET['update']; ///update host record to host table
  $hostRow=$_GET['fdata']; /// host レコードデータ
  $hostArr=explode(',',$hostRow);
  $groupName='notused';
  $osType=$_GET['ostype'];
  $action=$_GET['action'];
  $oldAction=$_GET['oldaction']; 
  $ngMsg="";
  $comm="";
  if ($action=='2' or $action=='3'){
    /// action=2(SNMP監視),3(SNMP通知なし),4(Agent監視)
    if (is_null($_GET['comm']) or empty($_GET['comm'])){
      $msg = '#error#'.$user.'#ホスト'.$host.'SNMPコミュニティの設定なし、更新無効';
      $nextpage = "HostListPage.php";
      writelogd($pgm,$msg);
      branch($nextpage,$msg);
    }else{
      $comm=$_GET['comm'];
      if ($action!=$oldAction){
        /// Action変更、SNMP監視試行
        $rtnck=snmpcheck($host,$osType,$comm);
        if ($rtnck!=0){
          $ngMsg=" 但し、snmpアクセス不可";
        }
      }
    }
  }elseif($action=='4' and $_GET['comm']==""){
    $comm="private";    
  }else{ /// action=0(監視なし),1(Ping監視),5(Ncat監視)
    if (isset($_GET['comm']) and $_GET['comm']!=""){
      $comm=$_GET['comm'];
    }
  }
  /// 更新共通
  $viewName = $_GET['viewname'];
  $mailOpt=$_GET['mailopt'];
  $eventLog=$_GET['eventlog'];
  $tcpPortb=cknotype($_GET['tcpport']);
  $tcpPort=cksemicolon($tcpPortb,$host);
  /// TCP拡張機能チェック 
  if (substr($tcpPort,0,1) == '&'){
    if ($osType!='1'){
      $msg = "#error#".$user."#ホスト".$hostmei."のWindowsでは使えません";
      $nextpage = "NewHostPage.php";
      branch($nextpage,$msg);
    }    
  }
  $cpuLimb=ckcnotype($_GET['cpulim']);
  $cpuLim=ckcolon($cpuLimb,$host);
  $ramLimb=ckcnotype($_GET['ramlim']);
  $ramLim=ckcolon($ramLimb,$host);
  $diskLimb=ckcnotype($_GET['disklim']);
  $diskLim=ckcolon($diskLimb,$host);
  $processb=cknotype($_GET['process']);
  $process=cksemicolon($processb,$host);
  /// プロセス拡張機能チェック
  if (substr($process,0,1) == '&'){
    if ($osType!='1'){
      $msg = "#error#".$user."#ホスト".$hostmei."のWindowsでは使えません";
      $nextpage = "NewHostPage.php";
      branch($nextpage,$msg);
    }
    
  }
  if (substr($process,0,1) == '%'){
    $msg = "#error#".$user."#この機能は使えません";
    $nextpage = "NewHostPage.php";
    branch($nextpage,$msg);
  }
  ///
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
  ///
  /// Flag|列名|データの配列
  $wtarray=array("","","","","","","","","","","","","","","");
  $wtarray[0]='0|host|'.$host; 
  $wtarray[1]='0|groupname|'.$groupName; 
  $wtarray[2]='0|ostype|'.$osType; 
  $wtarray[3]='0|result|'.$hostArr[3]; 
  $wtarray[4]='0|action|'.$action; 
  $wtarray[5]='0|viewname|'.$viewName; 
  $wtarray[6]='0|mailopt|'.$mailOpt;
  $wtarray[7]='0|tcpport|'.$tcpPort;  ///
  $wtarray[8]='0|cpulim|'.$cpuLim;    ///
  $wtarray[9]='0|ramlim|'.$ramLim;    ///
  $wtarray[10]='0|disklim|'.$diskLim; ///
  $wtarray[11]='0|process|'.$process; ///
  $wtarray[12]='0|image|'.$image;
  $wtarray[13]='0|snmpcomm|'.$comm;
  $wtarray[14]='0|eventlog|'.$eventLog;
  /// 
  /// ホストデータの配列と更新データを比較、相違あれば更新のFlag列へ２をセット
  $cct=0;
  foreach ($wtarray as $wtrec){  
    $wtval=explode('|',$wtrec);
    if (cknotype($hostArr[$cct]) != $wtval[2]){ /// table_dataｔと 入力データが違うか
      $wtarray[$cct]='2|'.$wtval[1].'|'.$wtval[2];
    }
    $cct++;
  }
  /// standby check
  $standbySw=0;
  for($idx=7;$idx<12;$idx++){
    $wtArrFlg=explode('|',$wtarray[$idx]);
    if($wtArrFlg[0]=='2'){
      $standbySw=1;
    }
  }
  /// update sql作成
  $host_sql="update host set result='1',";
  $svalue="";
  $updatesw='0';
  foreach ($wtarray as $wtrec){
    $wtval = explode('|',$wtrec);
    if ($wtval[0]=='2'){ /// update flag = 2
      /// 列名=値作成
      $svalue=$svalue.$wtval[1]. "='" .$wtval[2]. "',";
      $updatesw='1'; /// 更新スイッチオン
      
    }
  }
  /// 2025/2/10 追加 
  if($standbySw==1){
    $oksval=$svalue."standby='1'";	/// $standby列を'1'にしてstandby状態にする
    $agent_sql="update host set standby='1' where host='127.0.0.1'";
    putdata($agent_sql); 
  }else{
    $oksval=rtrim($svalue,',');
  }
  /// 一般監視ホストのupdate sql
  $upsql=$host_sql.$oksval;
  /// 127.0.0.1の場合は別のSql
  if(substr($host,0,3)=='127'){
    $agentHost=$_GET['agenthost'];
    $upsql=$upsql.",agenthost='".$agentHost."'";
    $updatesw='1';
  }
  /// sql完成
  $upsql=$upsql." where host='".$host."'";
  if ($updatesw!='0'){
    /// ホスト更新実行　　
    putdata($upsql);
    ///------------------------------------------
    /// statisticsの削除と作成
    ///------------------------------------------ 
    $stat_sql="delete from statistics where host='".$host."'";
    putdata($stat_sql);
    $stat_sql='insert into statistics (host,tstamp,gtype) values("'.$host.'","000000000000","9")';
    putdata($stat_sql);
    
    ///------------------------------------------
    /// イベントログ作成
    ///------------------------------------------ 
    $eventTime = date('ymdHis');
    $eventType='6';  ///内容修正
    $snmpType="";
    $snmpVal="";
    $admin=$user;
    $adminNum="";
    $cnfCls="";
    $mailSend="";
    $msg="";
    $event_sql="insert into eventlog values('".$host."','".$eventTime."','".$eventType."','".$snmpType."','".$snmpVal."','".$admin."','".$adminNum."','".$cnfCls."','".$mailSend."','".$msg."')";
    putdata($event_sql); 
    ///-----------------------------------
    /// send mail
    ///-----------------------------------
    $msg='ホスト '.$host.' 監視管理 ';
    mailsend($host,$user,'0','ホスト更新','内容修正','','');   
    /// 
  }
  ///--------------------------------------------------------------------------
  ///------&付きtcpportとprocessの拡張機能を監視対象ホストでsnmpsetでセット------
  ///--------------------------------------------------------------------------
  $tcpext=explode('|',$wtarray[7]);
  if ($tcpext[0]=='2' and  substr($tcpPort,0,1)=='&' and ($action=='2' or $action=='3')){
    $status=snmptcpportset($host,$comm,substr($tcpPort,1));
    if ($status==1){
      $msg = "#alert#".$user."#ホスト".$hostmei."へsnmpsetで拡張機能TCPport登録失敗しました";
      $nextpage = "HostListPage.php";
      branch($nextpage,$msg);
    } 
  }
  $procext=explode('|',$wtarray[11]);
  if ($procext[0]=='2' and substr($process,0,1)=='&' and ($action=='2' or $action=='3')){ /// &process
    $status=snmpprocessset($host,$comm,substr($process,1));
    if ($status==1){
      $msg = "#alert#".$user."#ホスト".$hostmei."へsnmpsetで拡張機能Process登録失敗しました";
      $nextpage = "HostListPage.php";
      branch($nextpage,$msg);
    } 
  }
  ///----------------------------------------------------
  /// 127.0.0.1, snmp Agent および admintb standbyセット
  ///----------------------------------------------------
  $host_sql="select snmpcomm from host where host='127.0.0.1'";
  $hostRows=getdata($host_sql);
  if(empty($hostRows)){
    $msg='#alert#'.$user.'#エージェントホスト127.0.0.1がありません';
    $nextpage = "HostListPage.php";
    branch($nextpage,$msg);
  }
  putagent('127.0.0.1',$hostRows[0],'sb');
  $stat_sql="update statistics set agent='sb' where host='127.0.0.1'";
  $statRows=putdata($stat_sql);
   
  $msg = '#notic#'.$user.'#ホスト'.$host.'情報が正常に更新されました'.$ngMsg;
  $nextpage = "HostListPage.php";
  writelogd($pgm,$msg);
  //branch($nextpage,$msg);
  
  //// 変更を即反映させるため、コアとマネージャを起動
  $msgConv='_notic_'.$user.'_ホスト'.$host.'情報が正常に更新されました'.$ngMsg;
  branchtarget('MonitorCoreAuto.php',$user,'core','HostListPage.php?param='.$msgConv);
}elseif (!isset($_GET['fradio'])){
  $msg = '#alert#'.$user.'#ホストを選択して下さい';
  $nextpage = "HostListPage.php";
  branch($nextpage,$msg);
  
}else{   
///-------------------------------
///-- ホストデータ表示処理 -------
///-------------------------------
  if(isset($_GET['param'])){
    $param=$_GET['param'];
    echo 'param:'.$param.'<br>';
  } 
  $hostRow = $_GET['fradio'];
  $hostArr = explode(',',$hostRow);  /// host fdata array
  $host = $hostArr[0]; ///host 
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
  $eventLog=$hostArr[15];
  //  $debugMsg='Debug1:'.$tcpPort.' '.$cpuLim.' '.$ramLim.' '.$DiskLim.' '.$process;
  //  writeloge($pgm,$debugMsg); 
  ///
  print "<h2><img src='header/php.jpg' width='30' height='30'>&emsp;&emsp;▽　更新/削除 対象ホスト： {$host} 　▽</h2>";
  ///
  hostimagelist();
  ///
  print '<h3>変更する箇所のみ入力し、<span class=trblk>「更新実行」</span>をクリック<br>';
  print 'ホスト名は変更不可、変更する場合は削除、新規作成</3>';
  /// comma check ID
  $jsparam="viewname cpulimit ramlimit disklimit tcpport process community";
  ///
  print '<form name="updatedb" type="get" action="hostupdel.php" onsubmit="return commaCheck(\''.$jsparam.'\');">';
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
  $selOptArr=array('','','','','','');
  $selOptArr[intval($action)]="selected";
  print '<td><select name=action>';
  print "<option value='0'{$selOptArr[0]}>監視なし</option>";
  print "<option value='1'{$selOptArr[1]}>PING監視</option>";
  print "<option value='2'{$selOptArr[2]}>SNMP監視</option>";
  print "<option value='3'{$selOptArr[3]}>SNMP通知なし</option>";
  print "<option value='4'{$selOptArr[4]}>Agent監視</option>";
  print "<option value='5'{$selOptArr[5]}>Ncat監視</option>";
  
  print '</select></td>';
  $selOptArr=array('','');
  print "<td><input id=viewname type=text name=viewname size=14 value={$viewName}></td>";
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
    //$debugMsg='Debug2:'.$tcpPort.' '.$cpuLim.' '.$ramLim.' '.$DiskLim.' '.$process;
    //writeloge($pgm,$debugMsg); 
    /// 127 以外
    print '<tr><th>TCPポート</th><th>CPU警告</th><th>メモリ警告</th><th>ディスク警告</th><th>監視プロセス</th><th>イベントログ</th><th>SNMPコミュニティ名</th></tr>';
    print '<tr>';
    print "<td><input id=tcpport type=text name=tcpport value={$tcpPort}></td>";
    print "<td><input id=cpulimit type=text name=cpulim size=10 value={$cpuLim}></td>";
    print "<td><input id=ramlimit type=text name=ramlim size=8  value={$ramLim}></td>";
    print "<td><input id=disklimit type=text name=disklim size=10  value={$diskLim}></td>";
    print "<td><input id=process type=text name=process  size=14 value={$process}></td>";
    $selOptArr=array('','');
    $selOptArr[intval($eventLog)]="selected";
    print '<td><select name=eventlog>';
    print "<option value='0'{$selOptArr[0]}>ログ出力</option>";
    print "<option value='1'{$selOptArr[1]}>snmpログ抑止</option>";
    print '</select></td>';
    print "<td><input id=community type=text name=comm size=10 value={$comm}></td>";
  }else{
    /// 127.0.0.x
    print '<tr><th colspan=2>監視他サイトホスト名</th><th>SNMPコミュニティ名</th><th colspan=4></th></tr>';
    print '<tr>';
    print "<td colspan=2><input type=text name=agenthost size=35 value={$agentHost}></td>";
    print "<td><input id=community type=text name=comm size=10 value={$comm}></td>";
    print '<td colspan=4><input type=text name=dummy size=60 value=""></td>';
    /// dummy
    print "<input type=hidden name=tcpport value=''>";
    print "<input type=hidden name=cpulim  value=''>";
    print "<input type=hidden name=ramlim  value=''>";
    print "<input type=hidden name=disklim value=''>";
    print "<input type=hidden name=process value=''>";
    print "<input type=hidden name=eventlog value='0'>";
  }  
  /// 
  print "<input type=hidden name=fdata value={$hostRow}>";    /// all data
  print "<input type=hidden name=oldaction value={$oldAction}>";    /// old action
  print '</tr>';
  print '</table>';
  ///
  if (substr($host,0,3)!='127'){
    print '<h3>☆CPU警告欄&emsp;&emsp;&emsp;警告値：危険値<br>';
    print '☆メモリ警告欄&emsp;&emsp;警告値：危険値<br>';
    print '☆ディスク警告欄&emsp;警告値：危険値<br>';
    print '☆TCPポート欄&emsp;&emsp;ポート番号；区切<br>'; 
    print '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;UNIX系監視対象ホストをプライベートMIB方式で行うには先頭に「&」を付与<br>';
    print '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;監視対象ホストをNCAT方式で行うには1つのポートを指定<br>';
    print '☆監視プロセス欄&emsp;プロセス名；区切<br>';
    print '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;UNIX系監視対象ホストをプライベートMIB方式で行うに先頭に「&」を付与</h3>';
    
  } else {
    print '<h4>エージェントホスト名欄&emsp;&emsp;他監視サイトのエージェントホスト名<br>SNMPコミュニティ名欄&emsp;&emsp;エージェントホストのコミュニティ名</h4>';
  }
  print "<input type=hidden name=user value={$user}>";
  print '&emsp;&emsp;&emsp;<input class=button type="submit" name="update" value="更新実行">';
  print '</form>';
  print '<br>';
  print '<font color=red>「削除」を実行すると、ホスト情報が消えるので注意 </font><br>';
  ///
  print '<form name="deletedb" type="get" action="hostupdel.php" onSubmit="return deleteHost(\''.$host.'\');">';
  print "<input type=hidden name=user value={$user}>";
  print "<input type=hidden name=host value={$host}>";
  print "<td><input type=hidden name=fdata value={$hostRow}></td>";  /// all data
  print '&emsp;&emsp;&emsp;<input class=buttondel type="submit" name="delete" value="削除実行">';
  print '</form>';
  print '<br>';
}
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body></html>';

?>

