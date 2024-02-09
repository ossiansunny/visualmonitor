<?php
require_once "mysqlkanshi.php";
require_once "snmpdataget.php";
require_once "mailsendsnmp.php";
require_once "mailsendany.php";

$statisupsw="0";
$snmpgtype="0";
$kanriuser="";
$pgm='SnmpAutoScan.php';

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
  global $snmpgtype;
  global $kanriuser;
  global $pgm;
  ///
  if ($stat=="1"){
    $statisupsw="3";    /// statistics snmptype normal 
  }elseif ($stat=="2"){
    $statisupsw="4";    /// statistics snmptype alert
  } 
  /// event log
  if (!($snmpgtype=='5' or $snmpgtype=='6')){
    /// $stat=2でgtype=5のとき、confclose=2にする
    $cfcl='0';
    if ($stat=='2' and $snmpgtype=='5'){
      $cfcl='2';
    }
    $eventtime = date('ymdHis');
    $hostmei=$hostrec[0];
    $eventt=strval(intval($snmpt)+1);
    $insql = "insert into eventlog (host,eventtime,eventtype,snmptype,snmpvalue,kanrisha,confclose) values('".$hostmei."','".$eventtime."','".$stat."','".$eventt."','".$snmpv."','".$kanriuser."','".$cfcl."')";
    $msg = $hostmei . " Eventlog Insert sql: " .$insql;
    putdata($insql); 
    
  }
  
}
///
function snmpmailsend($hostrec,$stat,$snmpt,$snmpv,$stat2){
  $mailopt=$hostrec[6];
    if ($mailopt =='1'){
      $rtcd=mailsendsnmp($hostrec,$snmpt,$snmpv,$stat2);
      if ($rtcd==1){
        $mailerror='イベント変化のメール失敗、メールサーバをチェック';
        writeloge($pgm,$mailerror);
      }
    }
}
///
$sql='select * from admintb';
$rows=getdata($sql);
$kanridata=explode(',',$rows[0]);
$kanriuser=$kanridata[0];
$monintval=$kanridata[7];
$snmpintval=$kanridata[8];
if ($snmpintval==0 || $snmpintval< $monintval/2){
  $snmpintval=$monintval*5;
}
$snmpintvalstr=strval($snmpintval);
$coremax=strval(intval($snmpintval / ($monintval / 2)));
$sql='update admintb set snmpintval='.$snmpintvalstr.', coreoldctr='.$coremax.', corenewctr='.$coremax;
putdata($sql);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
echo '<html lang="ja">';
echo '<head>';
echo "<meta http-equiv='refresh' content={$snmpintvalstr}>";
echo '<link rel="stylesheet" href="kanshi1.css">';
echo '</head>';
echo '<body>';
echo "<h4>SNMP Refresh {$snmpintvalstr}sec</h4>";

$tmstamp = date('ymdHis');


