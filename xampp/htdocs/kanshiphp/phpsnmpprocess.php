<?php
error_reporting(E_ALL & ~E_WARNING);

function phpsnmpprocess($host,$ostype,$community,&$data) {
  $snmparray = array();
  $snmparray = snmp2_walk($host, $community, ".1.3.6.1.2.1.25.4.2.1.2",1000000,1);
  if(! $snmparray){
    return "error";
    /// 呼び出し $rtn=phpsnmpprocess();
    ///          if ($rtn=="error"){
    ///            エラー処理
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
    /// プロセス全て動作中
    $dstr='allok';
  }else{
    $dstr=rtrim($dstr,';');
  }
  return $dstr;
}
/*
/// デバッグ
function unixprocess($host,$community,&$data) {
$snmparray = array();
$snmparray = snmpwalk($host, $community, ".1.3.6.1.2.1.25.4.2.1.2",1000000,1);
if(!$snmparray){
  return 1;
}
$c = count($snmparray);
$item = array();
$d =count($data);
$dstr='';
for($i=0;$i<$d;$i++) {
  $openflag = 0;
  for($j=0;$j<$c;$j++) {
    $item = explode(' ',$snmparray[$j]); // $item[0]=String $item[1]="Process"
    $item[1] = str_replace("\"","",$item[1]); // "Process" -> Process
    if($data[$i] == $item[1]) { // check Process : snmp Process
      $openflag = 1;
      break;
    }
  }
  if($openflag == 0) {
    $dstr=$dstr.$data[$i].";";
  } 
}
$dstr=rtrim($dstr,';');
$data=$dstr;
return 0;
}   


*/
/*
echo "//=====================Windows Process =========<br>\r\n";
$ckproc = array("firefox","httpd","oracle","snmp","abcd");
$comm = "public";
$host = "192.168.1.15";
$rc=phpsnmpprocess($host,$comm,$ckproc);
if($rc == 1) {
  $ckproc='unknown';
}
echo $ckproc."\r\n";
*/
/*
echo "//================Linux Process =================<br>\r\n";
$ckproc = array(snmpd,httpd);
$comm = "public";
$host = "192.168.1.18";
$ostype = "1";
$rc = phpsnmpprocess($host,$ostype,$comm,$ckproc);
if($rc == "error") {
  $ckproc='unknown';
} 
echo $ckproc."\r\n";
var_dump($rc);
*/
?>

