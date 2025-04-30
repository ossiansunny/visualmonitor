<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";
///
$pgm = "graphcreate.php";
$vpath_mrtghome="";
$vpath_kanshiphp="";
$vpath_ubin="";
$user="";
$getExt='';

if (strtoupper(substr(PHP_OS,0,3))==='WIN') {  
  $getExt='get.exe';
}else{  
  $getExt='get.sh';
}
///

function cfgtemplate($_ip,$_comm,$_gtype,$_os,$_title){
    global $pgm, $vpath_ubin, $vpath_kanshiphp, $getExt;
    $fname=$vpath_kanshiphp.'/mrtgcfg/'.$_ip.'.'.$_gtype.'.cfg';  ///windowsは\\,Unixは/がセパレータ
    $snmpget=$vpath_ubin.'/snmp'.$_gtype.$getExt.' '.$_ip.' '.$_os.' '.$_comm;
    $fp = fopen($fname,'w');
    $data = array();
    $data[0]='Target['.$_ip.'.'.$_gtype.']: `'.$snmpget.'`';
    $data[1]='MaxBytes['.$_ip.'.'.$_gtype.']: 100';
    $data[2]='Directory['.$_ip.'.'.$_gtype.']: mrtgimage';
    $data[3]='Options['.$_ip.'.'.$_gtype.']: gauge,growright';
    $data[4]='YTicsFactor['.$_ip.'.'.$_gtype.']: 1';
    $data[5]='Factor['.$_ip.'.'.$_gtype.']: 1';
    $data[6]='Title['.$_ip.'.'.$_gtype.']: '.$_title; 
    $data[7]='YLegend['.$_ip.'.'.$_gtype.']: '.$_title;
    $cc=count($data);
    $border='#------------------------------------------------------#';
    fwrite($fp,$border."\r\n");
    for($cs=0;$cs<$cc;$cs++){
      fwrite($fp,$data[$cs]."\r\n");      
    }    
    fclose($fp);
}
///
$user=$_GET['user'];
if(!isset($_GET['fradio'])){
  $msg = "#error#".$user."#ホストを選択して下さい";
  $nextpage = "GraphListPage.php";
  branch($nextpage,$msg);  
}
///
$hostArr = explode(',',$_GET['fradio']);
$host=$hostArr[0];
$osType=$hostArr[2];
if (isset($hostArr[13])){
  $community = $hostArr[13];
}else{
  $community = 'public';
}
if ($osType == '0'){
  $osType = 'windows';
} else {
  $osType = 'unix';
} 
$vpathParam=array("vpath_mrtghome","vpath_kanshiphp","vpath_ubin");
$rtnPath=pathget($vpathParam);
if(count($rtnPath)==3){
  $vpath_mrtghome=$rtnPath[0];
  $vpath_kanshiphp=$rtnPath[1];
  $vpath_ubin=$rtnPath[2];
}else{
  writeloge($pgm,"vpath_mrtghome,vpath_kanshiphp,vpath_ubin変数のパスが取得できません");
  $msg = "#error#".$user."#vpath_mrtghome,vpath_kanshiphp,vpath_ubin変数のパスが取得出来ません";
  $nextpage = "GraphListPage.php";
  branch($nextpage,$msg);
}
///
$zerofile=$vpath_kanshiphp.'/mrtgcfg/0.0.0.0.cfg'; 
if(!file_exists($zerofile)){
  $msg = "#error#".$user."#mrtgcfg/0.0.0.0.cfgがありません";
  $nextpage = "GraphListPage.php";
  branch($nextpage,$msg);  
}
///
if (isset($hostArr[8])){
  cfgtemplate($host, $community, 'cpu', $osType, 'CPU Load');
}
if (isset($hostArr[9])){
  cfgtemplate($host, $community, 'ram', $osType, 'Memory Usage');
}
if (isset($hostArr[10])){
  cfgtemplate($host, $community, 'disk', $osType, 'Disk Usage');
}
/// mrtgcfgからnewmrtg.cfgを作る
$it = new AppendIterator();
foreach(glob($vpath_kanshiphp.'/mrtgcfg/*.cfg') as $filename){
  //echo $filename.'<br>';
  $it->append(new SplFileObject($filename, "r"));
}
unlink($vpath_mrtghome.'/newmrtg.cfg');
$file = new SplFileObject($vpath_mrtghome.'/newmrtg.cfg', 'w');
foreach($it as $line) {
  if(! is_null($line)) {
    $file->fwrite($line);
  }
}
$msg = "#notic#".$user."#ホスト".$host."のグラフ作成登録を完了しました";
$nextpage = "GraphListPage.php";
branch($nextpage,$msg);
?>
