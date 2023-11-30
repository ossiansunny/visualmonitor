<?php
date_default_timezone_set('Asia/Tokyo');
require_once 'mysqlkanshi.php';
require_once 'winhostping.php';
require_once 'mailsendping.php';
require_once 'snmpagent.php';

$agentflag="";
$kanrino="";
$user="";
$pgm = "MonitorCoreAuto.php";

function agentcheck(){
  $rtn='0';
  $sql='select * from layout';
  $hrows=getdata($sql);
  $sql='select host,action,result from host'; // layoutにあるもの
  $rows=getdata($sql);
  foreach ($rows as $strdata){
    $sdata=explode(',',$strdata);
    $host=$sdata[0];  // host
    $act=$sdata[1]; // action
    $rst=intval($sdata[2]); // result=0 OK, result>1 NG
    //echo "-----> ".$host." ".$act." ".$sdata[2]."<br>";
    foreach ($hrows as $hrow){
      $hitem=explode(',',$hrow);
      if ($hitem[1]==$host){
        //echo "=====> ".$host." ".$act." ".$sdata[2]."<br>";
        if ($act=='1' || $act=='2' || $act=='3'){ // action=ping snmp snmp
          if ($rst>1){ // not ok
            $rtn='1';  // not ok
            //echo "+++++> ".$host." ".$act." ".$sdata[2]."<br>";
            break;
          }
        }
      }
    }
  }
  //echo "agentcheck return code ".$rtn."<br>";
  return $rtn;
}

function agentcheckx(){
  $rtn='0';
  $sql='select result,action from host'; // layoutにあるもの
  $rtarray=getdata($sql);
  $c=count($rtarray);
  for ($i=0;$i<$c;$i++){
    $rtsact=explode(',',$rtarray[$i]);
    $act=$rtsact[1];  // action
    $rst=intval($rtsact[0]); // result=0 OK, result>1 NG
    if ($act=='1' || $act=='2' || $act=='3'){ // action=ping snmp snmp
      if ($rst>1){ // not ok
        $rtn='1';  // not ok
        break;
      }
    }
  }
  return $rtn;
}


function resultdbupdate($hostmei,$cde){
  $upsql = "update host set result='" .$cde. "' where host='" .$hostmei. "'";
  $msg = $hostmei . " Update sql: " . $upsql;
  writelogd("resultdbupdate",$msg);
  putdata($upsql);  
}

function eventlog($hostrecarray,$cde){ // $cde=3 は、eventtype=2 and confclose=1
  global $pgm; 
  global $kanrino;
  global $user;
  $cfcs="0";
  if ($cde=="3"){ // event_type 3 = 監視管理
    //$cde="2"; // event_type 2 = 監視異常
    $cfcs="2"; // 確認済
    
  }  
  $h_hostmei = $hostrecarray[0];
  $h_action = $hostrecarray[4];
  $snmptype="";
  if ($h_action=='2'){ //##action=2(snmp ping)
    $snmptype="1";
  }else{
    $snmptype="0";
  }
  $dtm=date('ymdHis');
  $kanrino="";
  $insql = "insert into eventlog(host,eventtime,eventtype,snmptype,kanrisha,kanrino,confclose) values('".$h_hostmei."','".$dtm."','".$cde."','".$snmptype."','".$user."','".$kanrino."','".$cfcs."')";
  $msg = $h_hostmei . " Eventlog Insert sql: " . $insql;  
  writelogd($pgm,$msg);
  putdata($insql);  
}

