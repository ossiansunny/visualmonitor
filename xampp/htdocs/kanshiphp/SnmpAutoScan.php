<?php
require_once "mysqlkanshi.php";
require_once "snmpdataget.php";
require_once "mailsendsnmp.php";
require_once "mailsendany.php";

$statisupsw="0";
$snmpGType="0";
$kanriuser="";
$pgm='SnmpAutoScan.php';
//// debug host
$debughost = '255.255.255.255';
////
function agentprocessset($host,$community,$process){
  $rtncd=0;
  if (substr($process,0,1)=='&'){                    // &httpd;sshd
    $processx=substr($process,1,strlen($process)-1); // httpd;sshd
    $rtncd=snmpprocessset($host,$community,$processx);
  }
  if (substr($h_process,0,1)=='%'){                  // %httpd;sshd
    $processx=substr($process,1,strlen($process)-1); // httpd;sshd
    $rtncd=snmptrapset($host,$community,$processx);
  }  
  return $rtncd;
  
}

function nullstatis($statis){
  if (is_null($statis) || $statis==""){
    return 'empty';
  } else {
    return $statis;
  }
} 

function snmpvalcheck(&$snmpval){
  if (is_null($snmpval) || $snmpval==""){
    $snmpval = 'empty';
  } 
}

function snmpeventlog($hostrec,$stat,$snmpt,$snmpv,$stat2){
  global $statisupsw;
  global $snmpGType;
  global $kanriuser;
  global $pgm;
  $msg="snmpeventlog entry";
  writelogd($pgm,$msg);
  ///
  if ($stat=="1"){
    $statisupsw="3";    /// statistics snmptype normal 
  }elseif ($stat=="2"){
    $statisupsw="4";    /// statistics snmptype alert
  } 
  /// event log
  if (!($snmpGType=='5' or $snmpGType=='6')){
    /// $stat=2でgtype=5のとき、confclose=2にする
    $cfcl='0';
    if ($stat=='2' and $snmpGType=='5'){
      $cfcl='2';
    }
    $eventtime = date('ymdHis');
    $hostmei=$hostrec[0];
    $eventt=strval(intval($snmpt)+1);
    $insql = "insert into eventlog (host,eventtime,eventtype,snmptype,snmpvalue,kanrisha,kanrino,confclose) values('".$hostmei."','".$eventtime."','".$stat."','".$eventt."','".$snmpv."','" .$kanriuser. "','','" .$cfcl. "')";
    putdata($insql); 
    $msg = $hostmei . " Eventlog Insert sql: " .$insql;
    writelogd($pgm,$msg);    
  }
  
}
///
function snmpmailsend($hostrec,$stat,$snmpt,$snmpv,$stat2){
  global $pgm;
  $mailopt=$hostrec[6];
  if ($mailopt =='1'){
    $rtcd=mailsendsnmp($hostrec,$snmpt,$snmpv,$stat2);
    if ($rtcd==1){
      $mailerror='イベント変化のメール失敗、メールサーバをチェック';
      writeloge($pgm,$mailerror);
    }
  }
}
////////////////////////////////////////////////////////
/// メイン処理
////////////////////////////////////////////////////////
$currentTimeStamp = date('ymdHis');
$admin_sql='select * from admintb';
$adminRows=getdata($admin_sql);
$adminArr=explode(',',$adminRows[0]);
$kanriuser=$adminArr[0];
$kanripass=$adminArr[1];  /// use initial interval
$monintval=$adminArr[7];
$snmpintval=$adminArr[8];
if ($snmpintval==0 || $snmpintval< $monintval/2){
  $snmpintval=$monintval*5;
}
/// snmpautoscan interval
$snmpintvalstr=strval($snmpintval);
if (intval($kanripass) > time()-(5*60)){  /// ログインから５分間は、30秒間隔
  $snmpintvalstr=30;
}
///

//$snmpinittime=sprintf('%s',time()); 

$coremax=strval(intval($snmpintval / ($monintval / 2)));
$sql='update admintb set snmpintval='.$snmpintvalstr.', coreoldctr='.$coremax.', corenewctr='.$coremax;
putdata($sql);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
echo '<html lang="ja">';
echo '<head>';
echo "<meta http-equiv='refresh' content={$snmpintvalstr}>";
echo '<link rel="stylesheet" href="css/kanshi1.css">';
echo '</head>';
echo '<body>';
echo "<h4>SNMP Refresh {$snmpintvalstr}sec</h4>";


