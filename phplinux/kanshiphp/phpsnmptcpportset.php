<?php
error_reporting(E_ALL & ~E_WARNING);
///-------SNMP プライベートMIBにset---------
function snmptcpportset($host,$community,$tcpport) {
  if(substr(PHP_OS,0,6)=="Darwin"){
    $output=array();
    $result=array();
    $snmpcmd="snmpset -v1 -c{$community} {$host} .1.3.6.1.4.1.999999.1.2.0 s {$tcpport}";
    exec($snmpcmd,$output,$result);
    if ($result==1){
      return 1;
    }else{
      return 0;
    }
  }else{
    $snmparray = array();
    $snmparray = snmp2_set($host,$community,".1.3.6.1.4.1.999999.1.2.0","s",$tcpport,1000000,1);
    if (! $snmparray){
      return 1;
    }else{
      return 0;
    }
  }
}
///-------SNMP プライベートMIBをget---------未使用
function snmptcpportget($host,$community) {
  if(substr(PHP_OS,0,6)=="Darwin"){
    $output=array();
    $result=array();
    $getvalue='';
    $rtnval='';
    $snmpcmd="snmpget -v1 -c{$community} {$host} .1.3.6.1.4.1.999999.1.3.0";
    exec($snmpcmd,$output,$result);
    if ($result==1){
      $rtnval="error";
    }else{
      $snmpArr=explode('= ',$output[0]);
      $getvalue=$snmpArr[1];
      if ($getvalue=="\"\""){					/// ""
        $rtnval="allok";      
      }else{
        
        $rtnval=str_replace('"','',$getvalue);
      }
    }
    return $rtnval;  
  }else{
    $getvalue = snmp2_get($host,$community,".1.3.6.1.4.1.999999.1.3.0",1000000,1);
    if (! $getvalue){
      $rtnval="error";
    }else{
      if ($getvalue=="\"\""){					/// ""
        $rtnval="allok";      
      }else{
        $rtnarr=explode(':',$getvalue);				///STRING: "80" 分離
        $rtnvalsp=ltrim(rtrim(trim($rtnarr[1]),'"'),'"');         ///"80"のDoubleQuoteを削除
      }
    }
  }
}

?>

