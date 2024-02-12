<?php
error_reporting(E_ALL & ~E_WARNING);

function phpsnmpprocess($host,$ostype,$community,&$data) {
  $snmparray = array();
  $snmparray = snmp2_walk($host, $community, ".1.3.6.1.2.1.25.4.2.1.2",1000000,1);
  if(! $snmparray){
    return "error";
    
  }
  $c = count($snmparray);
  $item = array();
  $d =count($data);

  $dstr='';
  for($i=0;$i<$d;$i++) {
    $openflag = 0;
    for($j=0;$j<$c;$j++) {
      $item = explode(' ',$snmparray[$j]);
      $item[1] = str_replace("\"","",$item[1]);
      if($ostype=='0'){ 
        /// 0:windows
        if($data[$i] . ".exe" == $item[1]) {
          $openflag = 1;
          break;
        }
      }else{
        /// 1:Unix/Linux
        if($data[$i] == $item[1]) {
          $openflag = 1;
          break;
        }
      }
    }
    if($openflag == 0) {
      $dstr=$dstr.$data[$i].";";
    } 
  }
  if ($dstr==''){
    /// emptyは正常
    $dstr='allok';
  }else{
    $dstr=rtrim($dstr,';');
  }
  return $dstr;
}
?>


