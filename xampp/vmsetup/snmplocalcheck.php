<?php
$community='private';
if($argc==2){
  $community=$argv[1];
}
$vpath_vmsetup=__DIR__;
$vpath_base=explode('/vmsetup',$vpath_vmsetup);
$vpath_ubin=$vpath_base[0].'/ubin';
///
/// アプリの存在チェック snmpcpuget.sh snmpramget.sh snmpdiskget.sh
///
$snmpgetsw=0;
$snmpcpu=$vpath_ubin.'/snmpcpuget.sh';
$snmpram=$vpath_ubin.'/snmpramget.sh';
$snmpdisk=$vpath_ubin.'/snmpdiskget.sh';
if(file_exists($snmpcpu) and file_exists($snmpram) and file_exists($snmpdisk)){
  echo "snmpcheck:snmpcpuget.sh snmpramget.sh snmpdiskget.sh は存在します".PHP_EOL;
}else{
  $snmpgetsw=1;
  echo "snmpcheck:snmpcpuget.sh snmpramget.sh snmpdiskget.sh の全て又はいずれかが存在しません".PHP_EOL;
}
///
///　snmpd サービス稼働チェック
///
$output=null;
$result=null;
$cmd="ps -ef | grep snmpd | grep -v grep | wc -l";
exec($cmd,$output,$result);
if($output[0]=='1'){
  echo 'snmpcheck:snmp サービスは動作しています'.PHP_EOL;
}else{
  echo 'snmpcheck:snmp サービスが確認出来ません'.PHP_EOL;
}
///
/// snmpポートのチェック 161
///
$output=null;
$result=null;
$cmd="netstat -uan | sed '1,2d' | awk '{print $4}'";
exec($cmd,$output,$result);
$found=0;
if($result==0){
  $lCount=count($output);
  for($i=0;$i<$lCount;$i++){;
    $lArr=explode(':',$output[$i]);
    if($lArr[1]=='161'){
      //echo '161 found';
      $found=1;
      break;
    }
  }
  if($found==1){
    echo 'snmpcheck:udp ポート161 が見つかりました、ファイアウオールは手動で確認して下さい'.PHP_EOL;
  }else{
    echo 'snmpcheck:udp ポート161 が見つかりません'.PHP_EOL;
  }
}else{
  echo "netstatコマンドに失敗しました、netstat -uan で確認して下さい".PHP_EOL;
}
///
/// ファイアウオールの開放チェック 161
///
/*
$fire=0;
if($found==1){
  $output=null;
  $result=null;
  $cmd="firewall-cmd --list-ports";
  exec($cmd,$output,$result);
  var_dump($output);
  if($result==0 and $output[0]!=""){
    $lCount=count($output);
    for($i=0;$i<$lCount;$i++){;
      $lArr=explode('/',$output[$i]);
      if($lArr[1]=='161'){
        echo '161 open'.PHP_EOL;
        $fire=1;
        break;
      }
    }
    if($fire==1){
      echo '161 open'.PHP_EOL;
    }else{
      echo '161 not open'.PHP_EOL;
    }
  }else{
    echo "firewall-cmdコマンドに失敗しました、firewall-cmd --list-ports で確認して下さい".PHP_EOL;
  }
}
*/
///
///snmpcpugetテスト
///
$output=null;
$result=null;
$cmd=$vpath_ubin.'/snmpcpuget.sh localhost linux '.$community;
exec($cmd,$output,$result);
if($result==0){
  echo 'snmpcheck:snmp機能は動作しています'.PHP_EOL;
}else{
  echo 'snmpcheck:snmp機能が確認できません、<vpath_ubin>/snmpcpuget localhost unix <community>で確認してください'.PHP_EOL;
}
///
///localhostでsnmp locationアクセス
///
$output1=null;
$result1=null;
$cmd1="snmpset -v1 -c".$community." localhost .1.3.6.1.2.1.1.6.0 s ok";
exec($cmd1,$output1,$result1);
$output2=null;
$result2=null;
$cmd2="snmpget -v1 -c".$community." localhost .1.3.6.1.2.1.1.6.0";
exec($cmd2,$output2,$result2);
if($result1==0 and $result2==0){
  $outArr=explode(':',$output2[0]);
  if(trim($outArr[3])=='ok'){
    echo 'snmpcheck:エージェント機能は動作しています'.PHP_EOL;
  }
}else{
  echo "snmpcheck:エージェント機能確認に失敗しました、communityのwrite、sysLocationをチェックして下さい".PHP_EOL;
}
?>