//----------------viewscan-------------------------------
//--------------------------------------------------------
function viewscan(){
  global $pgm;  
  writelogd($pgm,"Enter viewscan function");
  //## get layout
  $sql='select host from layout where host!="No Assign" or host!=""';
  $hlayout=getdata($sql);
  $c=count($hlayout);
  for ($i=0;$i<$c;$i++){
    $hostlist=explode(',',$hlayout[$i]);
    $sql='select * from host where host="'.$hostlist[0].'"';
    $garray=getdata($sql);
    $d=count($garray);
    for ($j=0;$j<$d;$j++){
    ///for grec in garray:
      $grec=$garray[$j];
      $itemarray=explode(',',$grec);
      $c_host=$itemarray[0];
      $c_result=$itemarray[3];
      $c_action=$itemarray[4];
      $c_mailopt=$itemarray[6];
      ///        
      /// action 1,2,3,4の場合 
      ///
      if ($c_action=='1' || $c_action=="2" || $c_action=="3" || $c_action=="4"){  // action=1(ping),2(snmp),3(snmp通知なし),4(agent監視)
        /// 前回結果 正常　result=1
        if ($c_result=='1'){  
          $rtn = hostping($c_host); // winhostping, windows command
          $msg1="viewscan: ".$c_host. " result=1 new action rc=:".strval($rtn);
          if ($rtn != 0){ /// ping NG
            resultdbupdate($c_host,"2"); ///ホストデータ更新 result=2
            writelogd($pgm,$msg1);
            eventlog($itemarray,"2"); ///イベントログ作成
            // mail send check (active,dead and imarray
            if ($c_mailopt=='1'){  // mailopr=1 mail send
              ping($grec,'PROBLEM');  ///メール送信
            }
          }else{
            /// statistics gtype=0にする
            $usql='update statistics set gtype="0" where host="'.$c_host.'"';
            $rtn=putdata($usql);
            if (!empty($rtn)){ // connection error or sql error or not found
              writeloge($pgm,"Failed DB Access: ".$usql); 
              eventlog($itemarray,"a"); // a is DB異常
            }
          }  
        // 前回結果 異常　result 0,2
        }else if ($c_result=='0' || $c_result=='2'){ // result=0 or 2
          $rtn = hostping($c_host);  // winhostping, winddows command
          $msg1="viewscan: ".$c_host. " result=2 new action rc=:".strval($rtn);
          writelogd($pgm,$msg1);
          // ping ok
          if ($rtn == 0){
            resultdbupdate($c_host,"1"); // update result=1
            eventlog($itemarray,"1");  //normal event
            // mail send check(active,dead and itemarray set)
            if ($c_mailopt=='1'){
              ping($grec,'RECOVERY'); //send mail for ping
            }
          // ping ng
          }else{
            resultdbupdate($c_host,"3"); // update result=3
            $gsql='select gtype from statistics where host="'.$c_host.'"';
            $rows=getdata($gsql);
            $gtype=$rows[0];   // undefine offset
            /// statistics gtype="5" -> gtype=6
            if ($gtype=="5"){
              $usql='update statistics set gtype="6" where host="'.$c_host.'"';
              $rtn=putdata($usql);
              if (!empty($rtn)){ // connection error or sql error or not found
                writeloge($pgm,"Failed DB Access: ".$usql); 
                eventlog($itemarray,"a"); // a is DB異常
              } else{
                eventlog($itemarray,"3");  //確認 event
              }
            } elseif ($gtype!="6"){
              /// statistics gtype="6" -> eventlog skip
              eventlog($itemarray,"2");  //error event
            }
          }
        /// 前回結果 障害中最終　result 9
        }else if ($c_result=="9"){
          resultdbupdate($c_host,"2"); // update result=2 roundtrip
        
        /// 前回結果　障害中　result 3,4,5,6,7,8
        }else{
          $rslt = strval(intval($c_result + 1)); //update result=current value + 1
          resultdbupdate($c_host,$rslt);
        }
      }
    }
  }
}

##//---------End of viewscan----------------------------
##//

