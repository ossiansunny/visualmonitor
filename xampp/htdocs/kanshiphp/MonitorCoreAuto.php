<?php
date_default_timezone_set('Asia/Tokyo');
require_once 'BaseFunction.php';
require_once 'hostping.php';
require_once 'mysqlkanshi.php';
require_once 'mailupdown.php';  
require_once 'snmpagent.php';
require_once 'phpsnmpprocessset.php';
require_once 'phpsnmptcpportset.php';

$debug="";
$agentFlag="";
$adminNum="";
$user="";
$brcode="";
$brmsg="";
$pgm = "MonitorCoreAuto.php";
$startCoreTime=time();
$numberOfHost=0;

function agentcheck(){
  $rtnCde='0';
  $layout_sql='select * from layout';
  $layoutRows=getdata($layout_sql);
  $host_sql='select host,action,result from host'; /// layoutにあるもの
  $hostRows=getdata($host_sql);
  foreach ($hostRows as $hostRowsRec){
    $hostArr=explode(',',$hostRowsRec);
    $host=$hostArr[0];  /// host
    $action=$hostArr[1]; /// action
    $result=intval($hostArr[2]); /// result=0 OK, result>1 NG
    foreach ($layoutRows as $layoutRowsRec){
      $layoutArr=explode(',',$layoutRowsRec);
      if ($layoutArr[1]==$host){
        if ($action=='1' || $action=='2' || $action=='3'){ /// action=ping snmp snmp
          if ($result>1){ /// not ok
            $rtnCde='1';  /// not ok
            break;
          }
        }
      }
    }
  }
  return $rtnCde;
}

/// 未使用
/*
function agentcheckx(){
  $rtnCde='0';
  $host_sql='select result,action from host'; /// layoutにあるもの
  $hostRows=getdata($host_sql);
  $hostCount=count($hostRows);
  for ($i=0;$i<$hostCount;$i++){
    $hostArr=explode(',',$hostRows[$i]);
    $action=$hostArr[1];  /// action
    $result=intval($hostArr[0]); /// result=0 OK, result>1 NG
    if ($action=='1' || $action=='2' || $action=='3'){ /// action=ping snmp snmp
      if ($result>1){ /// not ok
        $rtnCde='1';  /// not ok
        break;
      }
    }
  }
  return $rtnCde;
}
*/
function resultdbupdate($_hostmei,$_cde){
  $host_sql = "update host set result='" .$_cde. "' where host='" .$_hostmei. "'";
  $msg = $_hostmei . " Update sql: " . $host_sql;
  writelogd("resultdbupdate",$msg);
  putdata($host_sql);  
}

