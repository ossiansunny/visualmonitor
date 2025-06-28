<?php
error_reporting(E_ALL & ~E_WARNING);
///---------------------------------------------------
///-------- use snmp command 
///---------------------------------------------------
function cpuload($host, $community, &$data) {
  if(strtoupper(substr(PHP_OS,0,3))==='WIN' or substr(PHP_OS,0,6)==="Darwin"){
    $snmparray = array();
    $snmpcmd = "snmpwalk -v1 -c{$community} {$host} .1.3.6.1.2.1.25.3.3.1.2";
    $output = array();
    $result = array();
    exec($snmpcmd,$output,$result);
    $maxcpu=0;
    if($result==1){
      return $result;
    }else{
      foreach($output as $record){
        $recArr=explode(':',$record);
        $cpu=ltrim($recArr[3]);
        if($cpu>$maxcpu){
          $maxcpu=$cpu;
        }
      }
      $data=$maxcpu;
      return $result;
    } 
  }else{
    $snmparray = array();
    /// ‘SCPU core‚Å‚ÌÅ‘å’læ“¾
    $snmparray = snmp2_walk($host, $community, ".1.3.6.1.2.1.25.3.3.1.2",1000000,1);
    if (! $snmparray){
      $data='0';
      return 1;
    }
    $c = count($snmparray);
    $item = array();
    $load = 0;
    $maxload=0;
    for($i=0;$i<$c;$i++) {
      $item = explode(' ',$snmparray[$i]);
      if ($item[0] == 'INTEGER:') {
        $load=intval($item[1]);
        if ($load > $maxload){
          $maxload=$load;
        }
      }
    }
    $data=strval($maxload);
    return 0;
  }
}
///

?>

