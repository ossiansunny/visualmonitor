<?php
error_reporting(E_ALL & ~E_WARNING);


//==================================================
//====================== windows ===================
//==================================================
function phpsnmptcpopenwin($host,$community,&$data) {
  $strings="";
  $resarray = array();
  $v4array = array();
  $v4array = snmprealwalk($host, $community, ".1.3.6.1.2.1.6.19.1.7.1",1000000,1);
  if(!$v4array){
    $strings="error";
    return $strings;
  }else{
    $keyv4=array_keys($v4array); 
    $v4item = array();
    $cnt=0;
    foreach ($keyv4 as $v4data){
      $ar2=explode('"',$v4data);
      if ($ar2[1] == '0.0.0.0'){   
        $ar3=explode('.',$ar2[2]); //echo $v4port.'\r\n';
        //echo $ar3[1].'<br>';
        $v4port=$ar3[1];
        $v4item[$cnt]=$v4port;
        $cnt++;
      }       
    }    
    
  }
  /// snmp ipv6処理
  $v6array = array();
  $v6array = snmprealwalk($host, $community, ".1.3.6.1.2.1.6.19.1.7.2",1000000,1);
  if(!$v6array){
    $strings='error';
    return $strings;
  }else{
    $keyv6=array_keys($v6array);     
    $v6item = array();
    $cnt=0;
    foreach ($keyv6 as $v6data){
      //echo $v6data.'<br>';
      $ar2 = explode('"',$v6data);
      if ($ar2[1]=='00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:00') {
        $ar3=explode('.',$ar2[2]);
        $v6port=$ar3[1]; 
        //echo $v6port.'<br>';
        $v6item[$cnt]=$v6port;
        $cnt++;
      }    
    }
  }
  ///---- table lookup --------
  $dc = count($data);
  $dstr = "";
  $v4c = count($v4item);
  $item = array();
  $oflg = 0; 
  foreach ($data as $ditem){
    if ($ditem == ""){
      continue;
    }
    ///-------IPv4 lookup--------
    $oflg = 0;
    foreach ($v4item as $v4i){
      if ($ditem == $v4i){
        $oflg = 1;
        break;
      } elseif ($ditem < $v4i) {
        break;
      } 
    }  
    
    if ($oflg == 0){
      ///-------IPv6 lookup--------
      foreach ($v6item as $v6i){
        if ($ditem == $v6i){
          $oflg = 1;
          break;
        } elseif ($ditem < $v6i) {
          break;
        }
      }
      if ($oflg == 0){
        /// v6マッチせず
        array_push($resarray,$ditem);        
      }   
    }
  }
  if (is_array($resarray) && empty($resarray)) {
    $strings='allok';
  } else {
    foreach ($resarray as $resitem) {
      $strings=$strings.$resitem.';';
    }
    $strings=rtrim($strings,';');
  }
  return $strings;
  
}

//==================================================
//====================== unix/linux ================
//==================================================

function phpsnmptcpopen($host,$community,&$data) {
  $resarray = array();
  $strings='';
  ///-----------------IPv4-----------------------
  $v4array = array();
  $v4array = snmprealwalk($host, $community, ".1.3.6.1.2.1.6.20.1.4.1",1000000,1);
  if(!$v4array){
    $strings='error';
    return $strings;
  }else{
    $keyv4=array_keys($v4array); 
    $v4item = array();
    $cnt=0;
    foreach ($keyv4 as $v4data){
      $ar = explode(".",$v4data);
      $ln = count($ar);
      $v4port=$ar[$ln-1];
      $ar2=explode('"',$v4data);
      if ($ar2[1] == '0.0.0.0'){   
        $v4item[$cnt]=$v4port;
        $cnt++;
      }       
    }    
    
  }
  ///-----------------IPv6-----------------------
  $v6array = array();
  $v6array = snmprealwalk($host, $community, ".1.3.6.1.2.1.6.20.1.4.2",1000000,1);
  if(!$v6array){
    $strings='error'; 
    return $strings;
  }else{
    $keyv6=array_keys($v6array);
    $v6item = array();
    $cnt=0;
    foreach ($keyv6 as $v6data){
      $ar = explode(".",$v6data);
      $ln = count($ar);
      $v6port = $ar[$ln-1];
      $ar2=explode('"',$v6data);
      if ($ar2[1]=='00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:00') {
        $v6item[$cnt]=$v6port;
        $cnt++;
      }    
    }
  }
  ///------------------- table lookup --------
  $dc = count($data);
  $dstr = "";
  $v4c = count($v4item);
  $item = array();
  $oflg = 0; 
  foreach ($data as $ditem){
    if ($ditem == ""){
      continue;
    }
    ///-------IPv4 lookup--------
    $oflg = 0;
    foreach ($v4item as $v4i){
      if ($ditem == $v4i){
        $oflg = 1;
        break;
      } elseif ($ditem < $v4i) {
        break;
      } 
    }  
    
    if ($oflg == 0){
      ///-------IPv6 lookup--------
      foreach ($v6item as $v6i){
        if ($ditem == $v6i){
          $oflg = 1;
          break;
        } elseif ($ditem < $v6i) {
          break;
        }
      }
      if ($oflg == 0){
        array_push($resarray,$ditem);
      }   
    }
  }
  if (is_array($resarray) && empty($resarray)) {
    $strings='allok';
  } else {
    foreach ($resarray as $resitem) {
      $strings=$strings.$resitem.';';
    }
    $strings=rtrim($strings,';');
  }
  return $strings;
}


/*
/// デバッグ
//LinuxのIPv6を無効にすれば、80番ポートはtcpで見つかる
//Linuxのsysctl.confでipv6無効にすれば、80もtcpに乗る
//======================================================Linux process===
echo "--------- windows -------------<br>";
$item = array();
$ckport = array("22","80","25","3389","11822");
$host = "192.168.1.155";
$comm = "public";
$rtn=phpsnmptcpopenwin($host,$comm,$ckport);
if ($rtn==1){
  $ckport='unknown';
}
var_dump($ckport)."<br>";


echo "---------- Unix---------------<br>\n";
$item = array();
$ckport = array("80","22","","19999","1234");
$host = "192.168.1.19";
$comm = "public";
$rtn=phpsnmptcpopen($host,$comm,$ckport);
// return 'unknow' is error, 'xx;yy' is close xx;yy, '' is allok(open)
//var_dump($rtn);
if ($rtn=='error'){
  $ckport='unknown';
  echo $rtn;
}else{
  print_r($rtn);  
}
*/
?>

