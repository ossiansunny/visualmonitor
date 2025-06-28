<?php
date_default_timezone_set('Asia/Tokyo');
require_once "mysqlkanshi.php";
require_once "hostping.php";
require_once "snmpdataget.php";
require_once 'BaseFunction.php';
require_once 'mailsend.php';
require_once 'snmpagent.php';
require_once "ncatdataget.php";
/// global
$agentSw=0;
$pgm="MonitorCoreAuto.php";
$user='';
$audio="";
/// 異常カウント 2,3,4->5 正常・異常の試行を２回スキップする、5に設定すれば３回スキップ
$resultMax='6';
///
/// ping 疎通
///
Function actionPing($hostArr)
{
  global $user, $agentSw, $resultMax;
  $newResult='';
  $newStatGtype='';
  $newEventType='';
  $host=$hostArr[0];
  $result=$hostArr[3];
  
  if($result=='0'){
    /// 初期状態ホスト
    $rtnPing=hostping($host);
    if($rtnPing==0){
      $newResult='1';
      $newStatGtype='3';
      $newEventType='1';
      eventLog($hostArr,$newEventType,'P','','','','','','');
      mailSend($hostArr,$user,'2','Ping','','','');  /// 正常復帰メール
    }else{
      $newResult='2';
      $newStatGtype='1';
      $newEventType='2';
      $agentSw=1;
      eventLog($hostArr,$newEventType,'P','','','','','','');
      mailSend($hostArr,$user,'1','Ping','','','');  /// 異常発生メール
    }
  }elseif($result=='1'){
    /// 監視中ホスト　前回正常
    $rtnPing=hostping($host);
    if($rtnPing==0){
      $newResult='1';
      $newStatGtype='3';
      $newEventType='1';
    }else{
      $newResult='2';
      $newStatGtype='1';
      $newEventType='2';
      $agentSw=1;
      eventLog($hostArr,$newEventType,'P','','','','','','');
      mailSend($hostArr,$user,'1','Ping','','','');  /// 異常発生メール
    }
  }elseif($result=='2'){
    /// 監視中ホスト　前回異常
    $rtnPing=hostping($host);
    if($rtnPing==0){
      $newResult='1';
      $newStatGtype='3';
      $newEventType='1';
      eventLog($hostArr,$newEventType,'P','','','','','','');
      mailSend($hostArr,$user,'2','Ping','','','');  /// 正常復帰メール
    }else{
      $newResult='3';
      $newStatGtype='1';
      $newEventType='2';
      $agentSw=1;
      eventLog($hostArr,$newEventType,'P','','','','','','');
      mailSend($hostArr,$user,'1','Ping','','','');  /// 異常発生メール
    }
  }else{
    /// result3からresultMax
    $newResult=strval(intval($result)+1);
    if(intval($newResult) >= intval($resultMax)){
      $newResult='2';
    }
    $newStatGtype='1';
    $agentSw=1;
  }
   
  /// update statistics 
  $timeStamp=date('ymdHis');
  $statSql="update statistics set tstamp='{$timeStamp}', gtype='{$newStatGtype}' where host='{$host}'";
  putdata($statSql);   
  return $newResult;
}
///
/// Ncat 疎通
///
Function actionNcat($hostArr,$statArr)
{
  global $user, $agentSw, $audio, $resultMax;
  $newResult='';
  $ncatNewPort='';
  $newStatGtype='';
  $newEventType='';
  $host=$hostArr[0];
  $result=$hostArr[3];
  $portArr=explode(';',$hostArr[7]);
  if($portArr[0]==''){
    $port='22';
  }else{
    $port=$portArr[0];
  }
  if($result=='1' or $result=='2'){ /// 1:ok or 2:ng
    $rtnNcat=hostncat($host,$port);
    if($rtnNcat==0){
      /// ncat ok
      $newResult='1';
      if($result=='2'){
        /// 前回NGから今回OK
        $ngToOk=1;
      }
      /// ---------------------------------------
      /// first port ncat ok
      /// 全ポートのチェック
      $host=$hostArr[0];
      $statHost=$statArr[0];
      $statPort=$statArr[8];
      $ncatOldPort=$statPort;
      ///-------------------------------------
      $ncatNewPort=ncatdataget($hostArr);
      ///------------------------------------
      if($ncatNewPort==='' or $ncatNewPort==='empty' or $ncatNewPort==='allok'){
        /// 今回正常
        $newStatGtype='3';  ///監視正常
        $newEventType='1';  /// 監視正常
        if($ncatOldPort!=$ncatNewPort){
          /// 前回異常
          eventLog($hostArr,$newEventType,'N',$ncatNewPort);
          mailsend($hostArr,$user,'2','Ncat','TcpPort','前回異常-今回正常','');
        }      
      }else{
        /// 今回異常　ポートの異常
        $newStatGtype='1'; /// 監視異常
        $newEventType='2'; /// 監視異常
        $agentSw=1;        /// エージェント異常表示
        eventLog($hostArr,$newEventType,'N',$ncatNewPort);
           
      }
    }else{
      /// first ncat ng
      $newStatGtype='1';
      $newEventType='2';
      $agentSw=1;
      if($result=='0' or $result=='1'){
        $newResult='2';
        eventLog($hostArr,$newEventType,'N','');
        mailSend($hostArr,$user,'1','Ncat','','','先頭ポート異常');
      }else{
        $newResult=strval(intval($result)+1);
        if($newResult==$resultMax){ /// max -> 2
          $newResult='2';
        }
      }  
    }  
  }else{
    /// 障害中 3,4->2 ncatせずカウントアップ
    $newStatGtype='1';
    $newEventType='2';
    $agentSw=1;
    eventLog($hostArr,$newEventType,'N','');
    $newResult=strval(intval($result)+1);
    if($newResult==$resultMax){ /// max -> 2
      $newResult='2';
    }
  }
  /// update statistics newStatGtype 1:無応答 3:正常監視 4:一部異常監視
  $timeStamp=date('ymdHis');
  $statSql="update statistics set tstamp='{$timeStamp}', gtype='{$newStatGtype}', tcpport='{$ncatNewPort}' where host='{$host}'";
  putdata($statSql);   
  return $newResult;
}
///
/// snmpPing疎通
///
Function actionSnmp($hostArr,$statArr)
{
  global $user, $agentSw, $resultMax;
  $newResult='';
  $host=$hostArr[0];
  $result=$hostArr[3];
  /// 前回正常か異常か
  if($result=='1' or $result=='2'){
    $rtnPing=hostping($host);
    if($rtnPing==0){
      ///
      ///  ping正常でsnmpチェック
      ///
      $rtnSnmp=snmpCheck($hostArr,$statArr);
      /// 
      if($rtnSnmp==0){
        $newResult='1';         /// host newresult 正常更新
      }else{
        $newResult=strval(intval($result)+1);   ///         
      }
    }else{  
      ///
      /// ping ngで障害処理
      /// 
      $newResult=strval(intval($result)+1);  
      $agentSw=1; 
      /// insert event log and send mail
      eventLog($hostArr,'2','1','');
      mailSend($hostArr,$user,'1','Ping','','',''); 
      /// reset statistics    
      $timeStamp=date('ymdHis');
      $statSql="update statistics set tstamp='{$timeStamp}',gtype='1',cpuval='',
         ramval='',diskval='',process='',tcpport='' where host='{$host}'";
      putdata($statSql);  
    }
  }else{  /// result >2
    $newResult=strval(intval($result)+1);
    if($newResult==$resultMax){
      $newResult='2';
    }  
    $agentSw=1;  
  }
  return $newResult;
}

