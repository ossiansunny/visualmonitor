<?php
error_reporting(E_ALL);
///----------------Unix/Windows---------------
function getagent($host,$community) {
  $resstr = snmpget($host, $community, ".1.3.6.1.2.1.1.6.0");
  $str=explode(':',$resstr);
  $strval=trim($str[1]);
  return $strval;
}

function putagent($host,$community,$value) {
  if($value=='ok'){
    $resstr = snmpset($host, $community, ".1.3.6.1.2.1.1.6.0" , "s", "ok");
  }else{
    $resstr = snmpset($host, $community, ".1.3.6.1.2.1.1.6.0" , "s", "ng");
  }
  if($resstr){
    return true;
  }else{
    return false;
  }
}
/*
//------------------------debug --------------------
//echo "---------- windows -----------------------<br>";
//???????????????????
//snmpagent.php snmpget,snmpset できない
//WMI SNMPプロバイダ　追加インストール　「アプリ」「オプション機能」「オプション機能を追加」
//snmpサービス　セキュリティ　private(read,write) public(read) remote(read) セット
//net-snmpインストール
//E:\xampp250\htdocs\kanshiphp>snmpset -v1 -cprivate localhost .1.3.6.1.2.1.1.6.0 s ok
//SNMPv2-MIB::sysLocation.0 = STRING: ok
//E:\xampp250\htdocs\kanshiphp>snmpget -v1 -cprivate localhost .1.3.6.1.2.1.1.6.0
//SNMPv2-MIB::sysLocation.0 = STRING: ok
//e:\xampp250\htdocs\kanshiphp> php snmpagent.phpで試験　 
//PHP Fatal error:  Uncaught Error: Call to undefined function snmpset()
//phpのsnmpset()がない？
//xampp control panelのphp.iniのextension=snmpを有効にしてapache再起動
//E:\xampp250\htdocs\kanshiphp>php snmpagent.php
//set completed<br>
*/
/*
$host = "127.0.0.1";
$comm = "private";
$value = 'ng';
if(putagent($host,$comm,$value)){
  echo 'set completed<br>';
}else{
  echo 'set failed<br>';
}
*/
//echo "---------- unix --------------------------<br>";
/*
$host = "127.0.0.1";
$comm = "public";
$ans=getagent($host,$comm);
echo "get value: ".$ans."<be>";
*/

?>