function eventlog($_hostArr,$_cde){ 
  global $pgm; 
  global $adminNum;
  global $user;
  $snmpType="";
  $adminNum="";
  $cnfClose="0";
  if ($_cde=="3"){ /// event_type 3 = 監視管理
    $cnfClose="2"; /// 確認済
  }  
  $hostName = $_hostArr[0];
  $action = $_hostArr[4];  
  if ($action=='2'){ /// action=2(snmp ping)
    $snmpType="1";
  }else{
    $snmpType="0";
  }
  $timeStamp=date('ymdHis'); 
  $event_sql = "insert into eventlog(host,eventtime,eventtype,snmptype,snmpvalue,kanrisha,kanrino,confclose,message) values('".$hostName."','".$timeStamp."','".$_cde."','".$snmpType."',' ','".$user."','".$adminNum."','".$cnfClose."',' ')";
  $msg = $hostName . " Eventlog Insert sql: " . $event_sql;  
  writelogd($pgm,$msg);
  putdata($event_sql);  
}
///----------------viewscan-------------------------------
///--------------------------------------------------------
/// レイアウト上のすべてのホストをチェック
///
function viewscan(){
  global $pgm;  
  global $agentFlag;
  global $numberOfHost;
  global $debug;
  $agentFlag="0"; /// agentFlag reset to 0, normal  
  writelogd($pgm,"viewscan関数開始");
  /// get host layout
  $layout_sql='select host from layout where NOT (host="No Assign" or host="")';
  $layoutRows=getdata($layout_sql);
  $layoutCount=count($layoutRows);
  $elapsArr=array();
  for ($i=0;$i<$layoutCount;$i++){
    /// start each host
    if ($debug=="7"){
      $startHostTime=time();
      $numberOfHost++;
    }
    ///
    $layoutArr=explode(',',$layoutRows[$i]);
    $host_sql='select * from host where host="'.$layoutArr[0].'"';
    $hostRows=getdata($host_sql);
    $hostCount=count($hostRows);
    for ($j=0;$j<$hostCount;$j++){
      /// 各ホストチェック
      $hostRowsRec=$hostRows[$j];
      $hostArr=explode(',',$hostRowsRec);
      $c_host=$hostArr[0];
      $c_result=$hostArr[3];
      $c_action=$hostArr[4];
      $c_mailopt=$hostArr[6];
      $c_tcpport=$hostArr[7];
      $c_process=$hostArr[11];
      $c_comm=$hostArr[13];
      ///        
      /// action 1(ping),2(snmp),3(snmp),4(agent)の場合 
      ///
      if ($c_action=='1' or $c_action=="2" or $c_action=="3" or $c_action=="4" or $c_action=="5"){  /// action=1(ping),2(snmp),3(snmp通知なし),4(agent監視)
        /// 前回結果 正常　result=1
        if ($c_result=='1'){  
          $rtnCde = hostping($c_host); /// winhostping(windows command)
          $msg1="viewscan: ".$c_host. " result=1 new action rc=:".strval($rtnCde);
          if ($rtnCde != 0){ 
            /// ping NG
            $agentFlag="1";
            resultdbupdate($c_host,"2"); ///ホストデータ更新 result=2
            writelogd($pgm,$msg1);
            eventlog($hostArr,"2"); ///イベントログ作成
            /// clear statistics snmp value 
            $stat_sql='update statistics set cpuval="",ramval="",diskval="",process="",tcpport="" where host="'.$c_host.'"';
            $rtnCde=putdata($stat_sql);
            if ($c_mailopt=='1'){  /// mailopr=1 mail send
              mailupdown($hosrRowsRec,'PROBLEM');  ///メール送信
              //mailupdown($grec,'PROBLEM');  ///メール送信
            }
          }else{
            /// ping OK
            /// statistics gtype=0にする
            $stat_sql='select gtype from statistics where host="'.$c_host.'"';
            $srtn=getdata($stat_sql);
            if (isset($srtn)){
              $cgtype=$srtn[0];
              if (!($cgtype=='5' or $cgtype=='6')){ 
                $usql='update statistics set gtype="0" where host="'.$c_host.'"';
                putdata($usql);
              }
            } 
          }  
        /// 前回結果 異常　result 0,2
        }else if ($c_result=='0' or $c_result=='2'){ /// result=0 or 2
          $rtnCde = hostping($c_host);  /// winhostping(windows command)
          $msg1="viewscan: ".$c_host. " result=2 new action rc=".strval($rtnCde);
          writelogd($pgm,$msg1);
          /// 
          if ($rtnCde == 0){
            /// ping OK 今回正常
            resultdbupdate($c_host,"1"); /// update result=1
            eventlog($hostArr,"1");  ///normal event
            writelogd($pgm,'host='.$c_host.'ping return=0');
            /// mail send check(active,dead and itemarray set)
            if ($c_mailopt=='1'){
              mailupdown($hostRowsRec,'RECOVERY'); ///send mail for ping
              //mailupdown($grec,'RECOVERY'); ///send mail for ping
            }
            /// vmmib snmpset
            /// tcpportの&があればsnmpset
            if (substr($c_tcpport,0,1) == '&'){
              $c_tcpportx=mb_substr($c_tcpport,1); ///top char strip
              snmptcpportset($c_host,$c_comm,$c_tcpportx);
            }
            /// processの&があればsnmpset 
            if (substr($c_process,0,1) == '&'){
              $c_processx=mb_substr($c_process,1); ///top char strip
              snmpprocessset($c_host,$c_comm,$c_processx);
            }
            /// vmmib end

          }else{
            /// ping NG 今回も異常
            $agentFlag="1";
            resultdbupdate($c_host,"3"); /// update result=3
            $stat_sql='select gtype from statistics where host="'.$c_host.'"';
            $statRows=getdata($stat_sql);
            if (isset($statRows)){
              $gtype=$statRows[0];   /// undefine offset
              /// gtype=0:未監視 gtype=4:無応答 gtype=5:確認 gtype=6:確認済
              /// gtype=7:クローズ
              if ($gtype=="5"){
                /// 確認 confirm gtype="5" -> gtype=6
                $stat_sql='update statistics set gtype="6" where host="'.$c_host.'"';
                putdata($stat_sql);
                eventlog($hostArr,"3");  ///監視管理
              } 
              if ($gtype!="6"){ 
                /// 確認済以外はエラー処理 gtype=6は無視 gtype=9はなにもしない
                if (!($gtype=='0' or $gtype=='9')){ 
                  writeloge($pgm,'gtype invalid? host='.$c_host.' gtype='.$gtype);
                }
              }
            }
          }
        /// 前回結果 障害中最終　result 9
        }else if($c_result=="5"){ /// 5 ->2
        //}else if($c_result=="9"){ /// 9
          $agentFlag="1";
          resultdbupdate($c_host,"2"); /// update result=2 roundtrip
        /// 前回結果　障害中　result 3,4,5,6,7,8
        }else{
          $agentFlag="1";
          $rslt = strval(intval($c_result + 1)); ///update result=current value + 1
          resultdbupdate($c_host,$rslt);
        }
      }
    }
    /// end each host
    if ($debug=="7"){
      $elapsTime=time()-$startHostTime;
      $elapsHost=$c_host.':'.strval($elapsTime);
      array_push($elapsArr,$elapsHost);
    }
  }
  if ($debug=="7"){
    foreach ($elapsArr as $elapsRec){
      writeloge($pgm,$elapsRec.'sec');
    }
  }
}