Function snmpCheck($hostArr,$statArr)
{
  global $user, $agentSw, $audio;
  $newStatGtype='';
  $newEventType='';
  $newResult='';
  $host=$hostArr[0];
  $statHost=$statArr[0];
  $statCpu=$statArr[3];
  $statRam=$statArr[4];
  $statDisk=$statArr[6];
  $statProc=$statArr[7];
  $statPort=$statArr[8];
  $snmpOldValue=array();
  $snmpOldValue[0]=$statHost;
  $snmpOldValue[1]=$statCpu;
  $snmpOldValue[2]=$statRam;
  $snmpOldValue[3]=$statDisk;
  $snmpOldValue[4]=$statProc;
  $snmpOldValue[5]=$statPort;
  
  ///
  $snmpNewValue=snmpdataget($hostArr);
  ///
  $snmpResult='0'; /// 正常
  $mailSw=0;
  ///
  /// cpu,ram,disk check
  ///
  for($cc=1;$cc<4;$cc++){
    $newEventType='0';
    if($snmpNewValue[$cc]=='unknown'){
      $snmpNewValue[$cc]=$snmpOldValue[$cc]; //snmp 処理失敗
      $newEventType='2';
      $snmpResult='1'; /// 異常
      $agentSw=1;
      $mailSw=1; 
    }else{
      if($snmpOldValue[$cc]==''){		
        if(substr($snmpNewValue[$cc],0,1)=='n'){		
          ///snmpOLdValue:null to snmpNewValue:n 無設定から正常値 		
          $newEventType='1';          		
        }elseif(substr($snmpNewValue[$cc],0,1)=='w' or substr($snmpNewValue[$cc],0,1)=='c'){		
          ///snmpOldValue:null to snmpNewValue:w/c 無設定から警告値、危険値へ		
          $newEventType='1';          		
        }
      }elseif(substr($snmpOldValue[$cc],0,1)=='n'){
        if(substr($snmpNewValue[$cc],0,1)=='w' or substr($snmpNewValue[$cc],0,1)=='c'){
          ///snmpOldValue:n to snmpNewValue:w/c 正常値から警告または危険値 log
          $newEventType='1';
        }
      }elseif(substr($snmpOldValue[$cc],0,1)=='w'){
        if(substr($snmpNewValue[$cc],0,1)=='n'){
          ///snmpOldValue:w to snmpNewValue:n 警告値から正常値
          $newEventType='1';		
        }elseif(substr($snmpNewValue[$cc],0,1)=='c'){
          ///snmpOldValue:w to snmpNewValue:c 警告値から危険値
          $newEventType='1';		
        }
      }elseif(substr($snmpOldValue[$cc],0,1)=='c'){
        if(substr($snmpNewValue[$cc],0,1)=='n'){
          ///snmpOldValue:c to snmpNewValue:n 危険値から正常値
          $newEventType='1';
        }elseif(substr($snmpNewValue[$cc],0,1)=='w'){
          ///snmpOldValue:c to snmpNewValue:w 危険値から警告値
          $newEventType='1';
        }
      }
    }
    ///cpu,ram,disk値　異常時ログ
    ///  cpu         ram         disk 
    /// $cc=1       $cc=2       $cc=3 
    /// snmpType=2  snmpType=3  snmpType=4
    /// $cc + 1 = snmpType
    if ($newEventType!='0')	{
      eventLog($hostArr,$newEventType,strval($cc+1),$snmpNewValue[$cc]);
    }
  }
  ///mailSend		
  ///
  
  if($mailSw==1){
    mailsend($hostArr,$user,'1','Snmp','応答なし','','');
  } 
   
  /// process,port check
  ///			
  for($cc=4;$cc<6;$cc++){
    if($snmpNewValue[$cc]==='' or $snmpNewValue[$cc]==='empty' or $snmpNewValue[$cc]==='allok'){
      $newStatGtype='3';
      ///snmpNewValue:process稼働、ポート閉鎖なし
      $newEventType='1';
       /// 前回の異常が正常に変わった時ログ出力とメール送信
      if(!($snmpOldValue[$cc]==='' or $snmpOldValue[$cc]==='empty' or $snmpOldValue[$cc]==='allok') or empty($snmpOldValue[$cc])){
        eventLog($hostArr,$newEventType,strval($cc+1),$snmpNewValue[$cc]);
        /// send mail
        $snmpMailType="";
        $snmpMailValue="";
        if($cc==4){
          $snmpMailType="Process";
        }else{
          $snmpMailType="TcpPort";
        }    
        if($snmpNewValue[$cc]=="allok"){
          $snmpMailValue="全て正常";
        }elseif($snmpNewValue[$cc]=="empty"){
          $snmpMailValue='指定なし';
        }else{
          $snmpMailValue=" ".$snmpNewValue."未稼働/閉鎖";   
          mailsend($hostArr,$user,'2','Snmp',$snmpMailType,$snmpMailValue,'');         
        } 
      }
    }else{
      $newStatGtype='4';
      /// ログ出力とメール送信
      if($snmpNewValue[$cc]=='unknown'){
        $snmpNewValue[$cc]=$snmpOldValue[$cc]; ///oldをコピー
      }else{
        $newEventType='2';
        $agentSw=1;
        ///process,port異常は監視異常
        ///  $process    $rcpport
        /// $cc=4       $cc=5
        /// snmpType=5  snmpType=6
        /// $cc + 1 = snmpType
        eventLog($hostArr,$newEventType,strval($cc+1),$snmpNewValue[$cc]);    
        /// send mail
        $snmpMailType="";
        $snmpMailValue="";
        if($cc==4){
          $snmpMailType="Process";
          $snmpMailName=" 未稼働";
        }else{
          $snmpMailType="TcpPort";
          $snmpMailName=" 閉鎖";
        }    
        if($snmpNewValue[$cc]=="allok"){
          $snmpMailValue="全て正常";
        }elseif($snmpNewValue[$cc]=="empty"){
          $snmpMailValue='指定なし';
        }else{
          $snmpMailValue=" ".$snmpNewValue[$cc].$snmpMailName;   
        }
        mailsend($hostArr,$user,'1','Snmp',$snmpMailType,$snmpMailValue,'');
        
          
      }
    }
  }
  /// update statistics    
  $timeStamp=date('ymdHis');
  $statSql="update statistics set tstamp='{$timeStamp}',gtype='{$newStatGtype}',cpuval='{$snmpNewValue[1]}',
      ramval='{$snmpNewValue[2]}',diskval='{$snmpNewValue[3]}',process='{$snmpNewValue[4]}',
      tcpport='{$snmpNewValue[5]}' where host='{$host}'";
  putdata($statSql);
  /// return        
  return $snmpResult;		
}
///
/// eventlog
///
Function eventLog($hostArr,$newEventType,$snmpType,$snmpValue,$kanrimei='',$kanrino='',$cnfcls='0',$mailsend='0',$message='')
{	
  global $user;	
  $host=$hostArr[0];	
  $tStamp=date('ymdHis');	
  if($hostArr[15]=='0'){  /// eventlog出力
    $eventSql="insert into eventlog values('{$host}','{$tStamp}','{$newEventType}','{$snmpType}','{$snmpValue}','{$user}',
        '{$kanrimei}','{$kanrino}','{$cnfcls}','{$mailsend}','{$message}')";	
    putdata($eventSql);	
  }	
}  

