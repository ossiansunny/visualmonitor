<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
require_once "mysqlkanshi.php";
require_once "varread.php";

echo '<html><head>';
echo '<link rel="stylesheet" href="kanshi1.css">';
echo '<title>リソースグラフ作成・更新</title>';
echo '</head><body>';

$pgm = "graphcreate.php";

$vpath_mrtgbase="";
$vpath_kanshiphp="";

function branch($_page,$_param){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="'.$_page.'" method="get">';
  echo '<input type=hidden name=param value="'.$_param.'">';
  echo '<input type="submit" name="next" value="Waiting...">';
  echo '</form>';
}
function cfgtemplate($ip,$comm,$gtype,$os,$ttl){
    global $vpath_mrtgbase;
    $fname='mrtgcfg\\'.$ip.'.'.$gtype.'.cfg';  //\\が必要
    $snmpget=$vpath_mrtgbase.'\\ubin\\snmp'.$gtype.'get.exe '.$ip.' '.$os.' '.$comm;
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
    fwrite($fp,$border."\r\n");
    for($cs=0;$cs<$cc;$cs++){
      fwrite($fp,$data[$cs]."\r\n");
    }    
    fclose($fp);
}
$parm=array();
if(!isset($_GET['param'])){
  echo '<a href="GraphListPage.php">クリックして、ホストを選択して下さい</a>';
  exit;
}
$param=$_GET['param'];
$parr=explode("#",$param);
$cde=$parr[1]; // param -> class="error"
$uid=$parr[2]; // user
$parm=explode(",",$parr[3]);
$host = $parm[0];
if (isset($parm[13])){
  $community = $parm[13];
}
if ($parm[2] == '0'){
  $ostype = 'windows';
} else {
  $ostype = 'unix';
} 
$vpatharr=array("vpath_mrtgbase","vpath_kanshiphp");
$rtnv=pathget($vpatharr);
if(count($rtnv)==2){
  $vpath_mrtgbase=$rtnv[0];
  $vpath_kanshiphp=$rtnv[1];
}else{
  writeloge($pgm,"variable vpath_mmrtgbase,vpath_kanshiphp could not get path");
  $nextpage='MonitorManager.php';
  branch($nextpage,$uid);
  exit;
}
if (isset($parm[8])){
  cfgtemplate($host, $community, 'cpu', $ostype, 'CPU Load');
}
if (isset($parm[9])){
  cfgtemplate($host, $community, 'ram', $ostype, 'Memory Usage');
}
if (isset($parm[10])){
  cfgtemplate($host, $community, 'disk', $ostype, 'Disk Usage');
}
/// 合体
$it = new AppendIterator();
foreach(glob($vpath_kanshiphp."\\mrtgcfg\\*.cfg") as $filename){
  $it->append(new SplFileObject($filename, "r"));
}
unlink($vpath_mrtgbase."\\newmrtg.cfg");
$file = new SplFileObject($vpath_mrtgbase."\\newmrtg.cfg", "w");
foreach($it as $line) {
  if(! is_null($line)) {
    $file->fwrite($line);
  }
}
$nextpage='MonitorManager.php';
branch($nextpage,$uid);
exit;
echo '</body></html>';
?>
