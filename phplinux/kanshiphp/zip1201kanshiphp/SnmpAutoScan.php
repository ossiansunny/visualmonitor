<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "snmpdataget.php";
require_once "snmpagent.php";
require_once "mailsendsnmp.php";
require_once "mailsendany.php";
require_once "phpsnmpprocessset.php";
require_once "phpsnmptcpportset.php"; 

$user="";
$brcode="";
$brmsg="";

$snmpGType="0";
$kanriuser="";
$host_actioni="";
$agentFlag="0";
$pgm='SnmpAutoScan.php';
$startCoreTime=time();
$numberOfHost=0;
$snmpNewGType="";
$snmpNewValue=array();
////
function agentprocessset($host,$process,$community){
  if (substr($process,0,1)=='&'){                    /// &httpd;sshd
    $processx=substr($process,1,strlen($process)-1); /// httpd;sshd
    $rtncd=snmpprocessset($host,$community,$processx);
  }
  return 0;
}

function agenttcpportset($host,$tcpPort,$community){
  if (substr($tcpPort,0,1)=='&'){                    /// &httpd;sshd
    $tcpPortx=substr($tcpPort,1,strlen($tcpPort)-1); /// httpd;sshd
    $rtncd=snmptcpportset($host,$community,$tcpPortx);
  }
  return 0;
}

function nullstatis($statis){
  if (is_null($statis) or $statis==''){
    return 'empty';
  } else {
    return $statis;
  }
} 

function snmpeventlog($hostrec,$stat,$snmpt,$snmpv){
  global $snmpGType;
  global $kanriuser;
  global $pgm;
  /// event log
  if (!($snmpGType=='5' or $snmpGType=='6')){
    /// $stat=2でgtype=5のとき、confclose=2にする
    $cfcl='0';
    if ($stat=='2' and $snmpGType=='5'){
      $cfcl='2';
    }
    $eventtime = date('ymdHis');
    $hostmei=$hostrec[0];
    $action=$hostrec[4];
    if ($action=='2'){
      /// host action=2 「snmp監視」のみイベントログ出力
      $eventt=strval(intval($snmpt)+1);
      $insql = "insert into eventlog (host,eventtime,eventtype,snmptype,snmpvalue,kanrisha,kanrino,confclose) values('".$hostmei."','".$eventtime."','".$stat."','".$eventt."','".$snmpv."','" .$kanriuser. "','','" .$cfcl. "')";
      putdata($insql); 
      $msg ="トレース情報 ".$hostmei." イベントログ作成 sql: " .$insql;
      writelogd($pgm,$msg);
    }
  }
}
///
function snmpmailsend($hostrec,$stat,$snmpt,$snmpv,$stat2){
  global $pgm;
  global $host_action;
  if ( $host_action != "3") {
    /// host_action=3 「snmp通知なし」はメール未送出
    $hostmei=$hostrec[0];
    $mailopt=$hostrec[6];
    if ($mailopt =='1'){
      $rtcd=mailsendsnmp($hostrec,$snmpt,$snmpv,$stat2);
      if ($rtcd==1){
        $mailerror='注意情報 ".$hostmei." イベントメール失敗、メールサーバ要チェック';
        writeloge($pgm,$mailerror);
      }     
    }
  }
}

