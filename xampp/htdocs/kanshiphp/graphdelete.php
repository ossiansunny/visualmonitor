<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";

print '<html><head>';
print '<title>リソースグラフ削除</title>';
print '<link rel="stylesheet" href="kanshi1_py.css">';
print '</head><body>';

$pgm = "graphdelete.php";

$vpath_mrtghome="";
$vpath_kanshiphp="";
$vpath_mrtgbase="";
$user="";
$user=$_GET['user'];
if(!isset($_GET['fradio'])){
  $msg = "#error#".$user."#ホストを選択して下さい";
  $nextpage = "GraphListPage.php";
  branch($nextpage,$msg);
  
}
$parm=explode(",",$parr[3]);
$fradio=explode(',',$_GET['fradio']);
$host = $fradio[0];
$vpatharr=array("vpath_mrtgbase","vpath_kanshiphp","vpath_mrtghome");
$rtnv=pathget($vpatharr);
if(count($rtnv)==3){
  $vpath_mrtgbase=$rtnv[0];
  $vpath_kanshiphp=$rtnv[1];
  $vpath_mrtghome=$rtnv[2];
}else{
  writeloge($pgm,"variable vpath_mrtgbase,vpath_kanshiphp,vpath_mrtghome could not get path");
  $nextpage='MonitorManager.php';
  $msg = "#error#".$user."#vpath_mmrtgbase,vpath_kanshiphp変数が取得出来ません";
  $nextpage = "GraphListPage.php";
  branch($nextpage,$msg);
}

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

$msg = "#error#".$user."#ホスト".$host."のグラフ作成登録を削除しました";
$nextpage = "GraphListPage.php";
branch($nextpage,$msg);
print '</body></html>';
?>

