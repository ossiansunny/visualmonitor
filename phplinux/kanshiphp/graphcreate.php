<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";

print '<html><head>';
print '<link rel="stylesheet" href="kanshi1.css">';
print '<title>リソースグラフ作成・更新</title>';
print '</head><body>';

$pgm = "graphcreate.php";
$vpath_mrtghome="";
$vpath_kanshiphp="";
$vpath_kanshibin="";
$user="";

function cfgtemplate($ip,$comm,$gtype,$os,$ttl){
    global $pgm, $vpath_mrtghome, $vpath_kanshiphp, $vpath_kanshibin;
    $fname=$vpath_kanshiphp.'/mrtgcfg/'.$ip.'.'.$gtype.'.cfg';  //\\が必要
    writelogd($pgm,$fname);
    $snmpget=$vpath_kanshibin.'/snmp'.$gtype.'get '.$ip.' '.$os.' '.$comm;
    $fp = fopen($fname,'w');
    $data = array();
    $data[0]='Target['.$ip.'.'.$gtype.']: `'.$snmpget.'`';
    $data[1]='MaxBytes['.$ip.'.'.$gtype.']: 100';
    $data[2]='Directory['.$ip.'.'.$gtype.']: mrtgimage';
    $data[3]='Options['.$ip.'.'.$gtype.']: gauge,growright';
    $data[4]='YTicsFactor['.$ip.'.'.$gtype.']: 1';
    $data[5]='Factor['.$ip.'.'.$gtype.']: 1';
    $data[6]='Title['.$ip.'.'.$gtype.']: '.$ttl; 
    $data[7]='YLegend['.$ip.'.'.$gtype.']: '.$ttl;
    $cc=count($data);
    $border='#------------------------------------------------------#';
    fwrite($fp,$border.PHP_EOL);
    for($cs=0;$cs<$cc;$cs++){
      fwrite($fp,$data[$cs].PHP_EOL);
      
    }    
    fclose($fp);
}
$user=$_GET['user'];
if(!isset($_GET['fradio'])){
  $msg = "#error#".$user."#ホストを選択して下さい";
  $nextpage = "GraphListPage.php";
  branch($nextpage,$msg);
  
}
$fradio = explode(',',$_GET['fradio']);
$host=$fradio[0];
$ostype=$fradio[2];
if (isset($fradio[13])){
  $community = $fradio[13];
}else{
  $community = 'public';
}
if ($ostype == '0'){
  $ostype = 'windows';
} else {
  $ostype = 'unix';
} 
$vpatharr=array("vpath_mrtghome","vpath_kanshiphp","vpath_kanshibin");
$rtnv=pathget($vpatharr);
if(count($rtnv)==3){
  $vpath_mrtghome=$rtnv[0];
  $vpath_kanshiphp=$rtnv[1];
  $vpath_kanshibin=$rtnv[2];
  $getpath=$vpath_mrtghome.' '.$vpath_kanshiphp;
  //writeloge($pgm,$getpath);
}else{
  writeloge($pgm,"variable vpath_mrtghome,vpath_kanshiphp could not get path");
  $msg = "#error#".$user."#vpath_mrtghome,vpath_kanshiphp変数が取得出来ません";
  $nextpage = "GraphListPage.php";
  branch($nextpage,$msg);
  
}
if (isset($fradio[8])){
  cfgtemplate($host, $community, 'cpu', $ostype, 'CPU Load');
}
if (isset($fradio[9])){
  cfgtemplate($host, $community, 'ram', $ostype, 'Memory Usage');
}
if (isset($fradio[10])){
  cfgtemplate($host, $community, 'disk', $ostype, 'Disk Usage');
}
/// 合体
$it = new AppendIterator();
foreach(glob($vpath_kanshiphp."/mrtgcfg/*.cfg") as $filename){
  $it->append(new SplFileObject($filename, "r"));
}
$mrtgcfgfile=$vpath_mrtghome."/newmrtg.cfg";
unlink($mrtgcfgfile);
$file = new SplFileObject($mrtgcfgfile, "w");
foreach($it as $line) {
  if(! is_null($line)) {
    $file->fwrite($line);
  }
}
$msg = "#notic#".$user."#ホスト".$host."のグラフ作成登録を完了しました";
$nextpage = "GraphListPage.php";
branch($nextpage,$msg);

print '</body></html>';
?>
