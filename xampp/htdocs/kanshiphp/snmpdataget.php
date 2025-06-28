<?php
require_once "phpsnmptcpopen6.php";
require_once "phpsnmpprocess.php";
require_once "phpsnmpcpuload.php";
require_once "phpsnmpdiskramload.php";
require_once "hostncat.php";
require_once "mysqlkanshi.php";
///
$pgm="snmpdataget.php";
///
function deldqt($target){
  $rtnval=rtrim(ltrim($target,'"'),'"');
  return $rtnval;
}

/// snmptcpport ncat拡張機能  
function snmptcpgetext($host,$portlist){
  $portArr=explode(';',$portlist);
  $rtnList="";
  $rtnCde=0;
  foreach ($portArr as $port){
    $rtnCde=hostncat($host,$port);
    if ($rtnCde!=0) {
      $rtnList=$rtnList.$port.';';
    }
  }
  $rtnList=rtrim($rtnList,";");
  if ($rtnList=='') {
    $rtnList="allok";
  }
  return $rtnList;  
}

function snmptcpget($host,$comm){
  global $pgm;
  $getvalue = snmp2_get($host,$comm,".1.3.6.1.4.1.999999.1.3.0",1000000,1);
  if (! $getvalue){
    $rtnval="error";
    writelogd("snmpdataget.php",$host." php snmp2_get port error return");
  }else{
    if ($getvalue=="\"\""){
      $rtnval="allok";
    }else{
      $rtnarr=explode(':',$getvalue);
      $rtnvalsp=ltrim(rtrim(trim($rtnarr[1]),'"'),'"');
      $rtnval=str_replace(' ',';',$rtnvalsp);
    }
  }
  /// $rtnval='error' snmp2_getに問題ある場合
  /// $rtnval='empty' 999999.1.3.0 が空(setされていない、設定もれ)
  /// $rtnval='allok' 全てのポートが開いている
  /// $rtnval='xx;yy' xxとyyポートが閉じている  
  return $rtnval;  
}

function snmpprocget($host,$comm){
  global $pgm;
  $getvalue = snmp2_get($host,$comm,".1.3.6.1.4.1.999999.1.5.0",1000000,1);
  if (! $getvalue){
    $rtnval="error";
    writelogd("snmpdataget.php",$host." php snmp2_get process error return");
  }else{
    if ($getvalue=="\"\""){
      $rtnval="allok";
    }else{
      $rtnarr=explode(':',$getvalue);
      $rtnvalsp=ltrim(rtrim(trim($rtnarr[1]),'"'),'"');
      $rtnval=str_replace(' ',';',$rtnvalsp);
      return $rtnval;
    }
  }
  /// $rtnval='error' snmp2_getに問題ある場合
  /// $rtnval='empty' 999999.1.5.0 が空(setされていない、設定もれ)
  /// $rtnval='allok' 全てのプロセスが動作している
  /// $rtnval='xx;yy' xxとyyプロセスが動作していない
  return $rtnval;  
}

