<?php
require_once "mysqlkanshi.php";
require_once "snmpdataget.php";
require_once "mailsendsnmp.php";

$statisupsw="0";
$snmpgtype="0";

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

function snmpeventlogmail($hostrec,$stat,$snmpt,$snmpv,$stat2){
  global $statisupsw;
  global $snmpgtype;
  $eventtime = date('ymdHis');
  $pgm='SnmpAutoScan.php';
  $hostmei=$hostrec[0];
  $eventt=strval(intval($snmpt)+1);
  $insql = "insert into eventlog (host, eventtime, eventtype, snmptype, snmpvalue) values('".$hostmei."','".$eventtime."','".$stat."','".$eventt."','".$snmpv."')";
  $msg = $hostmei . " Eventlog Insert sql: " .$insql;
  putdata($insql); 
  // write mail
  $mailopt=$hostrec[6];
  if ($mailopt =='1'){
    $rtcd=mailsendsnmp($hostrec,$snmpt,$snmpv,$stat2);
    //mailsendsnmp($itemarray,strval($scc),$snmpvalue[$scc],'2'); //Down通知
    if ($rtcd==1){
      $mailerror='イベント変化のメール失敗、メールサーバをチェック';
      writeloge($pgm,$mailerror);
    }
  }
  if ($stat=="1"){
    $statisupsw="3";    // statistics snmptype normal 
  }elseif ($stat=="2"){
    $statisupsw="4";    // statistics snmptype alert
  } 
}

function snmpeventlog($hostm,$stat,$snmpt,$snmpv){
  $eventtime = date('ymdHis');
  $pgm='SnmpAutoScan.php';
  $insql = "insert into eventlog (host, eventtime, eventtype, snmptype, snmpvalue) values('".$hostm."','".$eventtime."','".$stat."','".$snmpt."','".$snmpv."')";
  $msg = $hostm . " Eventlog Insert sql: " .$insql;
  putdata($insql); 
  
}
//# これはブラウザに出力する日本語が存在するときに必要
$pgm='SnmpAutoScan.php';
$sql='select * from admintb';
$rows=getdata($sql);
$kanridata=explode(',',$rows[0]);
$monintval=$kanridata[7];
$snmpintval=$kanridata[8];
if ($snmpintval==0 || $snmpintval< $monintval/2){
  $snmpintval=$monintval*5;
}
$snmpval=strval($snmpintval);
$coremax=strval(intval($snmpintval / ($monintval / 2)));
$sql='update admintb set snmpintval='.$snmpval.', coreoldctr='.$coremax.', corenewctr='.$coremax;
putdata($sql);
//echo 'Content-type: text/html; charset=UTF-8\n';
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
echo '<html lang="ja">';
echo '<head>';
echo "<meta http-equiv='refresh' content={$snmpval}>";
echo '<link rel="stylesheet" href="kanshi1.css">';
echo '</head>';
echo '<body>';
echo "<h4>SNMP Refresh {$snmpval}sec</h4>";
//echo '</body></html>';

##//-------------- 
$tmstamp = date('ymdHis');
#print('<<<<<<< snmpsutoscan started: '+tmstamp+' >>>>>>>')

