<?php

require_once "hostncat.php";

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
  /// $hostArr ホストレコード
  $host=$hostArr[0];
  $tcpport=$hostArr[7];
  
  ////
  $ncatNewPort = '';
  //// 該当欄空白は空白を返す
  ////
  if ($tcpport == ''){
    $ncatNewPort='empty';
  }else{   
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
  return $ncatNewPort;
}
//// return value $snmparray
//// 	'empty' 	ホストに指定なし
//// 	'unkown' 	エラー
//// 	string 		正常応答  
?>