/// host layout 読み込み
$sql='select host from layout where host!="No Assign"';
$hlayout=getdata($sql);
$c=count($hlayout);
for ($i=0;$i<$c;$i++){
  $hostlist=explode(',',$hlayout[$i]);
  $sql="select * from host where host='".$hostlist[0]."'";
  $data=getdata($sql);
  $d=count($data);
  for ($j=0;$j<$d;$j++){
    $datarec=$data[$j];
    $itemarray=explode(',',$datarec);
    ///[0]:host [1]:groupname [2]:ostype [3]:result [4]:action [5]:viewname [6]:mailopt
    ///[7]:tcpport [8]cpulim [9]:ramlim [10]:disklim [11]:process [12]image [13]snmpcomm
    $host=$itemarray[0];
    $host_result=$itemarray[3];
    $host_action=$itemarray[4];
    $mailopt=$itemarray[6];
    $tcpport=$itemarray[7];
    $cpulim=$itemarray[8];
    $ramlim=$itemarray[9];
    $disklim=$itemarray[10];
    $process=$itemarray[11];
    /// statistics読み、無ければ作成
    $sql="select * from statistics where host='".$host."'"; 
    $stdata = getdata($sql);
    $tmstamp = date('ymdHis');
    if (empty($stdata)) {
      $insql="insert into statistics (host,tstamp,gtype) values('".$host."','".$tmstamp."','9')";
      putdata($insql); 
      $logmsg = 'No statistics record then create new record: ' . $insql;
      writeloge('SnmpAutoScan.php',$logmsg);
      $stdata1 = array("","000000000000","0","","","","","","");
      $stdata1[0] = $host;
      $stdata1[1] = $tmstamp;
      
    } else {
      $stdata1 = explode(',',$stdata[0]);
    }
    $snmpgtype = $stdata1[2];
    /// gtype=5の場合、statistics gtype=6にし、eventlog 作成、メール送信
    if ($snmpgtype=="5"){
      $host=$stdata1[0];
      $upsql="update statistics set gtype='6' where host='" .$host. "'";
      putdata($upsql);
      /// 確認済eventlog作成 
      $eventtime = date('ymdHis');
      $insql = "insert into eventlog (host,eventtime,eventtype,snmptype,snmpvalue,kanrisha,confclose) values('".$host."','".$eventtime."','3','7','7','".$kanriuser."','2')";
      $msg = $hostmei . " Eventlog Insert sql: " .$insql;
      putdata($insql);
      writelogd($pgm,$msg); 
      $confsub="ホスト：".$host." 障害確認済";
      $confbody="障害管理番号でクローズ処理待ち"; 
      mailsendany("adminsubject","","",$confsub,$confbody);
      continue;
    }elseif($snmpgtype=="6"){
      continue;
    }elseif($snmpgtype=="0"){
      $host_result="1";
    }
    if ($host_action=="2" && $host_result=="1"){ /// host recordのsnmp監視 and ping結果OK
      /// hostdata Action=2(snmp) and Result=1(ping結果OK)
      $s_host = $stdata1[0];
      $s_stamp = $stdata1[1];      
      $s_cpuval = nullstatis($stdata1[3]);  /// statistics nall値の場合　'empty'を入れる
      $s_ramval = nullstatis($stdata1[4]);
      $s_agent = $stdata1[5];
      if (is_null($stdata1[5])){
        $s_agent = '';
      }
      $s_diskval = nullstatis($stdata1[6]);
      $s_process = nullstatis($stdata1[7]);
      $s_tcpport = nullstatis($stdata1[8]);  
      $snmpread=array();

      $snmpread[0]=$s_host; //host
      $snmpread[1]=$s_cpuval; //cpu
      $snmpread[2]=$s_ramval; //ram
      $snmpread[3]=$s_diskval; //disk
      $snmpread[4]=$s_process; //process
      $snmpread[5]=$s_tcpport; //tcpport
//if ($host=='192.168.1.155'){
//$snmpread=array("192.168.1.155","n:20","n:30","n:40","oracle","1521;403");
//}
      
      /// snmpデータ取得 ////////////////////////////////////////////////
      $snmpvalue = snmpdataget($itemarray); 
//if ($host=='192.168.1.155'){
//$snmpvalue=array("192.168.1.155","w:50","n:30","w:40","oracle","1521;403");
//}
      /// snmpdatagetでのエラーは各項目「unknown」が返される ////////////
      ///////////////////////////////////////////////////////////////////
      $tmstamp = date('ymdHis');
      /// snmpvalue=snmpdataget 処理データ配列
      /// snmpvalue[0] = host  
      /// snmpvalue[1] = CPU    
      /// snmpvalue[2] = RAM    
      /// snmpvalue[3] = Disk   
      /// snmpvalue[4] = Process
      /// snmpvalue[5] = Port
      for($cc=0;$cc<6;$cc++){
        /// unknownの時、前回と同じ値をセット
        snmpvalcheck($snmpvalue[$cc]);
        if (preg_match("/unknown/",$snmpvalue[$cc])){
          $snmpvalue[$cc]=$snmpread[$cc];
        }
      }
      // ping 又はsnmp エラーで値不定のログ出力

/*
      if ($host='192.168.1.155'){
      $record=$host.' cpulim='.$cpulim.' old_cpulim='.$snmpread[1].' new_cpulim='.$snmpvalue[1];
      writeloge($pgm,$record);
      $record=$host.' ramlim='.$ramlim.' old_ramlim='.$snmpread[2].' new_ramlim='.$snmpvalue[2];
      writeloge($pgm,$record);
      $record=$host.' disklim='.$disklim.' old_disklim='.$snmpread[3].' new_disklim='.$snmpvalue[3];
      writeloge($pgm,$record);
      $record=$host.' process='.$process.' old_process='.$snmpread[4].' new_process='.$snmpvalue[4];
      writeloge($pgm,$record);
      $record=$host.' tcpport='.$tcpport.' old_rcpport='.$snmpread[5].' new_tcpport='.$snmpvalue[5];
      writeloge($pgm,$record);
      }
*/  
      
      
      $stc = count($snmpread);  ///snmpread=statistics, snmpvalue=測定値

      for ($scc=1;$scc<$stc;$scc++){
        $old_val=$snmpread[$scc];
        $new_val=$snmpvalue[$scc]; 
//if ($host=='192.168.1.155'){
//writeloge($pgm,$host.' scc='.strval($scc).' old='.$old_val.' new='.$new_val);
//}
        if (substr($old_val,0,1) != substr($new_val,0,1)){ /// 各項目比較
          if ($scc<4){ ///cpu ram disk　処理
//if ($host=='192.168.1.155' and $scc==1){
//writeloge($pgm,$host.' scc='.strval($scc).' old='.$old_val.' new='.$new_val);
//}
            if ($old_val=="empty" and substr($new_val,0,1)=="n"){  
              snmpeventlog($itemarray,"1",strval($scc),$new_val,"1"); 
              snmpmailsend($itemarray,"1",strval($scc),$new_val,"1");
            } elseif ($old_val=="empty" and substr($new_val,0,1)!="n"){ 
              snmpeventlog($itemarray,"2",strval($scc),$new_val,"2"); 
              snmpmailsend($itemarray,"2",strval($scc),$new_val,"2");
            } elseif (substr($old_val,0,1)=="n" and substr($new_val,0,1)=="w"){ 
              snmpeventlog($itemarray,"2",strval($scc),$new_val,"2"); 
              snmpmailsend($itemarray,"2",strval($scc),$new_val,"2");
            } elseif (substr($old_val,0,1)=="n" and substr($new_val,0,1)=="c"){
              snmpeventlog($itemarray,"2",strval($scc),$new_val,"2"); 
              snmpmailsend($itemarray,"2",strval($scc),$new_val,"2");
            } elseif (substr($old_val,0,1)=="w" and substr($new_val,0,1)=="c"){ 
              snmpeventlog($itemarray,"2",strval($scc),$new_val,"2"); 
              snmpmailsend($itemarray,"2",strval($scc),$new_val,"2");
            } elseif (substr($old_val,0,1)!="n" and substr($new_val,0,1)=="n"){ 
              snmpeventlog($itemarray,"1",strval($scc),$new_val,"1"); 
              snmpmailsend($itemarray,"1",strval($scc),$new_val,"1");
            } elseif (!($old_val==$new_val)){ 
              snmpeventlog($itemarray,"2",strval($scc),$new_val,"2"); 
              snmpmailsend($itemarray,"2",strval($scc),$new_val,"2");
            }             
          }else{ /// 4=process or 5==port 処理
//if ($host=='192.168.1.155'){
//writeloge($pgm,$host.' process or port scc='.strval($scc).' old='.$old_val.' new='.$new_val );
//}
            if ($old_val=="empty" and $new_val=="allok"){ 
              snmpeventlog($itemarray,"1",strval($scc),$new_val,"1"); 
              snmpmailsend($itemarray,"1",strval($scc),$new_val,"1");
            } elseif ($old_val=="empty" and $new_val!="allok"){ 
              snmpeventlog($itemarray,"2",strval($scc),$new_val,"2"); 
              snmpmailsend($itemarray,"2",strval($scc),$new_val,"2");
            } elseif ($old_val=="allok" and $new_val!="allok"){ 
              snmpeventlog($itemarray,"2",strval($scc),$new_val,"2"); 
              snmpmailsend($itemarray,"2",strval($scc),$new_val,"2");
            } elseif ($old_val!="allok" and $new_val=="allok"){
              snmpeventlog($itemarray,"1",strval($scc),$new_val,"1"); 
              snmpmailsend($itemarray,"1",strval($scc),$new_val,"1");
            }elseif (!($old_val==$new_val)){ 
              snmpeventlog($itemarray,"2",strval($scc),$new_val,"2"); 
              snmpmailsend($itemarray,"2",strval($scc),$new_val,"2");
            }
          } 

        } else {  /// 前回と今回が同じ状態の処理
//if ($host=='192.168.1.155'){
//writeloge($pgm,$host.' same status scc='.strval($scc).' old='.$old_val.' new='.$new_val );
//}
          $tmstamp = date('ymdHis');
          $evtype=''; 
          if (((int)$tmstamp-(int)$s_stamp) > 1000){ //前回より1秒以上か
            if ($snmpgtype=='3'){ /// gtype 3監視正常のみ（ex。0:未監視、1:無応答）
              $evtype='1'; ///監視正常
            }else{
              $evtype='2'; ///監視異常
            }
            if (!(substr($new_val,0,1)=='n' || $new_val=='empty' || $new_val=='allok')){
              //snmpeventlogmail($itemarray,$evtype,strval($scc),$new_val,$evtype);
              snmpeventlog($itemarray,$evtype,strval($scc),$new_val,$evtype); 
              snmpmailsend($itemarray,$evtype,strval($scc),$new_val,$evtype);
            } 
          } 
        }
        ///-------------statistics更新----------
        if ($statisupsw=="3"){ /// 3 監視正常 
          $upsql="update statistics set tstamp='".$tmstamp."',gtype='3',cpuval='".$snmpvalue[1]."',ramval='".$snmpvalue[2]."',agent='".$s_agent."',diskval='".$snmpvalue[3]."',process='".$snmpvalue[4]."',tcpport='".$snmpvalue[5]."' where host='".$snmpvalue[0]."'";
          putdata($upsql);
        }elseif($statisupsw=="4"){ /// 4 監視一部異常
          $upsql="update statistics set tstamp='".$tmstamp."',gtype='4',cpuval='".$snmpvalue[1]."',ramval='".$snmpvalue[2]."',agent='".$s_agent."',diskval='".$snmpvalue[3]."',process='".$snmpvalue[4]."',tcpport='".$snmpvalue[5]."' where host='".$snmpvalue[0]."'";
          putdata($upsql);
        }elseif($snmpgtype=="9"){
          $upsql="update statistics set tstamp='".$tmstamp."',gtype='0',cpuval=''.ramval='',agent='".$s_agent."',diskval='',process='',tcpport='' where host='".$snmpvalue[0]."'";
          putdata($upsql);
        }
      } 

    } elseif ($host_action=='2') { /// host record snmp監視 ping結果NG
      ///snmp無応答 gtype=1
      if($snmpgtype=="9"){
        $upsql="update statistics set tstamp='".$tmstamp."',gtype='0',cpuval=''.ramval='',agent='".$s_agent."',diskval='',process='',tcpport='' where host='".$snmpvalue[0]."'";
        putdata($upsql);
      }else{
        $upsql="update statistics set gtype='1' where host='" .$host. "'";
        putdata($upsql);
      }
    }
  } /// end of One host record for
} /// end of Host list record for

$prcstamp = time();
$updt="update processtb set snmpstamp=".strval($prcstamp);
putdata($updt);

print '</body></html>';
?>