if(!isset($_GET['param'])){  // ユーザ取得依頼
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="MonitorCoreAuto.php" method="get">';
  echo '<input type="hidden" name="param" value="">';
  echo '<input type="submit" name="next" style="display:none;" />';
  echo '</form></body></html>';
  echo '<script type="text/javascript">';
  echo 'var keyvalue = sessionStorage.getItem("user");';
  echo 'if (!keyvalue) {';
  echo '  keyvalue = "unknown";';
  echo '}';  
  echo 'document.forms["F"].elements["param"].value = keyvalue;';
  echo '</script>';
}else{     
  $user=$_GET['param'];
  $sql='select * from admintb';
  $rows=getdata($sql);
  $kanridata=explode(',',$rows[0]);
  $monintval=$kanridata[7];
  $snmpintval=$kanridata[8];
  $debug=$kanridata[9];
  $kanrino=$kanridata[10];
  $coreoldctr=$kanridata[11];
  $corenewctr=$kanridata[12];
  $coreintval=intval($monintval/2);
  $coreval=strval($coreintval);
  $msg='Debug: coreval='.$coreval;
  writelogd($pgm,$msg);

  //$agentflag="";
  //$pgm = "MonitorCoreAuto.php";

  echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
  echo '<html lang="ja">';
  echo '<head>';
  echo "<meta http-equiv='refresh' content={$coreval}>";
  echo '<link rel="stylesheet" href="kanshi1.css">';
  echo '</head>';
  echo '<body>';
  echo "<h4>Core Refresh {$coreval}sec</h4>";


  //--------------------View Process --------------------
  //echo 'enter logic<br>';
  //$msg='MonitorCoreAuto Started';
  //writelogd($pgm,$msg);
  //##-------------- every refresh ---------------
  $starttime=date('ymdHis');
  // alive count up
  $coreoldval=$corenewctr;
  if ($corenewctr>1){
    $corenewval=$corenewctr-1;
  }else{
    $corenewval=1;
  }
  $sql = 'update admintb set coreoldctr='.$coreoldval.', corenewctr='.$corenewval;
  putdata($sql);
  $msg="Retry Time ".$starttime;
  writelogd($pgm,$msg);
  //##----------------- viewscan --------------
  writelogd($pgm,"--------------->> viewscan enter");
  viewscan(); // active check and send mail every host
  writelogd($pgm,"--------------->> viewscan exit");

  //----------------- viewscan --------------
  /////////////////////////////
  //examine statistics now
  $sql='select agent from statistics where host="127.0.0.1"';
  $rows=getdata($sql);
  $oldflag=$rows[0];
  $usql="";
  $agentflag=agentcheck();
  if ($agentflag=='0'){ // 127.0.0.1にsnmpsetでok set
    putagent('127.0.0.1','private','ok'); //snmpset ok to snmplocation
    $usql='update statistics set agent="ok" where host="127.0.0.1"';
    //echo $usql.'<br>';
    if ($oldflag=='ng'){
      //if ($debug=='1'){
        writeloge($pgm,$usql);
      //}
    }
  }else{              // 127.0.0.1 to set ng by snmpset
    putagent('127.0.0.1','private','ng'); //snmpset ng to snmplocation
    $usql='update statistics set agent="ng" where host="127.0.0.1"';
    //echo $usql.'<br>';
    if ($oldflag=='ok'){
      //if ($debug=="1"){
        writeloge($pgm,$usql);
      //}
    }
  }
  //echo "-----> ".$usql."<br>";
  $rtn=putdata($usql);
  if (!empty($rtn)){ // connection error or sql error or not found
    writeloge($pgm,"Failed DB Access: ".$usql); 
    eventlog($itemarray,"a"); // a is DB異常
  }
  $endtime=date('ymdHis');
  $etimeux=strtotime($endtime);
  $stimeux=strtotime($starttime);
  $elaps = $etimeux - $stimeux;  //-----------------------????
  $msg='start:'.strval($starttime).' end:'.strval($endtime).' elaps:'.strval($elaps);
  writelogd($pgm,$msg); //debug only
  //##/////////////////////////////
  $corestamp=time(); //----------------------------------------???
  $usql='update processtb set corestamp="'.strval($corestamp).'"';
  putdata($usql);
  echo '</body></html>';
}
?>

