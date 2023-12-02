<?php
require_once "mysqlkanshi.php";
require_once "phpsnmptcpopen6.php";
require_once "phpsnmpprocess.php";
require_once "phpsnmpdiskram.php";
require_once "phpsnmpcpuload.php";
$pgm="snmpdataget.php";
function snmptrapget($host){
  $sql='select host,process from trapstatistics where host="'.$host.'"';
  $rows=getdata($sql);
  $traprecord = explode(',',$rows[0]);
  $rtndd = $traprecord[1];
  if ($rtndd=='allok'){
      $rtndd='';
  }
  return $rtndd;
}

function snmpprocget($host){
  $getvalue = snmp2_get($host,'remote',".1.3.6.1.4.1.9999.1.3.0",1000000,1);
  if (! $getvalue){
    return "error";
  }else{
    $rtnarr=explode(':',$getvalue);
    $rtnval=ltrim(rtrim(trim($rtnarr[1]),'"'),'"');
    return $rtnval;
  }  
}

function snmpdataget($itar){ 
  /// $itar はホストレコード  
  $host=$itar[0];
  $ostype=$itar[2];
  if ($ostype=='0'){
    $osname='windows';
  }else if($ostype=='1'){
    $osname='unix';
  }else{
    $osname='other';
  }
  $tcpport=$itar[7];
  $cpulim=$itar[8];
  $ramlim=$itar[9];
  $disklim=$itar[10];
  $process=$itar[11]; //top & is use trap data
  if ($itar[13]=='' || is_null($itar[13])){
    $community='public';
  }else{
    $community=$itar[13];
  }
  $snmparray = array('','','','','','');
  $snmparray[0]=$host;
  if ($ostype == "0" || $ostype == "1"){ 
    ///------------------------------------------------------------------
    /// ostype=1はWindows ostype=2はUnix
    ///------------------------------------------------------------------
    if ($cpulim != ''){  
      ///-------------------------------------------------
      /// -------windows / unix cpu 設定ありの処理--------
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
        $cpuval=intval($data);   
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
      /// ------windows / unix ram 設定ありの処理---------
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
        $ramval=intval($data);   
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
      // ------windows / unix disk設定ありの処理----------
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
        $diskval=intval($data); 
        if ($diskval >= intval($diskwc[1])){ 
        /// critical チェック
          $snmparray[3] = "c:" . strval($diskval);
        }else if ($diskval >= intval($diskwc[0])){ 
        /// warnning チェック
          $snmparray[3] = "w:" . strval($diskval);
        }else{ 
        /// normalチェック
          $snmparray[3] = "n:" . strval($diskval);
        }
      }
    }	

    if ($process != ''){ 
      //----------------------------------------------------
      // ------windows / unix process設定ありの処理---------
      //----------------------------------------------------
      $ckproc=explode(';',$process);
      $string="";
      if (substr($ckproc[0],0,1)=='&'){
        /// &付きプロセスチェック
        $traprtntb=snmpprocget($host);
        if ($traprtntb=='error' || $traprtntb=='allok'){
          $snmparray[4]='';
        }else{         
          $snmparray[4]=$traprtntb;
        }
      }else{        
        /// 通常のsnmpprocess チェック
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
      }
      $snmparray[4]=$string;
      
    }

    if ($tcpport != ''){ 
      ///-------------------------------------------------
      /// --------windows / unix port設定ありの処理-------
      ///-------------------------------------------------
      $reqlist=explode(';',$tcpport);
      $string="";
      if ($ostype=='0'){ 
        $rtntb=phpsnmptcpopenwin($host,$community,$reqlist);
      } else {
        $rtntb=phpsnmptcpopen($host,$community,$reqlist);
      }
      
      if ($rtntb=='error'){
      /// snmpエラーまたはno response
        $string='unknown'; 
      }else{
        $string = $rtntb;
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

