<?php
///////////////////////////////////////////////////////////////
///
/// /// phpsnmptcpopen6.php ///
///
/// /// 処理OS ///
/// このアプリが動作する基本OS
/// Windows  PHPのsnmp関数実行
/// Linux    PHPのsnmp関数実行
/// MacOSX   OSインストールsnmpコマンドをexec関数で実行
///
/// /// 処理概要
/// 1.エージェントホストがWindowsの場合、
///   IPv4ポートはOID 1.3.6.1.2.1.6.19.1.7.1 IPv6ポートはOID 1.3.6.1.2.1.6.19.1.7.2で
///   解放ポートを取得、要求ポートが閉鎖されていればそのポート番号を返す
/// 2.エージェントホストがWindows以外の場合
///   IPv4ポートはOID 1.3.6.1.2.1.6.20.1.4.1 IPv6ポートはOID 1.3.6.1.2.1.6.20.1.4.2で
///   解放ポートを取得、要求ポートが閉鎖されていればそのポート番号を返す
/// 3.要求ポートがemptyの場合、および要求ポートが全て開放されている場合は、'allok'を返す
///   無応答などsnmpエラーの場合は、errorを返す
/// 4.アプリ関数内の引数
///   第一引数：エージェントホスト名（文字列）
///   第二引数：コミュニティ名（文字列）
///   第三引数：要求ポート（文字列の配列）
/// 5.アプリからの戻り値
///   'error'　エラー
///   'allok'  正常または要求ポートなし
///   'xx;yy'  xxポート、yyポート閉鎖 
///     
/// /// 変更履歴 ///
/// 2025/4   /usr/bin/mrtgrun /usr/bin/plotgraph パス指定へ変更
///
///
///////////////////////////////////////////////////////////////

error_reporting(E_ALL & ~E_WARNING);
///
/// get vpath_ncat
///
require_once "varread.php";
$vpathParam=array("vpath_ncat");
$vpathRows=pathget($vpathParam);
$vpathNcat=$vpathRows[0];

///==================================================
///====================== windows ===================
///==================================================
function phpsnmptcpopenwin($host,$community,$data) {
  if(substr(PHP_OS,0,6)=='Darwin'){
    /// MacOSX snmp関数使用できないため、snmpコマンド使用
    $result=array();
    $output=array();
    $snmpcmd="snmpwalk -v1 -c{$community} {$host} .1.3.6.1.2.1.6.19.1.7.1";
    exec($snmpcmd,$output,$result);
    if($result==1){
      $strings="error";
      return $strings;
    }else{
      $v4item=array();
      foreach($output as $record){
        $ar2=explode('"',$record);
        if($ar2[1] == '0.0.0.0'){
          $ar3=explode('.',$ar2[2]);
          $v4port=$ar3[1];
          array_push($v4item,$v4port);
        }        
      }      
    }
    $result=array();
    $output=array();
    $snmpcmd="snmpwalk -v1 -c{$community} {$host} .1.3.6.1.2.1.6.19.1.7.2";
    exec($snmpcmd,$output,$result);
    if($result==1){
      $strings="error";
      return $strings;
    }else{
      $v6item=array();
      foreach($output as $record){
        $ar2=explode('"',$record);
        if ($ar2[1]=='00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:00') {
          $ar3=explode('.',$ar2[2]);
          $v6port=$ar3[1];
          array_push($v6item,$v6port);
        }       
      }
    }   
    ///
    ///---- table lookup --------
    ///
    $resarray=array();
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
          /// v6 found
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
  }else{
    /// Windiws/Linuxはsnmp関数使用
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
          $ar3=explode('.',$ar2[2]); 
          $v4port=$ar3[1];
          $v4item[$cnt]=$v4port;
          $cnt++;
        }       
      }
    }
    
    /// snmp ipv6????
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
        $ar2 = explode('"',$v6data);
        if ($ar2[1]=='00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:00') {
          $ar3=explode('.',$ar2[2]);
          $v6port=$ar3[1]; 
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
          /// v6 found
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
}