///---------End of viewscan----------------------------
///

if(!isset($_GET['param'])){ /// ユーザ取得依頼
  
  print '<!DOCTYPE html>';
  print '<html lang="ja">';
  print '<head>';
  print '<meta charset="UTF-8">';
  print '<link rel="stylesheet" href="css/kanshi1.css">';
  print '</head>';
  print '<body>';
  print '<h4>しばらくお待ち下さい</h4>';
  print '</body>';
  print '</html>';
  

  paramGet($pgm);
  ///
}else{     
  paramSet();
  ///
  $admin_sql='select * from admintb';
  $adminRows=getdata($admin_sql);
  $adminArr=explode(',',$adminRows[0]);
  $monIntVal=$adminArr[7];
  ///$snmpIntVal=$adminArr[8];
  $debug=$adminArr[9];
  $adminNum=$adminArr[10];
  ///$coreOldCtr=$adminArr[11];
  $coreNewCtr=$adminArr[12];
  $coreIntVal=intval($monIntVal/2);
  $coreStrVal=strval($coreIntVal);
  $msg='Debug: coreval='.$coreStrVal;
  writelogd($pgm,$msg);
  
  print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
  print '<html lang="ja">';
  print '<head>';
  print "<meta http-equiv='refresh' content={$coreStrVal}>";
  print '<link rel="stylesheet" href="css/kanshi1.css">';
  print '</head>';
  print '<body>';
  print "<h4>Core Refresh {$coreStrVal}sec</h4>";
  print '</body></html>';
  
  ///--------------------表示処理--------------
  ///-------------- refresh処理 --------------
  $startStamp=date('ymdHis');
  /// alive count up
  $coreOldVal=$coreNewCtr;
  if ($coreNewCtr>1){
    $coreNewVal=$coreNewCtr-1;
  }else{
    $coreNewVal=1;
  }
  $admin_sql = 'update admintb set coreoldctr='.$coreOldVal.', corenewctr='.$coreNewVal;
  putdata($admin_sql);
  $msg="Retry Time ".$startStamp;
  writelogd($pgm,$msg);
  ///----------- viewscan 呼び出し--------------
  writelogd($pgm,"--------------->> viewscan 開始");
  viewscan(); 
  writelogd($pgm,"--------------->> viewscan 終了");
  ///--------------------------------------------
  $layout_sql="select host from layout where host like '127%'";
  $layoutRows=getdata($layout_sql);
  if(isset($layoutRows)){
    foreach($layoutRows as $layoutRowsRec){
      $layoutHost=$layoutRowsRec;
      $local_sql="select host,snmpcomm,agenthost from host where host='".$layoutHost."'";
      $localRows=getdata($local_sql);
      if(! isset($localRows)){
        writeloge($pgm,"Failed No statistics: ".$local_sql);
        eventlog($hostArr,"a"); ///
        branch($pgm,$user);
      }
      $localArr=explode(',',$localRows[0]);
      $localHost=$localArr[0];
      $localComm=$localArr[1];
      $localAgent=$localArr[2];
      $stat_sql="select agent from statistics where host='".$localHost."'";
      $statRows=getdata($stat_sql);
      if(! isset($statRows)){
        writeloge($pgm,"statisticsテーブルselect失敗".$stat_sql);
        eventlog($hostArr,"a"); ///
        branch($pgm,$user);
      }
      if($localHost=='127.0.0.1'){
        /// 127.0.0.1の処理
        $oldflag=$statRows[0];
        $usql="";
        /// layout中のホストが全てアクティブなら、127.0.0.1のsyslocationへokセット
        /// インアクティブがあれば、ngをセット
        $agentValue="";
        if ($agentFlag=='0'){
          /// sysLocationがngだったらng、 okだったらok
          $astat=getagent('127.0.0.1','private');
          if ($astat=='ng' or $astat=='sb'){
            $agentValue='ng';
          }else if($astat=='ok'){
            $agentValue='ok';
          }  
        }else{
          $agentValue='ng';
        }
        putagent('127.0.0.1','private',$agentValue); ///syslocationへValueセット
        $stat_sql='update statistics set agent="'.$agentValue.'" where host="127.0.0.1"';
        $rtnCde=putdata($stat_sql);
        if (! empty($rtnCde)){ /// connection error || sql error || not found
          writeloge($pgm,"statisticsテーブル更新エラー: ".$stat_sql);
          eventlog($hostArr,"a"); /// a DB異常
        }
        if ($oldflag!=$agentFlag){
          writelogd($pgm,"Agent flag changed ".$stat_sql);
        }
      }else{
        /// 127.0.0.1以外の127.0.0.2,127 0.0.3などの他の監視サイトの処理
        /// 他サイト127.0.0.1に相当するlocalAgentホストからok またはngを取得
        /// snmpagent.php内にある
        $astat=getagent($localAgent,$localComm);
        $usql="update statistics set agent='".$astat."' where host='".$localHost."'";
        $rtnCde=putdata($usql);
        if (!empty($rtnCde)){ /// connection error || sql error || not found
          writeloge($pgm,"statisticsテーブル接続エラー: ".$usql);
          eventlog($hostArr,"a"); /// a DB異常
          branch($pgm,$user);
        }
      } 
    }
  }
  /*
  ///-----------------------------------------------------
  /// admintbのstandbyが"2"の場合、"1"へ
  /// standbyが"1"の場合、"0"へそしてsaveintval値をsnmpintvalへ 
  ///-----------------------------------------------------
  if ($standby=="2"){ 
    $admin_sql="update admintb set standby='1'";
    putdata($admin_sql);
  }else if($standby=='1'){
    $admin_sql="update admintb set standby='0', snmpintval=".$saveIntVal;
    putdata($admin_sql);
  }
  */
  ///
  $endStamp=date('ymdHis');
  $endTimeUnix=strtotime($endStamp);
  $startTimeUnix=strtotime($startStamp);
  $elapsTime = $endTimeUnix - $startTimeUnix;  
  $msg='start:'.strval($startStamp).' end:'.strval($endStamp).' elaps:'.strval($elapsTime);
  writelogd($pgm,$msg); 
  $coreStamp=time(); 
  $proc_sql='update processtb set corestamp="'.strval($coreStamp).'"';
  putdata($proc_sql);
  /// elaps time of Core
  if ($debug=="7"){
    $elapsCoreTime=time()-$startCoreTime;
    $elapsCore='Host count='.strval($numberOfHost).' Core elaps time='.strval($elapsCoreTime).'sec';
    writeloge($pgm,$elapsCore);
  }
  ///
}
?>


