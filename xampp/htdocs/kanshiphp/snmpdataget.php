<?php
require_once "mysqlkanshi.php";
require_once "phpsnmptcpopen6.php";
require_once "phpsnmpprocess.php";
require_once "phpsnmpdiskram.php";
require_once "phpsnmpcpuload.php";
///
$pgm="snmpdataget.php";
///
function snmptrapget($host){
  $sql="select host,process from trapstatistics where host='".$host."'";
  $rows=getdata($sql);
  $traprecord = explode(',',$rows[0]);
  $rtndd = $traprecord[1];
  if ($rtndd=='allok'){
      $rtndd='';
  }
  return $rtndd;
}

function snmptcpget($host){
  $getvalue = snmp2_get($host,'remote',".1.3.6.1.4.1.999999.1.3.0",1000000,1);
  if (! $getvalue){
    $rtnval="error";
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
  return $rtnval;  
}

function snmpprocget($host){
  $getvalue = snmp2_get($host,'remote',".1.3.6.1.4.1.999999.1.5.0",1000000,1);
  if (! $getvalue){
    $rtnval="error";
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
  return $rtnval;  
}

function snmpdataget($hostArr){ 
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
  $process=$hostArr[11]; //top & is use trap data
  if ($hostArr[13]=='' || is_null($hostArr[13])){
    $community='public';
  }else{
    $community=$hostArr[13];
  }
  $snmparray = array('','','','','','');
  $snmparray[0]=$host;
  if ($ostype == "0" || $ostype == "1"){ 
    ///------------------------------------------------------------------
    /// ostype=1はWindows ostype=2はUnix
    ///------------------------------------------------------------------
    if ($cpulim != ''){  
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
        $rtn=wincpuload($host,$community,$data);
      }else{
        $rtn=unixcpuload($host,$community,$data);
      }      
      if ($rtn==1){
        $snmparray[1]="unknown";
      }else{
        if (intval($data) > 100){
          $cpuval=100;
        }else{
          $cpuval=intval($data);
        }   
        if ($cpuval >= intval($cpuwc[1])){  // critical check 90
          $snmparray[1] = "c:" . strval($cpuval);
        }else if($cpuval >= intval($cpuwc[0])){ // warning check 80
          $snmparray[1] = "w:" . strval($cpuval);
        }else{ // normal
          $snmparray[1] = 'n:' . strval($cpuval);
        }
      }             
    }

    if ($ramlim != ''){ 
      ///-------------------------------------------------
      /// ------windows / unix ram 同じ処理---------
      ///-------------------------------------------------
      $ramwc = explode(':',$ramlim);
      $ramc = count($ramwc);
      if ($ramc==1 || $ramwc[1]==""){
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
        if ($ramval >= intval($ramwc[1])){  // critical check
          $snmparray[2] = "c:" . strval($ramval);
        }else if($ramval >= intval($ramwc[0])){ // warning check
          $snmparray[2] = "w:" . strval($ramval);
        }else{ // normal
          $snmparray[2] = "n:" . strval($ramval);
        }
      }
    }

    if ($disklim != ''){ 
      //--------------------------------------------------
      // ------windows / unix disk　同じ処理----------
      //--------------------------------------------------
      $diskwc = explode(':',$disklim);
      $diskc = count($diskwc);
      if ($diskc==1 || $diskwc[1]==""){
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

    if ($process != ''){ 
      //----------------------------------------------------
      // ------windows / unix process　同じ処理---------
      //----------------------------------------------------
      $ckproc=explode(';',$process);
      $string="";
      if ($ostype=='1' && substr($ckproc[0],0,1)=='&'){
        /// 拡張プロセスチェック unix ostype=1 && processtop=&
        $traprtntb=snmpprocget($host);
        if ($traprtntb=='error'){
          $string='';
        }else{         
          $string=$traprtntb;
        }
        writelogd($pgm,$host."snmpprocget return extend process ".$string);
      }else if ($ostype=='1' && substr($ckproc[0],0,1)=='%'){
        /// tラッププロセスチェック unix ostype=1 && processtop=%
        $traprtntb=snmptrapget($host);
        $string=$traprtntb;
        writelogd($pgm,$host."snmptrapget return trapped process ".$string);
        
      }else{        
        /// snmpprocess チェック
        $reqlist=array();
        foreach ($ckproc as $ckitem){
          array_push($reqlist,$ckitem);
        }
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

    if ($tcpport != ''){ 
      ///-------------------------------------------------
      /// --------windows / unix port　同じ処理-------
      ///-------------------------------------------------
      $reqlist=explode(';',$tcpport);
      $string="";
      if ($ostype=='1' && substr($ckproc[0],0,1)=='&'){
        /// 拡張プロセスチェック
        $traprtntb=snmptcpget($host);
        if ($traprtntb=='error'){
          $string='';
        }else{         
          $string=$traprtntb;
          //writeloge($pgm,"extend tcpport ".$string);
        }
      }else{  
        if ($ostype=='0'){ 
          $rtntb=phpsnmptcpopenwin($host,$community,$reqlist);
        } else {
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
    }
  }else{
    $msg=$host . " $ostype:" . $ostype . " Unknow OStype?? ";
    writeloge($pgm,$msg);
  }
  return $snmparray;
}  
?>