///
/// main
///

if(!isset($_GET['param'])){ /// ユーザ取得依頼
  print '<html>';
  print "<body bgcolor=khaki>";
  print '<h4><font color=gray>お待ち下さい....</font></h4>';
  print "</body></html>";
  paramGet($pgm);
  ///
}else{
  $startUnix=time();     
  paramSet();
  ///
  $user_sql='select authority,bgcolor,audio from user where userid="'.$user.'"';
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    branch('logout.php',$msg);
  }
  $userArr=explode(',',$userRows[0]);
  $authority=$userArr[0];
  $bgColor=$userArr[1];
  $audio=$userArr[2];
  ///
  $admin_sql='select monintval,snmpintval,debug,kanrino,logout from admintb';
  $adminRows=getdata($admin_sql);
  $adminArr=explode(',',$adminRows[0]);
  $monIntVal=$adminArr[0];
  $snmpIntVal=$adminArr[1];
  $debug=$adminArr[2];
  $adminNum=$adminArr[3];
  $adminLogout=$adminArr[4];
  $coreIntVal=intval($snmpIntVal);    
  $coreStrVal=strval($coreIntVal);
  
  /// 
  print '<html>';
  print '<head>';
  if($adminLogout=='0'){
    print "<meta http-equiv='refresh' content={$coreStrVal}>";
  }
  print '<link rel="stylesheet" href="css/CoreMenu.css">';
  print '</head>';
  print "<body class={$bgColor}>";
  print '<div ><table><tr><td >';
  print "<h5><font color=white>Core Refresh {$coreStrVal}sec</font></h5>";
  print '</td></tr></table></div>';
  $agentSw=0;
  $saveLocalAction='';
  $saveLocalComm='';
  $layoutSql="select host from layout";
  $layoutRows=getdata($layoutSql);
  if(empty($layoutRows)){
    $msg="layoutテーブルにホストデータがありません";
    writelogd($pgm,$msg);
  }else{  
    foreach($layoutRows as $layoutRow){
    
      if(empty($layoutRow) or $layoutRow=='NoAssign'){
        continue;
      }else{
        $hostSql="select host,groupname,ostype,result,action,viewname,mailopt,tcpport,cpulim,ramlim,disklim,process,image,snmpcomm,agenthost,eventlog,standby from host where host='{$layoutRow}'";
        $hostRows=getdata($hostSql);
        if(empty($hostRows)){
          $msg="レイアウトにあるホスト".$layoutRow."がホストテーブルにありません";
          writeloge($pgm,$msg); 
        }else{      
          $newResult='';
          $hostArr=explode(',',$hostRows[0]);
          $host=$hostArr[0];
          if($hostArr[3]==''){  // result='' -> result='0'
            $hostArr[3]='0';
          }  
          $action=$hostArr[4];
          $snmpcomm=$hostArr[13];
          /// read statistics table
          $statSql="select host,tstamp,gtype,cpuval,ramval,agent,diskval,process,tcpport from statistics where host='{$host}'";
          $statRows=getdata($statSql);
          if(empty($statRows)){
            $msg="ホスト".$host."に対するstatisticsテーブルがありません";
            writelogd($pgm,$msg);  
          }else{
            $statArr=explode(',',$statRows[0]);
            $statGtype=$statArr[2];
          
            if($statGtype=='5') {
              ///
              /// 確認、確認済、クローズ
              ///
              $cnfcls='2';  ///eventlogの確認済
              $eventLogSql="select snmptype,snmpvalue,kanrimei,kanrino,message from eventlog where host='{$host}' and eventtype='3' and confclose='1' order by eventtime desc";
              $eventLogRows=getdata($eventLogSql);
              $eventLogArr=explode(',',$eventLogRows[0]);
              $snmptype=$eventLogArr[0];
              $snmpvalue=$eventLogArr[1];
              $kanrimei=$eventLogArr[2];
              $kanrino=$eventLogArr[3];
              $message=$eventLogArr[4];
              eventLog($hostArr,'3',$snmptype,$snmpvalue,$kanrimei,$kanrino,$cnfcls,'0',$message);
              $newStatGtype='6';  ///ststisticsの確認済
              $statSql="update statistics set gtype='{$newStatGtype}', status='2' where host='{$host}'";
              putdata($statSql);
            }else{
              ///
              ///  action
              ///
              if($host=='127.0.0.1'){
                $saveLocalAction=$action;
                $saveLocalComm=$snmpcomm; 
              }     
              switch($action){
                case '1': 
                  $newResult=actionPing($hostArr);
                  break;
                case '2':
                case '3':
                  $newResult=actionSnmp($hostArr,$statArr);
                  break;
                case '4':
                  $newResult=actionPing($hostArr);
                  break;
                case '5':
                  $newResult=actionNcat($hostArr,$statArr);
                  break;
              }  
            }
          }
          /// update host
          $hostSql="update host set result='{$newResult}',standby='' where host='{$host}'";
          putdata($hostSql);
          /// 効果音再生
          if ($newResult=='2') {
            $audioSrc='css/'.$audio;
            print "<audio src={$audioSrc} autoplay></audio>";
          }  
        }
      }  
    }
    //127処理 $agentSw=0 正常 $agensw=1 異常
    if($saveLocalAction=='4'){
      ///
      /// Agent処理
      ///
      $agentSql="select agent from statistics where host='127.0.0.1'";
      $agentRows=getdata($agentSql);
      if(empty($agentRows)){
        $msg="127.0.0.1エージェントstatisticsがありません";
        writelogd($pgm,$msg);
      }else{
        $agentValue='';
        if($agentSw==0){
          $agentValue='ok';
        }else{
          $agentValue='ng';
        }
        $agentSql="update statistics set agent='{$agentValue}' where host='127.0.0.1'";
        putdata($agentSql);
        putagent('127.0.0.1',$saveLocalComm,$agentValue);
      }
    }
  }
  /// lasttimeはlogin時に0にリセット、MonitorCoreAutoの実行時に実行時刻をセット
  /// MonitorManagerが監視終了時刻で表示する
  $lastTime=date('G:i:s');
  $admin_sql = "update admintb set laststamp='{$lastTime}'";
  putdata($admin_sql);
  ///
  $nowtime=time();
  $proc_sql = "update processtb set corestamp={$nowtime}";
  putdata($proc_sql);
  print '</body></html>';
  /// 処理時間測定
  $endUnix=time();
  $elapsUnix=$endUnix-$startUnix;
  if($debug=='7'){
    $elapsStamp="---- elaps time ".strval($elapsUnix).' second';
    writeloge($pgm,$elapsStamp);
  }  
  
}  
?>
