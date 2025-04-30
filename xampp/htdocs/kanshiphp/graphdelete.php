<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";

$pgm = "graphdelete.php";

$vpath_mrtghome="";
$vpath_kanshiphp="";
$user="";
///
///
$user=$_GET['user'];
if(!isset($_GET['fradio'])){
  $msg = "#error#".$user."#ホストを選択して下さい";
  $nextpage = "GraphListPage.php";
  branch($nextpage,$msg);  
}
$hostArr=explode(',',$_GET['fradio']);
$host = $hostArr[0];
$vpathParam=array("vpath_kanshiphp","vpath_mrtghome");
$rtnPath=pathget($vpathParam);
if(count($rtnPath)==2){
  $vpath_kanshiphp=$rtnPath[0];
  $vpath_mrtghome=$rtnPath[1];
}else{
  writeloge($pgm,"vpath_kanshiphp,vpath_mrtghome変数のパスが取得できません");
  $nextpage='MonitorManager.php';
  $msg = "#error#".$user."#vpath_kanshiphp変数のパスが取得出来ません";
  $nextpage = "GraphListPage.php";
  branch($nextpage,$msg);
}

///------------------------------------

$cfgPath = $vpath_kanshiphp."/mrtgcfg/".$host.".*.cfg";
$cfgHosts = glob($cfgPath);
foreach($cfgHosts as $cfgFile){
  unlink($cfgFile);
}

/// newmrtg.cfg再構成
$iterator = new AppendIterator();
foreach(glob($vpath_kanshiphp."/mrtgcfg/*.cfg") as $fileName){
  $iterator->append(new SplFileObject($fileName, "r"));
}
$cfgPath = $vpath_home."/newmrtg.cfg";
unlink($cfgPath);
$newMrtg = new SplFileObject($vpath_mrtghome."/newmrtg.cfg", "w");
foreach($iterator as $lineRec) {
  if(! is_null($lineRec)){
    $newMrtg->fwrite($lineRec);
  } 
}
$imgPath = $vpath_mrtghome."/mrtgimage/".$host."*.*";
$imgHosts = glob($imgPath);
foreach($imgHosts as $imgFile){
  unlink($imgFile);
}

$msg = "#error#".$user."#ホスト".$host."のグラフ作成登録を削除しました";
$nextpage = "GraphListPage.php";
branch($nextpage,$msg);

?>

