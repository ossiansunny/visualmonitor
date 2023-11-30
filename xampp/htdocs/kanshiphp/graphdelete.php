<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
require_once "mysqlkanshi.php";
require_once "varread.php";

echo '<html><head>';
echo '<title>リソースグラフ削除</title>';
echo '<link rel="stylesheet" href="kanshi1_py.css">';
echo '</head><body>';

$pgm = "graphdelete.php";

function branch($_page,$_param){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="'.$_page.'" method="get">';
  echo '<input type=hidden name=param value="'.$_param.'">';
  echo '<input type="submit" name="next" value="Waiting...">';
  echo '</form>';
}
$vpath_mrtghome="";
$vpath_kanshiphp="";
$vpath_mrtgbase="";
if(!isset($_GET['param'])){
   echo '<a href="GraphListPage.php">クリックして、ホストを選択して下さい</a>';
   exit;
}
$param=$_GET['param'];
$parr=explode("#",$param);
$cde=$parr[1]; // param -> class="error"
$uid=$parr[2]; // user
$parm=explode(",",$parr[3]);

$vpatharr=array("vpath_mrtgbase","vpath_kanshiphp","vpath_mrtghome");
$rtnv=pathget($vpatharr);
if(count($rtnv)==3){
  $vpath_mrtgbase=$rtnv[0];
  $vpath_kanshiphp=$rtnv[1];
  $vpath_mrtghome=$rtnv[2];
}else{
  writeloge($pgm,"variable vpath_mrtgbase,vpath_kanshiphp,vpath_mrtghome could not get path");
  $nextpage='MonitorManager.php';
  branch($nextpage,$uid);
  exit;
}

$host = $parm[0];

//------------------------------------

$target = $vpath_kanshiphp."\\mrtgcfg\\" . $host . ".*.cfg";
$result = glob($target);
foreach($result as $tfile){
  unlink($tfile);
}

// newmrtg.cfg再構成
$it = new AppendIterator();
foreach(glob($vpath_kanshiphp."\\mrtgcfg\\*.cfg") as $filename){
  $it->append(new SplFileObject($filename, "r"));
}
$fpath = $vpath_mrtgbase."\\newmrtg.cfg";
unlink($fpath);
$file = new SplFileObject($vpath_mrtgbase."\\newmrtg.cfg", "w");
foreach($it as $line) {
  if(! is_null($line)){
    $file->fwrite($line);
  } 
}
$target = $vpath_mrtghome."\\mrtgimage\\" . $host ."*.*";
$result = glob($target);
foreach($result as $tfile){
  unlink($tfile);
}

$nextpage='MonitorManager.php';
branch($nextpage,$uid);
exit;
echo '</body></html>';
?>
