<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";
///
$pgm = "graphcreate.php";
$vpath_mrtgbase="";
$vpath_kanshiphp="";
$vpath_base="";
$user="";
$osDirSep='';

if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
  $osDirSep='\\';
}else{
  $osDirSep='/';
}
///

function cfgtemplate($_ip,$_comm,$_gtype,$_os,$_title){
    global $pgm, $vpath_base, $vpath_kanshiphp, $osDirSep;
    $fname=$vpath_kanshiphp.$osDirSep.'mrtgcfg'.$osDirSep.$_ip.'.'.$_gtype.'.cfg';  //\\が必要
    //echo $fname."<br>";
    if($osDirSep=='\\'){
      $snmpget=$vpath_base.$osDirSep.'ubin'.$osDirSep.'snmp'.$_gtype.'get.exe '.$_ip.' '.$_os.' '.$_comm;
    }else{
      $snmpget=$vpath_base.$osDirSep.'bin'.$osDirSep.'snmp'.$_gtype.'get.exe '.$_ip.' '.$_os.' '.$_comm;
    }
    writelogd($pgm,$snmpget);
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
      //echo $data[$cs]."<br>";      
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
//var_dump($hostArr);
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
$vpathParam=array("vpath_mrtgbase","vpath_kanshiphp","vpath_base");
$rtnPath=pathget($vpathParam);
if(count($rtnPath)==3){
  $vpath_mrtgbase=$rtnPath[0];
  $vpath_kanshiphp=$rtnPath[1];
  $vpath_base=$rtnPath[2];
  
}else{
  writeloge($pgm,"variable vpath_mrtgbase,vpath_kanshiphp,vpath_base could not get path");
  $msg = "#error#".$user."#vpath_mrtgbase,vpath_kanshiphp,vpath_base変数が取得出来ません";
  $nextpage = "GraphListPage.php";
  branch($nextpage,$msg);
  
}
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
foreach(glob($vpath_kanshiphp.$osDirSep.'mrtgcfg'.$osDirSep.'*.cfg') as $filename){
  //echo $filename.'<br>';
  $it->append(new SplFileObject($filename, "r"));
}
//echo $vpath_mrtgbase.$osDirSep.'newmrtg.cfg<br>';
unlink($vpath_mrtgbase.$osDirSep.'newmrtg.cfg');
$file = new SplFileObject($vpath_mrtgbase.$osDirSep.'newmrtg.cfg', 'w');
foreach($it as $line) {
  //echo $line.'<br>';
  if(! is_null($line)) {
    $file->fwrite($line);
  }
}
$msg = "#notic#".$user."#ホスト".$host."のグラフ作成登録を完了しました";
$nextpage = "GraphListPage.php";
branch($nextpage,$msg);
/*
print '</body></html>';
*/
?>