function snmpdataget($hostArr){
  global $pgm; 
  /// $hostArr ホストレコード
  $host=$hostArr[0];
  $ostype=$hostArr[2];
  if ($ostype=='0'){
    $osname='windows';
  }else if($ostype=='1'){
    $osname='unix';
  }else{
    $osname='other';
  }
  $tcpport=$hostArr[7];
  $cpulim=$hostArr[8];
  $ramlim=$hostArr[9];
  $disklim=$hostArr[10];
  $process=$hostArr[11]; ///top & is use trap data
  $community=$hostArr[13];
  if ($hostArr[13]=='' || is_null($hostArr[13])){
    $community='public';
  }
  ////
  $snmparray = array('','','','','','');
  //// 該当欄空白は空白を返す
  ////
  $snmparray[0]=$host;
  if ($ostype == "0" or $ostype == "1" or $ostype == "2"){ 
    ///------------------------------------------------------------------
    /// ostype=1はWindows ostype=2はUnix
    ///------------------------------------------------------------------
    if ($cpulim == ''){
      $snmparray[1]='empty';
    }else{    
      ///-------------------------------------------------
      /// -------windows / unix cpu 同じ処理--------
      ///-------------------------------------------------
      
      $cpuwc = explode(':',$cpulim);
      $cpuc = count($cpuwc);
      if ($cpuc==1 || $cpuwc[1]==""){
        $cpuwc[1]=$cpuwc[0];
      }
      $data="";      
      if ($ostype=='0'){        
        $rtn=cpuload($host,$community,$data);
      }else{
        $rtn=cpuload($host,$community,$data);
      } 
      if ($rtn==1){
        $snmparray[1]="unknown";
      }else{
        if (intval($data) > 100){
          $cpuval=100;
        }else{
          $cpuval=intval($data);
        }   
        if ($cpuval >= intval($cpuwc[1])){  /// critical check 90
          $snmparray[1] = "c:" . strval($cpuval);
        }else if($cpuval >= intval($cpuwc[0])){ /// warning check 80
          $snmparray[1] = "w:" . strval($cpuval);
        }else{ /// normal
          $snmparray[1] = 'n:' . strval($cpuval);
        }
      }             
    }

    if ($ramlim == ''){
      $snmparray[2]='empty';
    }else{   
      ///-------------------------------------------------
      /// ------windows / unix ram 同じ処理---------
      ///-------------------------------------------------
      $ramwc = explode(':',$ramlim);
      $ramc = count($ramwc);
      if ($ramc==1 or $ramwc[1]==""){
         $ramwc[1]=$ramwc[0];
      }
      $data="";
      if ($ostype=='0'){
        $rtn=winramload($host,$community,$data);
      }else{
        $rtn=unixramload($host,$community,$data);
      }
      if ($rtn==1){
        $snmparray[2]="unknown";
      }else{
        if (intval($data)>100){
          $ramval=100;
        }else{
          $ramval=intval($data);
        }   
        if ($ramval >= intval($ramwc[1])){  /// critical check
          $snmparray[2] = "c:" . strval($ramval);
        }else if($ramval >= intval($ramwc[0])){ /// warning check
          $snmparray[2] = "w:" . strval($ramval);
        }else{ /// normal
          $snmparray[2] = "n:" . strval($ramval);
        }
      }
    }
    if ($disklim == ''){
      $snmparray[3]='empty';
    }else{   
      ///--------------------------------------------------
      /// ------windows / unix disk　同じ処理----------
      ///--------------------------------------------------
      $diskwc = explode(':',$disklim);
      $diskc = count($diskwc);
      if ($diskc==1 or $diskwc[1]==""){
        $diskwc[1]=$diskwc[0];
      }
      $data="";      
      if ($ostype=='0'){
        $rtn=windiskload($host,$community,$data);
      }else{
        $rtn=unixdiskload($host,$community,$data);
      }      
      if ($rtn==1){
        $snmparray[3]="unknown";        
      }else{
        if (intval($data)>100){
          $diskval=100;
        }else{
          $diskval=intval($data);
        }  
        if ($diskval >= intval($diskwc[1])){ 
        /// critical 危険値
          $snmparray[3] = "c:" . strval($diskval);
        }else if ($diskval >= intval($diskwc[0])){ 
        /// warnning 警告値
          $snmparray[3] = "w:" . strval($diskval);
        }else{ 
        /// normal　正常値
          $snmparray[3] = "n:" . strval($diskval);
        }
      }
    }	
    if ($process == ''){
      $snmparray[4]='empty';
    }else{   
      ///----------------------------------------------------
      /// ------windows / unix process　同じ処理---------
      ///----------------------------------------------------
      $string="";
      if ($ostype=='1' and substr($process,0,1)=='&'){
        /// 拡張プロセスチェック unix ostype=1 && processtop=&
        $rtntb=snmpprocget($host,$community);
        if ($rtntb=='error'){
          $string='unkown';
        }else if($rtntb=='empty'){
          $rtnCde=snmpprocessset($host,$community,substr($process,1));
          if ($rtnCde==1){
            writeloge($pgm,"host:".$host." community:".$community." process snmpset failed");
          }
          $string='unkown';
        }else{
          $string=$rtntb;
        }
        writelogd($pgm,$host."snmpprocget return extend process ".$string);
      }else{   
        /// 基本プロセスチェック　snmp応答プロセスとチェックプロセス配列(reqlist)比較
        $reqlist=explode(';',$process);
        $rtntb=phpsnmpprocess($host,$ostype,$community,$reqlist);
        if ($rtntb=='error'){
          $string='unknown';
        }else{
          $string=$rtntb;
        }
        writelogd($pgm,$host."phpsnmpprocess return normal process ".$string);
      }
      $snmparray[4]=$string; 
    }
    
    if ($tcpport == ''){
      $snmparray[5]='empty';
    }else{   
      ///-------------------------------------------------
      /// --------windows / unix port　同じ処理-------
      ///-------------------------------------------------
      $string="";
      if (substr($tcpport,0,1)=='%'){
        /// 拡張 NCAT TCPポートチェック
        $rtntb=snmptcpgetext($host,substr($tcpport,1));
        $string=$rtntb;
      }else if ($ostype=='1' and substr($tcpport,0,1)=='&'){
        /// 拡張 VMMIB TCPポートチェック
        $rtntb=snmptcpget($host,$community);
        if ($rtntb=='error'){
          $string='unkown';
        }else if($rtntb=='empty'){
          $rtnCde=snmptcpportset($host,$community,substr($tcpport,1));
          if ($rtnCde==1){
            writeloge($pgm,"host:".$host." community:".$community." tcp port snmpset failed");
          }
          $string='unkown';
        }else{
          $string=$rtntb;
        }
      }else{
        /// 基本TCPポートチェック  
        $reqlist=explode(';',$tcpport);
        if ($ostype=='0'){     /// get windows tcpport 
          $rtntb=phpsnmptcpopenwin($host,$community,$reqlist);
        }elseif($ostype=='2'){ /// get macOSX tcpport
          $rtntb=phpncattcpopenmac($host,$community,$reqlist);  
        } else {               /// get unix/linux tcpport 
          $rtntb=phpsnmptcpopen($host,$community,$reqlist);
        }      
        if ($rtntb=='error'){
        /// snmp no response
          $string='unknown'; 
        }else{
          $string = $rtntb;
        }
      }
      $snmparray[5] = $string;
      if($host=='192.168.1.171'){
        writeloge($pgm,$snmparray[5]);
      }  
    }
  }else{
    // ostype 0,1 以外
    $msg=$host . " $ostype:" . $ostype . " Unknown OStype?? ";
    writeloge($pgm,$msg);
    $snmparray[1]='unknown';
    $snmparray[2]='unknown';
    $snmparray[3]='unknown';
    $snmparray[4]='unknown';
    $snmparray[5]='unknown';
  }
  
  return $snmparray;
}

?>


