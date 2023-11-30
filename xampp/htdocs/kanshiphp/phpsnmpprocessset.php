<?php
error_reporting(E_ALL & ~E_WARNING);
///-------SNMPエージェントへチェックすべきプロセスをセット---------
function snmpprocessset($host,$community,$process) {
  $snmparray = array();
  $snmparray = snmp2_set($host,$community,".1.3.6.1.4.1.9999.1.2.0","s",$process,1000000,1);
  if (! $snmparray){
    return 1;
  }else{
    return 0;
  }
}

/*
/// デバッグ用
$host='gcp.sunnyblue.mydns.jp';
$community='remote';
$process='aa;bb;cc';
$rtn=snmpprocessset($host,$community,$process);
if ($rtn==0){
  echo 'set OK';
  $snmparray = snmp2_get($host,$community,".1.3.6.1.4.1.9999.1.2.0","1000000","1");
  if (! $snmparray){
    echo 'get NG';
    return 1;
  }else{
    echo 'get OK';
    //var_dump($snmparray);
    $rtnval=explode(':',$snmparray);
    //var_dump($str1);
    //$str2= $str1[1];
    //echo 'str2:'.$str2;
    //$str3=trim($str2);
    //echo 'str3:'.$str3;
    //$str4=ltrim($str3,'"');
    //echo 'str4:'.$str4;
    //$str5=rtrim($str4,'"');
    //echo 'str5:'.$str5;
    $strx=ltrim(rtrim(trim($rtnval[1]),'"'),'"');
    echo 'rtnval:'.$strx;
  }  
}else{
  echo 'NG';
}
*/
?>