## get layout
$sql='select host from layout where host!="No Assign"';
$hlayout=getdata($sql);
$c=count($hlayout);
for ($i=0;$i<$c;$i++){
  $hostlist=explode(',',$hlayout[$i]);
  $sql='select * from host where host="'.$hostlist[0].'"';
  $data=getdata($sql);
  $d=count($data);
  for ($j=0;$j<$d;$j++){
    $datarec=$data[$j];
    $itemarray=explode(',',$datarec);
    //##[0]:host [1]:groupname [2]:ostype [3]:result [4]:action [5]:viewname [6]:mailopt
    //##[7]:tcpport [8]cpulim [9]:ramlim [10]:disklim [11]:process [12]image [13]snmpcomm
    $host=$itemarray[0];
    $result=$itemarray[3];
    $action=$itemarray[4];
    $mailopt=$itemarray[6];
    $tcpport=$itemarray[7];
    $cpulim=$itemarray[8];
    $ramlim=$itemarray[9];
    $disklim=$itemarray[10];
    $process=$itemarray[11];
    if ($action=="2" && $result=="1"){ // Action=2(snmp) and Result=1(結果OK)
      $gtype="0";
      $tmstamp = date('ymdHis');
      ////////////////////////////////////////////////////////
      // read current snmp data from statistics record
      ////////////////////////////////////////////////////////
      $sql='select * from statistics where host="'.$host.'"'; 
      $stdata = getdata($sql);
      if (empty($stdata)) {
        $insql="insert into statistics (host,tstamp,gtype) values('".$host."','000000000000','9')";
        putdata($insql); 
        //
        $logmsg = 'No statistics record then create new record: ' . $insql;
        writeloge('SnmpAutoScan.php',$logmsg);
        $stdata1 = array("","000000000000","0","","","","","","");
        $stdata1[0] = $host; 
      } else {
        $stdata1 = explode(',',$stdata[0]);
      }
      $s_host = $stdata1[0];
      $s_stamp = $stdata1[1]; // statistics timer
      $s_gtype = $stdata1[2];
      if ($s_gtype=='9'){ // スタンバイ -> 未監視
         $s_gtype = '0';
      }
      $s_cpuval = nullstatis($stdata1[3]);
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
      
      ////////////////////////////////////////////////////////
      $snmpvalue = snmpdataget($itemarray); ##//current snmp data from get snmp
      ////////////////////////////////////////////////////////
      $tmstamp = date('ymdHis');
      //#print('<<<<<<< snmpdataget exit: '+tmstamp+' >>>>>>>')
      // snmpvalue(snmpdataget response) array
      // snmpvalue[0] = host  
      // snmpvalue[1] = CPU    if 255 then error
      // snmpvalue[2] = RAM    if 255 then error
      // snmpvalue[3] = Disk   if 255 then error
      // snmpvalue[4] = Process
      // snmpvalue[5] = Port
      for($cc=0;$cc<6;$cc++){
        snmpvalcheck($snmpvalue[$cc]);
        if (preg_match("/unknown/",$snmpvalue[$cc])){
          $snmpvalue[$cc]=$snmpread[$cc];
        }
      }

      // ping またはsnmp エラーで値定まらず、pythonでは255で判定している
      $record=$host.' hostramlim='.$ramlim.' readramlim='.$snmpread[2].' valramlim='.$snmpvalue[2];
      writelogd($pgm,$record);

      $upsw = '0';
      $statisupsw = '0';
        // イベントログ　相違する数だけ出力
        // 日付：時刻　ホスト　イベント種類　SNMP監視　SNMP状態　管理者　管理番号　確認　　メール送信
        //                     監視異常=2      CPU       w:80      空白    空白      未確認  未送信
      $stc = count($snmpread);  //snmpread=statistics, snmpvalue=測定値
      //$snmphost=snmpvalue[0];
      /*
      if ($host=='192.168.1.139'){
        echo '---><br>snmpread<br>';
        var_dump($snmpread);
        echo '<br>--->snmpvalue<br>';
        var_dump($snmpvalue);
      }
      */
      for ($scc=1;$scc<$stc;$scc++){
        $old_val=$snmpread[$scc];
        $new_val=$snmpvalue[$scc]; 
        if (substr($old_val,0,1) != substr($new_val,0,1)){ // 各項目比較
          if ($scc<4){ //cpu ram disk　比較
            if ($old_val=="empty" && substr($new_val,0,1)=="n"){  
              snmpeventlogmail($itemarray,"1",strval($scc),$new_val,"1"); 
            } elseif ($old_val=="empty" && substr($new_val,0,1)!="n"){ 
              snmpeventlogmail($itemarray,"2",strval($scc),$new_val,"2");
            } elseif (substr($old_val,0,1)=="n" && substr($new_val,0,1)=="w"){ 
              snmpeventlogmail($itemarray,"2",strval($scc),$new_val,"2");
            } elseif (substr($old_val,0,1)=="n" && substr($new_val,0,1)=="c"){
              snmpeventlogmail($itemarray,"2",strval($scc),$new_val,"2");
            } elseif (substr($old_val,0,1)=="w" && substr($new_val,0,1)=="c"){ 
              snmpeventlogmail($itemarray,"2",strval($scc),$new_val,"2");
            }
                          
          }else{ //4==process or 5==port
            
            if ($old_val=="empty" && $new_val=="allok"){ 
              snmpeventlogmail($itemarray,"1",strval($scc),$new_val,"1");
            } elseif ($old_val=="empty" && $new_val!="allok"){ 
              snmpeventlogmail($itemarray,"2",strval($scc),$new_val,"2");
            } elseif ($old_val=="allok" && $new_val!="allok"){ 
              snmpeventlogmail($itemarray,"2",strval($scc),$new_val,"2");
            } elseif ($old_val!="allok" && $new_val=="allok"){
              snmpeventlogmail($itemarray,"1",strval($scc),$new_val,"1");
            }
          } 

        } else {  // 前回と今回が同じ状態
        ///--------------この処理はgtypeの3が無いので実行しない　-------------- 
          $tmstamp = date('ymdHis');
          //echo $tmstamp.' '.$s_stamp.'<br>';
          $evtype=''; 
          if (((int)$tmstamp-(int)$s_stamp) > 1000){ //前回より1秒以上か
            if ($s_gtype=='3'){ // ???? 3は無い（0:未監視、1:無応答）
              $evtype='1'; //監視正常
            }else{
              $evtype='2'; //監視異常
            }
            if (!(substr($new_val,0,1)=='n' || $new_val=='empty' || $new_val=='allok')){
              //echo '--->evtype='.$evtype.' strvalscc='.$scc.' new_val='.$new_val.'<br>';
              //echo '--->timestamp '.$tmstamp. ' ' . $s_stamp.'<br>';
              //echo 'over 1000<br>';
              snmpeventlogmail($itemarray,$evtype,strval($scc),$new_val,$evtype);
            } 
          } 
        }
        ///--------------ここまで-------------- 
        ///================================================
        ///-------------statistics更新----------
        if ($statisupsw=="3" || $statisupsw="4"){
          //echo 'statistics update<br>';
          $upsql="update statistics set tstamp='".$tmstamp."',gtype='".$statisupsw."',cpuval='".$snmpvalue[1]."',ramval='".$snmpvalue[2]."',agent='".$s_agent."',diskval='".$snmpvalue[3]."',process='".$snmpvalue[4]."',tcpport='".$snmpvalue[5]."' where host='".$snmpvalue[0]."'";
          $rtn=putdata($upsql);
          if (!empty($rtn)){ // connection error or sql error or not found
            writeloge($pgm,"Failed DB Access: ".$upsql); 
            snmpeventlogmail($itemarray,"a","","","a"); // a is DB異常
          }
        }

      } //end of for

    } elseif ($action==2) { 
      $sql='select * from statistics where host="'.$host.'"'; 
      $stdata = getdata($sql);
      $stdata1 = explode(',',$stdata[0]);
      $s_gtype = $stdata1[2];
      if (! ($s_gtype=="5" || $s_gtype=="6")){  
        $upsql="update statistics set gtype='1' where host='" .$host. "'";
        $rtn=putdata($upsql);
        if (!empty($rtn)){ // connection error or sql error or not found
          writeloge($pgm,"Failed DB Access: ".$upsql); 
          snmpeventlogmail($itemarray,"a","","","a"); // a is DB異常
        }
        //writelogd($pgm,$upsql);
      }
    }
  }
} //end of for
$prcstamp = time();
$updt='update processtb set snmpstamp="'.strval($prcstamp).'"';
putdata($updt);
echo '</body></html>';
?>
