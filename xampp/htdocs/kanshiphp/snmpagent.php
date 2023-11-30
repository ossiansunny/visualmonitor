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
//snmpagent.php snmpget,snmpset �ł��Ȃ�
//WMI SNMP�v���o�C�_�@�ǉ��C���X�g�[���@�u�A�v���v�u�I�v�V�����@�\�v�u�I�v�V�����@�\��ǉ��v
//snmp�T�[�r�X�@�Z�L�����e�B�@private(read,write) public(read) remote(read) �Z�b�g
//net-snmp�C���X�g�[��
//E:\xampp250\htdocs\kanshiphp>snmpset -v1 -cprivate localhost .1.3.6.1.2.1.1.6.0 s ok
//SNMPv2-MIB::sysLocation.0 = STRING: ok
//E:\xampp250\htdocs\kanshiphp>snmpget -v1 -cprivate localhost .1.3.6.1.2.1.1.6.0
//SNMPv2-MIB::sysLocation.0 = STRING: ok
//e:\xampp250\htdocs\kanshiphp> php snmpagent.php�Ŏ����@ 
//PHP Fatal error:  Uncaught Error: Call to undefined function snmpset()
//php��snmpset()���Ȃ��H
//xampp control panel��php.ini��extension=snmp��L���ɂ���apache�ċN��
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