///==================================================
///====================== unix/linux ================
///==================================================

function phpsnmptcpopen($host,$community,$data) {
  if(substr(PHP_OS,0,6)=='Darwin'){
    /// MacOSX snmp関数使用できないため、snmpコマンド使用
    $result=array();
    $output=array();
    $snmpcmd="snmpwalk -v1 -c{$community} {$host} .1.3.6.1.2.1.6.20.1.4.1";
    exec($snmpcmd,$output,$result);
    if($result==1){
      $strings="error";
      return $strings;
    }else{
      $v4item=array();
      foreach($output as $record){
        $ar2=explode('"',$record);
        if($ar2[1] == '0.0.0.0'){
          $ar3=explode('=',$ar2[2]);
          $v4port=ltrim(rtrim($ar3[0]),'.');
          array_push($v4item,$v4port);
        }        
      }      
    }
    $result=array();
    $output=array();
    $snmpcmd="snmpwalk -v1 -c{$community} {$host} .1.3.6.1.2.1.6.20.1.4.2";
    exec($snmpcmd,$output,$result);
    if($result==1){
      $strings="error";
      return $strings;
    }else{
      $v6item=array();
      foreach($output as $record){
        $ar2=explode('"',$record);
        if ($ar2[1]=='00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:00') {
          $ar3=explode('=',$ar2[2]);
          $v6port=ltrim(rtrim($ar3[0]),'.');
          array_push($v6item,$v6port);
        }       
      }
    }   
    ///
    ///---- table lookup --------
    ///
    $resarray=array();
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
          /// v6 found
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
    
  }else{  
    ///　snmp関数使用
    
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
}
///==================================================
///====================== MacOSX ================
///==================================================
function phpncattcpopenmac($host,$community,$data) {
  global $vpathNcat;
  /// 
  $strings='';
  foreach($data as $record){
    $output=array();
    $result=array();
    $ncatcmd=$vpathNcat." -z -w 0.4 ".$host." ".$record;
    exec($ncatcmd,$output,$result);
    if($result==1){
      $strings=$strings.$record.';';
    }  
  }  
  if(empty($strings)){
    $strings='allok';
  }
  $strings=rtrim($strings,';');
  return $strings;
}

function phpsnmptcpopenmac($host,$community,$data) {
  if(substr(PHP_OS,0,6)=='Darwin'){
    /// MacOSX snmp関数使用できないため、snmpコマンド使用
    $result=array();
    $output=array();
    $snmpcmd="snmpwalk -v1 -c{$community} {$host} .1.3.6.1.2.1.6.13.1.1";
    exec($snmpcmd,$output,$result);
    if($result==1){
      $strings="error";
      return $strings;
    }else{
      $v4item=array();
      foreach($output as $record){
        $ar2=explode('e.0.0.0.0.',$record);
        if(!empty($ar2[1])){
          $ar3=explode('.',$ar2[1]);
          $v4port=$ar3[0];
          array_push($v4item,$v4port);
          
        }          
      }      
    }
    ///
    ///---- table lookup --------
    ///
    $resarray=array();
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
          /// v6 found
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
    
  }else{  
    ///　snmp関数使用
    
    $resarray = array();
    $strings='';
    ///-----------------IPv4-----------------------
    $v4array = array();
    $v4array = snmprealwalk($host, $community, ".1.3.6.1.2.1.6.13.1.1",1000000,1);
    if(!$v4array){
      $strings='error';
      return $strings;
    }else{
      $keyv4=array_keys($v4array); 
      $v4item = array();
      foreach ($keyv4 as $v4data){
        $ar2 = explode('e.0.0.0.0.',$v4data);
        if(!empty($ar2[1])){
          $ar3=explode('.',$ar2[1]);
          $v4port=$ar3[0];
          array_push($v4item,$v4port);          
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
}

?>
