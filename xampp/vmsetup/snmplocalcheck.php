<?php
$community='private';
if($argc==2){
  $community=$argv[1];
}
$vpath_vmsetup=__DIR__;
if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
  $vpath_base=explode('\vmsetup',$vpath_vmsetup);
}else{
  $vpath_base=explode('/vmsetup',$vpath_vmsetup);
}
$vpath_ubin=$vpath_base[0].'/ubin';
///
/// アプリのubin存在チェック snmpcpuget snmpramget snmpdiskget
///
$snmpgetsw=0;
if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
  $cpuget='snmpcpuget.exe';
  $ramget='snmpramget.exe';
  $diskget='snmpdiskget.exe';
}else{
  $snmpcpu='snmpcpuget.sh';
  $snmpram='snmpramget.sh';
  $snmpdisk='snmpdiskget.sh';
}
$snmpcpu=$vpath_ubin.'/'.$cpuget;
$snmpram=$vpath_ubin.'/'.$ramget;
$snmpdisk=$vpath_ubin.'/'.$diskget;
if(file_exists($snmpcpu) and file_exists($snmpram) and file_exists($snmpdisk)){
  echo "snmpcheck: {$cpuget} {$ramget} {$diskget} は存在します".PHP_EOL;
}else{
  $snmpgetsw=1;
  echo "snmpcheck: {$cpuget} {$ramget} {$diskget} の全て又はいずれかが存在しません".PHP_EOL;
}
///
///　snmpd サービス稼働チェック
///
$snmpprocsw=0;
if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
  /// windows
  $cmd="tasklist | findstr \"snmp.exe\"";
  $output=null;
  $result=null;
  exec($cmd, $output, $result);
  if(empty($output)){
    $snmpprpcsw=1;
  }
}else{
  /// linux
  $cmd="ps -ef | grep snmpd | grep -v grep | wc -l";
  exec($cmd,$output,$result);
  if($output[0]!='1'){
    $snmpprocsw=1;
  }
}
if($snmpprocsw==1){
  echo 'snmpcheck: snmp サービスが確認出来ません'.PHP_EOL;
}else{
  echo 'snmpcheck: snmpサービスは稼働しています'.PHP_EOL;
}  


///
/// snmpポートのチェック 161
///
$output=null;
$result=null;
$snmpportsw=0;
if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
  /// windows
  $cmd="netstat -an | findstr \"0.0.0.0:161\"";
  exec($cmd,$output,$result);
  if(empty($output)){
    $snmpportsw=1;
  }
}else{
  /// linux
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
    if($found==0){
      $snmpportsw=1;
    }
  }else{
    $snmpportsw=1;
    echo "snmpcheck: netstatコマンドに失敗しました".PHP_EOL;
  }
}
if($snmpportsw==1){
  echo 'snmpcheck: snmp udp ポート161 が見つかりません'.PHP_EOL;
}else{
  echo 'snmpcheck: udp ポート161 が見つかりました、ファイアウオールは不明です'.PHP_EOL;
} 



///
///snmp機能テスト
///

$output=null;
$result=null;
$snmpfuncsw=0;
if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
  /// windows
  $cmd=$vpath_ubin.'/snmpcpuget.exe localhost windows '.$community;
}else{
  /// linux
  $cmd=$vpath_ubin.'/snmpcpuget.sh localhost linux '.$community;
}
exec($cmd,$output,$result);
if($result==0){
  echo 'snmpcheck: snmp機能は動作しています'.PHP_EOL;
}else{
  $snmpfumcsw=1;
  echo 'snmpcheck: snmp機能が確認できません、snmpgetコマンドで確認してください'.PHP_EOL;
}

///
///localhostでsnmp locationアクセス
///
$output=null;
$result=null;
$snmpagentsw=0;
$cmd="snmpset -v1 -c".$community." localhost .1.3.6.1.2.1.1.6.0 s ok";
exec($cmd,$output1,$result1);
$output2=null;
$result2=null;
$cmd2="snmpget -v1 -c".$community." localhost .1.3.6.1.2.1.1.6.0";
exec($cmd2,$output2,$result2);
if($result==0 and $result2==0){
  $outArr=explode(':',$output2[0]);
  if(trim($outArr[3])!='ok'){
    $snmpagentsw=1;    
  }
}else{
  $snmpagentsw=1;
}
if($snmpagentsw==1){
  echo "snmpcheck: エージェント機能確認に失敗しました、snmpset/snmpgetコマンドで、sysLocationをチェックして下さい".PHP_EOL;
}else{
  echo 'snmpcheck: エージェント機能は動作しています'.PHP_EOL;
}
?>