////////////////////////////////////////////////////////
/// メイン処理
////////////////////////////////////////////////////////
if(!isset($_GET['param'])){ /// ユーザ取得依頼
  
  print '<html>';
  print "<body bgcolor=khaki>";
  print '<h4><font color=gray>お待ち下さい....</font></h4>';
  print "</body></html>";  
  paramGet($pgm);
  ///
}else{     
  paramSet();

  $user_sql='select bgcolor from user where userid="'.$user.'"';
  $userRows=getdata($user_sql);
  $bgcolor=$userRows[0];
  ///
  $currentTimeStamp = date('ymdHis');
  $admin_sql='select * from admintb';
  $adminRows=getdata($admin_sql);
  $adminArr=explode(',',$adminRows[0]);
  $kanriuser=$adminArr[0];
  $kanripass=$adminArr[1];  /// ここは初期インターバルで使用
  $monintval=$adminArr[7];
  $debug=$adminArr[9];
  $snmpintval=$adminArr[8];
  ///$standby=$adminArr[15];
  ///$saveIntVal=$adminArr[16];
  if ($snmpintval==0 or $snmpintval< $monintval/2){
    $snmpintval=$monintval;
  }
  /// snmpautoscan interval
  //$snmpintvalstr=strval($snmpintval);
  $snmpNowTime=sprintf('%s',time());
  $snmpNowInt=intval($snmpNowTime)-120;
  if (intval($kanripass) > $snmpNowInt){  /// ログインから2分間は、20秒間隔
    $snmpintvalstr=20;
  }else{
    $snmpintvalstr=strval($snmpintval);
    $coremax=strval(intval($snmpintval / ($monintval / 2)));
    $sql='update admintb set snmpintval='.$snmpintvalstr.', coreoldctr='.$coremax.', corenewctr='.$coremax;
    putdata($sql);
  }
  echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
  echo '<html lang="ja">';
  echo '<head >';
  echo "<meta http-equiv='refresh' content={$snmpintvalstr}>";
  echo '<link rel="stylesheet" href="css/CoreMenu.css">';
  echo '</head>';
  echo "<body class={$bgcolor}>";
  echo '<div><table><tr><td>';
  echo "<h4><font color=white>SNMP Refresh {$snmpintvalstr}sec</font></h4>";
  echo '</td></tr></table></div>';
  ///
  /// host layout 読み込み
  ///
  $layout_sql='select host from layout where NOT (host="NoAssign" or host="No Assign" or host="")';
  $layoutRows=getdata($layout_sql);
  ///
  /// レイアウトホスト処理開始
  ///
  foreach ($layoutRows as $layoutRowsRec) {
    $layoutArr=explode(',',$layoutRowsRec);
    /// 
    /// host layoutに従い、ホストデータ読み込み
    /// 
    $host_sql="select * from host where host='".$layoutArr[0]."'";
    $hostRows=getdata($host_sql);
    $startostTime="";
    if (isset($hostRows)){
      $snmpNewValue=[]; /// ホスト毎中身をクリア
      $hostRowsRec=$hostRows[0];
      $hostArr=explode(',',$hostRowsRec);
      ///[0]:host [1]:groupname [2]:ostype [3]:result [4]:action [5]:viewname [6]:mailopt
      ///[7]:tcpport [8]cpulim [9]:ramlim [10]:disklim [11]:process [12]image [13]snmpcomm
      ///[14]:agenthost [15]:eventlog
      if ($debug=='7'){
        $startHostTime=time();
        $numberOfHost++;
      }
      $host=$hostArr[0];
      $host_result=$hostArr[3];
      $host_action=$hostArr[4];
      ///$mailOpt=$hostArr[6];
      $tcpPort=$hostArr[7];
      ///$cpuLim=$hostArr[8];
      ///$ramLim=$hostArr[9];
      ///$diskLim=$hostArr[10];
      $process=$hostArr[11];
      $community=$hostArr[13];
      $agenthost=$hostArr[14];
      $eventlog=$hostArr[15];
      /// 
      /// statistics読み
      $sql="select * from statistics where host='".$host."'"; 
      $statRows = getdata($sql);
      $currentTimeStamp = date('ymdHis');
      $snmpGType = '';
      $snmpAllOk='';
      if (empty($statRows)) {
        ///
        /// statistics無ければ作成,Agentへプロセス、ポートセット
        if ($host==""){
          writeloge($pgm,'注意情報 statistics作成中、ホスト名が空白を発見、layout要チェック');
        }else{
          $stat_sql="insert into statistics (host,tstamp,gtype) values('".$host."','".$currentTimeStamp."','9')";
          putdata($stat_sql); 
          $logMsg = 'トレース情報 statisticsデータ無いので、新規作成 sql: ' . $insql;
          writelogd($pgm,$logMsg);
          $statArr = array("","000000000000","0","","","","","","");
          $statArr[0] = $host;
          $statArr[1] = $currentTimeStamp;
          $snmpGType = '9';
          agentprocessset($host,$process,$community); 
          agenttcpportset($host,$tcpPort,$community);
        }
      } else {
        ///
        /// statisticsあれば読み込み,gtype='9'ならばAgentへプロセス、ポートセット
        $statArr = explode(',',$statRows[0]);
        /// statisticsのgtypeをセット
        $snmpGType=$statArr[2];
        if ($snmpGType=='9'){
          agentprocessset($host,$process,$community);
          agenttcpportset($host,$tcpPort,$community);
        }
      }
      //////////////////////////////////////////////////////////////////
      /// gtype=5の場合、statistics gtype=6にし、eventlog 作成、メール送信
      if ($snmpGType=="5"){
        $host=$statArr[0];
        $stat_sql="update statistics set tstamp='".$currentTimeStamp."',gtype='6' where host='" .$host. "'";
        putdata($stat_sql);
        /// 確認済eventlog作成 
        $eventtime = date('ymdHis');
        $event_sql = "insert into eventlog (host,eventtime,eventtype,snmptype,snmpvalue,kanrisha,confclose) values('".$host."','".$eventtime."','3','7','7','".$kanriuser."','2')";
        putdata($event_sql);
        $msg = "トレース情報 ".$hostmei." 確認済イベントログ作成 sql: " .$insql;
        writelogd($pgm,$msg); 
        $confsub="ホスト：".$host." 障害確認済";
        $confbody="障害管理番号でクローズ処理待ち"; 
        mailsendany("adminsubject","","",$confsub,$confbody);
        continue;
      //////////////////////////////////////////////////////////////////
      }elseif($snmpGType=="6"){
        continue;
      }elseif($snmpGType=="0"){ /// 0:未監視
        $host_result="1";
      }
      if (($host_action=="2" or $host_action=="3") and $host_result=="1"){ 
        /// host データのsnmp監視でping結果正常の場合のみsnmpデータ取得をする
        /// hostdata Action=2(snmp) or Action=3(snmp通知なし) and Result=1(ping結果OK)
        $s_host = $statArr[0];
        $s_stamp = $statArr[1];      
        $s_cpuval = nullstatis($statArr[3]);  /// statistics null値の場合　'empty'を入れる
        $s_ramval = nullstatis($statArr[4]);
        $s_agent = $statArr[5];
        if (is_null($statArr[5])){
          $s_agent = '';
        }
        $s_diskval = nullstatis($statArr[6]);
        $s_process = nullstatis($statArr[7]);
        $s_tcpport = nullstatis($statArr[8]);  
        $snmpOldValue=array();

        $snmpOldValue[0]=$s_host; ///host
        $snmpOldValue[1]=$s_cpuval; ///cpu
        $snmpOldValue[2]=$s_ramval; ///ram
        $snmpOldValue[3]=$s_diskval; ///disk
        $snmpOldValue[4]=$s_process; ///process
        $snmpOldValue[5]=$s_tcpport; ///tcpport
      
////  ------------------------------------------------------------------------
        /// snmpデータ取得 start ////////////////////////////////////////////////
        $snmpNewValue = snmpdataget($hostArr); 
        /// snmpデータ取得 end   ////////////////////////////////////////////////
////  ------------------------------------------------------------------------
        /// snmpdatagetでのエラーは各項目「unknown」が返される ////////////
        ///////////////////////////////////////////////////////////////////
        $currentTimeStamp = date('ymdHis');
        /// snmpNewValue=snmpdataget 処理データ配列
        /// snmpNewValue[0] = host  
        /// snmpNewValue[1] = CPU    
        /// snmpNewValue[2] = RAM    
        /// snmpNewValue[3] = Disk   
        /// snmpNewValue[4] = Process
        /// snmpNewValue[5] = Port
        ///
        /// snmpデータ取得後チェック
        ///
        for($cc=0;$cc<6;$cc++){
          /// unknownの時、前回と同じ値をセット
          if (preg_match("/unknown/",$snmpNewValue[$cc])){
            $snmpNewValue[$cc]=$snmpOldValue[$cc];
          }
        }
        $snmpOldCount = count($snmpOldValue);  
        ///snmpOldValue=statistics, snmpNewValue=測定値
////
        for ($scc=1;$scc<$snmpOldCount;$scc++){
          /// ------------------------------
          /// 配列データ処理 [0]から[5]まで
          /// ------------------------------
          $old_val=$snmpOldValue[$scc];
          if ($snmpNewValue[$scc]==''){
            $new_val='empty';
          }else{
            $new_val=$snmpNewValue[$scc];
          }
          $snmpAllOk='0';
////
          /// -----------------
          /// 配列の各項目比較
          /// -----------------          
          if ($scc<4){
            ///-------------------------------------- 
            ///scc:1 cpu, scc:2 ram, scc:3 disk　処理
            ///--------------------------------------          
            $oldValS=substr($old_val,0,1);
            $newValS=substr($new_val,0,1);
            if ($oldValS != $newValS){ 
              ////cpu, ram disk状態変化ありの処理　変化の状態をイベントログ出力            
              if ($new_val=='empty' or $newValS=='n') {
                snmpeventlog($hostArr,"1",strval($scc),$new_val);
                snmpmailsend($hostArr,"1",strval($scc),$new_val,"1");
              } else {
                $snmpAllOk='1';
                snmpeventlog($hostArr,"2",strval($scc),$new_val);
                snmpmailsend($hostArr,"2",strval($scc),$new_val,"2");
              }            
            }else{
              /// cpu ram disk 状態変化なし処理　異常でログ出力の場合イベントログ出力
              if (!($new_val=='empty' or $newValS=='n')) {
                $snmpAllOk='1';
                /// CPU,RAM,Diskの状態はnormal 以外はログとメー>ル
                if ($eventlog=='0'){
                  /// $event=1 のとき、同じ状態が続く場合、イベントログを出さない
                  snmpeventlog($hostArr,'2',strval($scc),$new_val);
                  snmpmailsend($hostArr,'2',strval($scc),$new_val,'2');
                }
              }
            }
          }else{
            ///------------------------------------ 
            /// scc:4=process or scc:5==port 処理
            ///------------------------------------ 
            if ($old_val != $new_val){ 
              ////process, port 状態変化あり処理　変化の状態をイベントログ出力
              if ($new_val=='empty' or $new_val=='allok') {
                snmpeventlog($hostArr,"1",strval($scc),$new_val); 
                snmpmailsend($hostArr,"1",strval($scc),$new_val,"1");
              } else {
                $snmpAllOk='1';
                $agentFlag='1';
                snmpeventlog($hostArr,"2",strval($scc),$new_val); 
                snmpmailsend($hostArr,"2",strval($scc),$new_val,"2");
              }
            }else{
              /// process, port 状態変化なし処理　異常でログ出力の場合イベントログ出力
              if (!($new_val=='empty' or $new_val=='allok')){
                $snmpAllOk='1';
                $agentFlag='1';
                /// Process,TCPPort欄がempty,allok以外はイベントログとメール出力
                if ($eventlog=='0'){
                  /// 但し、$eventlog=0は出力,$eventlog=1出さない
                  snmpeventlog($hostArr,'2',strval($scc),$new_val);
                  snmpmailsend($hostArr,'2',strval($scc),$new_val,'2');             
                }
              }
            } /// scc=4,5 end 
          }        
        } 
      }else{ 
        /// action=2,3(snmp監視、snmp監視通知なし）および　前回正常以外 
        /// 前回正常はホストに対してpingでの確認結果
        if($snmpGType=="9"){
          /// standbyのとき、snmp gtype=1 無応答にする
          $stat_sql="update statistics set tstamp='".$currentTimeStamp."',gtype='1' where host='" .$host. "'";
          putdata($stat_sql);
        }
      }
      ///
      /// 全データ共通（ping監視、snmp監視共通)
      ///
      if ($debug=='7'){
        /// 処理時間測定
        $elapsTime=time()-$startHostTime;
        $elapsHost=$host.':'.strval($elapsTime).'sec';
        writeloge($pgm,$elapsHost);
      }
      ///
      /// statistics レコード更新
      ///  
    
      if ($snmpNewValue[0]!='') {
        if ($snmpAllOk==0) {
          $snmpNewGType='3';
        }else{
          $snmpNewGType='4';
        }    
        $stat_sql="update statistics set tstamp='".$currentTimeStamp."'";
        $stat_sql=$stat_sql.",gtype='".$snmpNewGType."'";
        $stat_sql=$stat_sql.",cpuval='".$snmpNewValue[1]."'";
        $stat_sql=$stat_sql.",ramval='".$snmpNewValue[2]."'";
        $stat_sql=$stat_sql.",agent='".$s_agent."'";
        $stat_sql=$stat_sql.",diskval='".$snmpNewValue[3]."'";
        $stat_sql=$stat_sql.",process='".$snmpNewValue[4]."'";
        $stat_sql=$stat_sql.",tcpport='".$snmpNewValue[5]."'";
        $stat_sql=$stat_sql." where host='".$snmpNewValue[0]."'";
        putdata($stat_sql);      
      }
    } 
    /// ホストデータ処理終了
  } 
  /// レイアウト処理終了
  ///
  /// エージェントホストへok/ngを登録
  ///
  $agent_sql="select host from host where host='127.0.0.1'";
  $agentRows=getdata($agent_sql);
  $agentValue='';
  /// 127.0.0.1が存在する場合のみ処理
  if (isset($agentRows)) {
    if ($agentFlag=='0'){
      /// sysLocationがngだったらng、 okだったらok
      //$astat=getagent('127.0.0.1','private');
      //if ($astat=='ng' or $astat=='sb'){
        //$agentValue='ng';
      //}else if($astat=='ok'){
        $agentValue='ok';
      //}
    }else{
      $agentValue='ng';
    }
    putagent('127.0.0.1','private',$agentValue); 
    ///syslocationへValueセットおよびstataisticsへValueセット
    $stat_sql='update statistics set tstamp="'.$currentTimeStamp.'", agent="'.$agentValue.'" where host="127.0.0.1"';
    $rtnCde=putdata($stat_sql);
    if (! empty($rtnCde)){ /// connection error || sql error || not found
      writeloge($pgm,"注意情報 127.0.0.1 statistics更新エラー sql: ".$stat_sql);
    }
  }
  ///-----------------------------------------------------
  /// admintbのstandbyが"2"の場合、"1"へ
  /// standbyが"1"の場合、"0"へそしてsaveintval値をsnmpintvalへ
  ///-----------------------------------------------------
  /*
  if ($standby=="2"){
    $admin_sql="update admintb set standby='1'";
    putdata($admin_sql);
  }else if($standby=='1'){
    $admin_sql="update admintb set standby='0', snmpintval=".$saveIntVal;
    putdata($admin_sql);
  }
  */
  ///
  $prcstamp = time();
  $proc_sql="update processtb set snmpstamp=".strval($prcstamp);
  putdata($proc_sql);
  print '</body></html>';
  /// 処理時間測定
  if ($debug=="7"){
    $elapsCoreTime=time()-$startCoreTime;
    $elapsCore='Host count='.strval($numberOfHost).' Core elaps time='.strval($elapsCoreTime).'sec';
    writeloge($pgm,"トレース情報 ".$elapsCore);
  }

}
?>
re='Host count='.strval($numberOfHost).' Core elaps time='.strval($elapsCoreTime).'sec';
    writeloge($pgm,"トレース情報 ".$elapsCore);
  }

}
?>
