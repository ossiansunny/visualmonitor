<?php
error_reporting(E_ALL & ~E_WARNING);

function phpsnmpprocess($host,$ostype,$community,$data) {
  if(substr(PHP_OS,0,6)=='Darwin'){
    $output=array();
    $result=array();
    $snmpcmd="snmpwalk -v1 -c{$community} {$host} .1.3.6.1.2.1.25.4.2.1.2"; 
    exec($snmpcmd,$output,$result);
    if($result==1){
      return "error";
    }   
    $dstr='';
    foreach($data as $item){
      $lookflag=0;
      foreach($output as $record){             ///snmp取得データの処理
        $recArr=explode(' ',$record);          ///取得データの、で配列化
        $procd=str_replace('"','',$recArr[3]); ///４列目の両側の"をとる
        $procdArr=explode(':',$procd);         ///４列名を：で配列化
        $proc=$procdArr[0];                      ///プロセス名取得
        if($ostype=='0'){
          $itemok=$item.".exe";
        }else{
          $itemok=$item;
        }  
        if($itemok == $proc){
          $lookflag=1;
          break;
        }
      }
      if($lookflag==0){
        $dstr=$dstr.$item.";";
      }
      //$lookflag=0;
    }
    if ($dstr==''){
      /// emptyは正常
      $dstr='allok';
    }else{
      $dstr=rtrim($dstr,';');
    }
    return $dstr;
       
  }else{
    $snmparray = array();
    $snmparray = snmp2_walk($host, $community, ".1.3.6.1.2.1.25.4.2.1.2",2000000,2);
    ///////////////////////////////////////////////////////////// timeout 2 sec, retry 2 ///
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
        $itemArr = explode(' ',$snmparray[$j]);
        $itemFld = str_replace("\"","",$itemArr[1]);
        $itemFldArr=explode(':',$itemFld);
        $proc=$itemFldArr[0];
        if($ostype=='0'){ 
          /// 0:windows
          if($data[$i].".exe" == $proc) {
            $openflag = 1;
            break;
          }
        }else{
          /// 1:Unix/Linux
          if($data[$i] == $proc) {
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
  
}
?>


