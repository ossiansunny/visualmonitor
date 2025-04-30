<?php

require_once "hostncat.php";
//require_once "mysqlkanshi.php";

///
$pgm="ncatdataget.php";
///
function deldoublequote($target){
  $rtnval=rtrim(ltrim($target,'"'),'"');
  return $rtnval;
}

/// tcpport ncat拡張機能  

function ncattcpget($host,$portlist){
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


function ncatdataget($hostArr){
  global $pgm; 
  //var_dump($hostArr);
  /// $hostArr ホストレコード
  $host=$hostArr[0];
  $tcpport=$hostArr[7];
  //var_dump($tcpport);
  
  ////
  $ncatNewPort = '';
  //// 該当欄空白は空白を返す
  ////
  //var_dump($tcpport);
  if ($tcpport == ''){
    $ncatNewPort='empty';
  }else{   
    //var_dump($tcpport);
    $string="";
    $tcpPortList='';
    if (substr($tcpport,0,1)=='%'){
      /// 拡張 NCAT TCPポートチェック
      $tcpPortList=substr($tcpport,1);
    }else{
      $tcpPortList=$tcpport;
    }    
    $rtntb=ncattcpget($host,$tcpPortList);
    $ncatNewPort = $rtntb;
    
  }
  //var_dump($snmparray);
  return $ncatNewPort;
}
//// return value $snmparray
//// 	'empty' 	ホストに指定なし
//// 	'unkown' 	エラー
//// 	string 		正常応答  
/*
$hostArr=array('192.168.1.139','notused','1','1','2','MailServer','1','80;443','80:90','80:90','80:90','httpd','server.png','public','','1','0');
$snmpNewValue=array();
$snmpNewValue=ncatdataget($hostArr);
var_dump($snmpNewValue);
*/
?>