/// host layout 読み込み
$layout_sql='select host from layout where host!="No Assign"';
$layoutRows=getdata($layout_sql);
$c=count($layoutRows);
for ($i=0;$i<$c;$i++){
  $layoutArr=explode(',',$layoutRows[$i]);
  $host_sql="select * from host where host='".$layoutArr[0]."'";
  $hostRows=getdata($host_sql);
  $hostCount=count($hostRows);
  for ($j=0;$j<$hostCount;$j++){
    $hostRowsRec=$hostRows[$j];
    $hostArr=explode(',',$hostRowsRec);
    ///[0]:host [1]:groupname [2]:ostype [3]:result [4]:action [5]:viewname [6]:mailopt
    ///[7]:tcpport [8]cpulim [9]:ramlim [10]:disklim [11]:process [12]image [13]snmpcomm
    $host=$hostArr[0];
    $host_result=$hostArr[3];
    $host_action=$hostArr[4];
    //$mailOpt=$hostArr[6];
    $tcpPort=$hostArr[7];
    //$cpuLim=$hostArr[8];
    //$ramLim=$hostArr[9];
    //$diskLim=$hostArr[10];
    $process=$hostArr[11];
    $community=$hostArr[13];
    /// 
    /// statistics読み
    $sql="select * from statistics where host='".$host."'"; 
    $statRows = getdata($sql);
    $currentTimeStamp = date('ymdHis');
    $snmpGType = '';
    if (empty($statRows)) {
      ///
      /// 無ければ作成,Agentへプロセスセット
      $stat_sql="insert into statistics (host,tstamp,gtype) values('".$host."','".$currentTimeStamp."','9')";
      putdata($stat_sql); 
      $logMsg = 'No statistics record then create new record: ' . $insql;
      writelogd('SnmpAutoScan.php',$logMsg);
      $statArr = array("","000000000000","0","","","","","","");
      $statArr[0] = $host;
      $statArr[1] = $currentTimeStamp;
      $snmpGType = '9';
      agentprocessset($host,$process,$community); 
    } else {
      ///
      /// あれば読み込み,gtypr='9'ならばAgentへプロセスセット
      $statArr = explode(',',$statRows[0]);
      $snmpGType=$statArr[2];
      if ($snmpGType=='9'){
        agentprocessset($host,$process,$community);
      }
    }
    //////////////////////////////////////////////////////////////////
    /// gtype=5の場合、statistics gtype=6にし、eventlog 作成、メール送信
    if ($snmpGType=="5"){
      $host=$statArr[0];
      $stat_sql="update statistics set gtype='6' where host='" .$host. "'";
      putdata($stat_sql);
      /// 確認済eventlog作成 
      $eventtime = date('ymdHis');
      $event_sql = "insert into eventlog (host,eventtime,eventtype,snmptype,snmpvalue,kanrisha,confclose) values('".$host."','".$eventtime."','3','7','7','".$kanriuser."','2')";
      putdata($event_sql);
      $msg = $hostmei . " Eventlog Insert sql: " .$insql;
      writelogd($pgm,$msg); 
      $confsub="ホスト：".$host." 障害確認済";
      $confbody="障害管理番号でクローズ処理待ち"; 
      mailsendany("adminsubject","","",$confsub,$confbody);
      continue;
    //////////////////////////////////////////////////////////////////
    }elseif($snmpGType=="6"){
      continue;
    }elseif($snmpGType=="0"){
      $host_result="1";
    }
    if ($host_action=="2" && $host_result=="1"){ /// host recordのsnmp監視 and ping結果OK
      /// hostdata Action=2(snmp) and Result=1(ping結果OK)
      $s_host = $statArr[0];
      $s_stamp = $statArr[1];      
      $s_cpuval = nullstatis($statArr[3]);  /// statistics nall値の場合　'empty'を入れる
      $s_ramval = nullstatis($statArr[4]);
      $s_agent = $statArr[5];
      if (is_null($statArr[5])){
        $s_agent = '';
      }
      $s_diskval = nullstatis($statArr[6]);
      $s_process = nullstatis($statArr[7]);
      $s_tcpport = nullstatis($statArr[8]);  
      $snmpOldValue=array();

      $snmpOldValue[0]=$s_host; //host
      $snmpOldValue[1]=$s_cpuval; //cpu
      $snmpOldValue[2]=$s_ramval; //ram
      $snmpOldValue[3]=$s_diskval; //disk
      $snmpOldValue[4]=$s_process; //process
      $snmpOldValue[5]=$s_tcpport; //tcpport
//// debug
//if ($host==$debughost){
//$snmpOldValue=array($debughost,"n:20","n:30","n:40","oracle","1521;403");
//}
////
      /// snmpデータ取得 start ////////////////////////////////////////////////
      $snmpNewValue = snmpdataget($hostArr); 
      /// snmpデータ取得 end   ////////////////////////////////////////////////
////
//// debug
//if ($host==$debughost){
//$snmpNewValue=array($debughost,"w:50","n:30","w:40","oracle","1521;403");
//}
////
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
      for($cc=0;$cc<6;$cc++){
        /// unknownの時、前回と同じ値をセット
        snmpvalcheck($snmpNewValue[$cc]);
        if (preg_match("/unknown/",$snmpNewValue[$cc])){
          $snmpNewValue[$cc]=$snmpOldValue[$cc];
        }
      }
      // ping 又はsnmp エラーで値不定のログ出力

//// debug
if ($host==$debughost){
//$logMsg=$host.' cpulim='.$cpuLim.' old_cpulim='.$snmpOldValue[1].' new_cpulim='.$snmpNewValue[1];
//writeloge($pgm,$logMsg);
//$logMsg=$host.' ramlim='.$ramLim.' old_ramlim='.$snmpOldValue[2].' new_ramlim='.$snmpNewValue[2];
//writeloge($pgm,$logMsg);
//$logMsg=$host.' disklim='.$diskLim.' old_disklim='.$snmpOldValue[3].' new_disklim='.$snmpNewValue[3];
//writeloge($pgm,$logMsg);
$logMsg=$host.' process='.$process.' old_process='.$snmpOldValue[4].' new_process='.$snmpNewValue[4];
writeloge($pgm,$logMsg);
$logMsg=$host.' tcpport='.$tcpPort.' old_rcpport='.$snmpOldValue[5].' new_tcpport='.$snmpNewValue[5];
writeloge($pgm,$logMsg);
}
////
      
      $snmpOldCount = count($snmpOldValue);  ///snmpOldValue=statistics, snmpNewValue=測定値

      for ($scc=1;$scc<$snmpOldCount;$scc++){
        $old_val=$snmpOldValue[$scc];
        $new_val=$snmpNewValue[$scc]; 
//// debug
//if ($host==$debughost){
//writeloge($pgm,$host.' scc='.strval($scc).' old='.$old_val.' new='.$new_val);
//}
////
        if (substr($old_val,0,1) != substr($new_val,0,1)){ /// 各項目比較
          if ($scc<4){ ///scc:1 cpu, scc:2 ram, scc:3 disk　処理
//// debug
//if ($host==$debughost and $scc==1){
//writeloge($pgm,$host.' scc='.strval($scc).' old='.$old_val.' new='.$new_val);
//}
////
            if ($old_val=="empty" and substr($new_val,0,1)=="n"){  
              snmpeventlog($hostArr,"1",strval($scc),$new_val,"1"); 
              snmpmailsend($hostArr,"1",strval($scc),$new_val,"1");
            } elseif ($old_val=="empty" and substr($new_val,0,1)!="n"){ 
              snmpeventlog($hostArr,"2",strval($scc),$new_val,"2"); 
              snmpmailsend($hostArr,"2",strval($scc),$new_val,"2");
            } elseif (substr($old_val,0,1)=="n" and substr($new_val,0,1)=="w"){ 
              snmpeventlog($hostArr,"2",strval($scc),$new_val,"2"); 
              snmpmailsend($hostArr,"2",strval($scc),$new_val,"2");
            } elseif (substr($old_val,0,1)=="n" and substr($new_val,0,1)=="c"){
              snmpeventlog($hostArr,"2",strval($scc),$new_val,"2"); 
              snmpmailsend($hostArr,"2",strval($scc),$new_val,"2");
            } elseif (substr($old_val,0,1)=="w" and substr($new_val,0,1)=="c"){ 
              snmpeventlog($hostArr,"2",strval($scc),$new_val,"2"); 
              snmpmailsend($hostArr,"2",strval($scc),$new_val,"2");
            } elseif (substr($old_val,0,1)!="n" and substr($new_val,0,1)=="n"){ 
              snmpeventlog($hostArr,"1",strval($scc),$new_val,"1"); 
              snmpmailsend($hostArr,"1",strval($scc),$new_val,"1");
            } elseif (!($old_val==$new_val)){ 
              snmpeventlog($hostArr,"2",strval($scc),$new_val,"2"); 
              snmpmailsend($hostArr,"2",strval($scc),$new_val,"2");
            }             
          }else{ /// scc:4=process or scc:5==port 処理
//// debug
if ($host==$debughost){
writeloge($pgm,$host.' process or port scc='.strval($scc).' old='.$old_val.' new='.$new_val );
}
////
            if ($old_val=="empty" and $new_val=="allok"){ 
              snmpeventlog($hostArr,"1",strval($scc),$new_val,"1"); 
              snmpmailsend($hostArr,"1",strval($scc),$new_val,"1");
            } elseif ($old_val=="empty" and $new_val!="allok"){ 
              snmpeventlog($hostArr,"2",strval($scc),$new_val,"2"); 
              snmpmailsend($hostArr,"2",strval($scc),$new_val,"2");
            } elseif ($old_val=="allok" and $new_val!="allok"){ 
              snmpeventlog($hostArr,"2",strval($scc),$new_val,"2"); 
              snmpmailsend($hostArr,"2",strval($scc),$new_val,"2");
            } elseif ($old_val!="allok" and $new_val=="allok"){
              snmpeventlog($hostArr,"1",strval($scc),$new_val,"1"); 
              snmpmailsend($hostArr,"1",strval($scc),$new_val,"1");
            }elseif (!($old_val==$new_val)){ 
              snmpeventlog($hostArr,"2",strval($scc),$new_val,"2"); 
              snmpmailsend($hostArr,"2",strval($scc),$new_val,"2");
            }
          } 

        } else {  /// 前回と今回が同じ状態の処理
//// debug
if ($host == $debughost){
writeloge($pgm,$host.' same status scc='.strval($scc).' old='.$old_val.' new='.$new_val );

}
////
          $currentTimeStamp = date('ymdHis');
          $eventType=''; 
          if (((int)$currentTimeStamp-(int)$s_stamp) > 1000){ //前回より1秒以上か
            if ($snmpGType=='3'){ /// gtype 3監視正常のみ（ex。0:未監視、1:無応答）
              $eventType='1'; ///監視正常
            }else{
              $eventType='2'; ///監視異常
            }
            if (!(substr($new_val,0,1)=='n' || $new_val=='empty' || $new_val=='allok')){
              //snmpeventlogmail($hostArr,$eventType,strval($scc),$new_val,$eventType);
              snmpeventlog($hostArr,$eventType,strval($scc),$new_val,$eventType); 
              snmpmailsend($hostArr,$eventType,strval($scc),$new_val,$eventType);
            } 
          } 
        }
        ///-------------statistics更新----------
        if ($statisupsw=="3"){ /// 3 監視正常 
          $stat_sql="update statistics set tstamp='".$currentTimeStamp."',gtype='3',cpuval='".$snmpNewValue[1]."',ramval='".$snmpNewValue[2]."',agent='".$s_agent."',diskval='".$snmpNewValue[3]."',process='".$snmpNewValue[4]."',tcpport='".$snmpNewValue[5]."' where host='".$snmpNewValue[0]."'";
          putdata($stat_sql);
        }elseif($statisupsw=="4"){ /// 4 監視一部異常
          $stat_sql="update statistics set tstamp='".$currentTimeStamp."',gtype='4',cpuval='".$snmpNewValue[1]."',ramval='".$snmpNewValue[2]."',agent='".$s_agent."',diskval='".$snmpNewValue[3]."',process='".$snmpNewValue[4]."',tcpport='".$snmpNewValue[5]."' where host='".$snmpNewValue[0]."'";
          putdata($stat_sql);
        }elseif($snmpGType=="9"){
          $stat_sql="update statistics set tstamp='".$currentTimeStamp."',gtype='0',cpuval='',ramval='',agent='".$s_agent."',diskval='',process='',tcpport='' where host='".$snmpNewValue[0]."'";
          putdata($stat_sql);
        }
      } 

    } elseif ($host_action=='2') { /// host record snmp監視 ping結果NG
      ///snmp無応答 gtype=1
      if($snmpGType=="9"){
        $stat_sql="update statistics set tstamp='".$currentTimeStamp."',gtype='0',cpuval='',ramval='',agent='".$s_agent."',diskval='',process='',tcpport='' where host='".$snmpNewValue[0]."'";
        putdata($stat_sql);
      }else{
        $stat_sql="update statistics set gtype='1' where host='" .$host. "'";
        putdata($stat_sql);
      }
    }
  } /// end of One host record for
} /// end of Host list record for

$prcstamp = time();
$proc_sql="update processtb set snmpstamp=".strval($prcstamp);
putdata($proc_sql);

print '</body></html>';
?>

