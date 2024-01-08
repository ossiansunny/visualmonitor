<?php
  require_once "mysqlkanshi.php";
  $sql="select host,snmpcomm,agenthost from host where host like '127%'";
  $rows=getdata($sql);
  //$row=explode(',',$rows);
  foreach ($rows as $rowrec){
    $hdata=explode(',',$rowrec);
    var_dump($hdata);
  }
?>