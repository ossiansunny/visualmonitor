<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";
/*
print '<html><head>';
print '<title>リソースグラフ削除</title>';
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
print '</head><body>';
*/
$pgm = "graphdelete.php";

$vpath_mrtghome="";
$vpath_kanshiphp="";
$vpath_mrtgbase="";
$user="";
$osDirSep='';
///
if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
  $osDirSep='\\';
}else{
  $osDirSep='/';
}
///
$user=$_GET['user'];
if(!isset($_GET['fradio'])){
  $msg = "#error#".$user."#ホストを選択して下さい";
  $nextpage = "GraphListPage.php";
  branch($nextpage,$msg);  
}
$hostArr=explode(',',$_GET['fradio']);
$host = $hostArr[0];
$vpathParam=array("vpath_mrtgbase","vpath_kanshiphp","vpath_mrtghome");
$rtnPath=pathget($vpathParam);
if(count($rtnPath)==3){
  $vpath_mrtgbase=$rtnPath[0];
  $vpath_kanshiphp=$rtnPath[1];
  $vpath_mrtghome=$rtnPath[2];
}else{
  writeloge($pgm,"vpath_mrtgbase,vpath_kanshiphp,vpath_mrtghome変数のパスが取得できません");
  $nextpage='MonitorManager.php';
  $msg = "#error#".$user."#vpath_mmrtgbase,vpath_kanshiphp変数のパスが取得出来ません";
  $nextpage = "GraphListPage.php";
  branch($nextpage,$msg);
}

///------------------------------------

$cfgPath = $vpath_kanshiphp.$osDirSep."mrtgcfg".$osDirSep.$host.".*.cfg";
$cfgHosts = glob($cfgPath);
foreach($cfgHosts as $cfgFile){
  unlink($cfgFile);
}

/// newmrtg.cfg再構成
$iterator = new AppendIterator();
foreach(glob($vpath_kanshiphp.$osDirSep."mrtgcfg".$osDirSep."*.cfg") as $fileName){
  $iterator->append(new SplFileObject($fileName, "r"));
}
$cfgPath = $vpath_mrtgbase.$osDirSep."newmrtg.cfg";
unlink($cfgPath);
$newMrtg = new SplFileObject($vpath_mrtgbase.$osDirSep."newmrtg.cfg", "w");
foreach($iterator as $lineRec) {
  if(! is_null($lineRec)){
    $newMrtg->fwrite($lineRec);
  } 
}
$imgPath = $vpath_mrtghome.$osDirSep."mrtgimage".$osDirSep.$host."*.*";
$imgHosts = glob($imgPath);
foreach($imgHosts as $imgFile){
  unlink($imgFile);
}

$msg = "#error#".$user."#ホスト".$host."のグラフ作成登録を削除しました";
$nextpage = "GraphListPage.php";
branch($nextpage,$msg);
?>

