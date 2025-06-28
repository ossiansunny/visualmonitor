<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";

$pgm = "graphdelete.php";

$vpath_mrtghome="";
$vpath_kanshiphp="";
$brcode="";
$brmsg="";
$user="";
///
///
if(isset($_GET['param'])){
  paramSet();
}
$hostArr=explode(',',$brmsg);
$host = $hostArr[0];
$vpathParam=array("vpath_kanshiphp","vpath_mrtghome");
$rtnPath=pathget($vpathParam);
if(count($rtnPath)==2){
  $vpath_kanshiphp=$rtnPath[0];
  $vpath_mrtghome=$rtnPath[1];
}else{
  writeloge($pgm,"vpath_kanshiphp,vpath_mrtghome変数のパスが取得できません");
  $msg = "#error#".$user."#vpath_kanshiphp変数のパスが取得出来ません";
  $nextpage = "GraphListPage.php";
  branch($nextpage,$msg);
}

///------------------------------------
$newmrtgFile=$vpath_mrtghome."/newmrtg.cfg";
$wtFile=fopen($newmrtgFile,"w");
if(flock($wtFile,LOCK_EX)){
  $cfgPath = $vpath_kanshiphp."/mrtgcfg/".$host.".*.cfg";
  $cfgHosts = glob($cfgPath);
  foreach($cfgHosts as $cfgFile){
    
    unlink($cfgFile);
  }
///------------------------------------
  $mrtgCfgFile=$vpath_kanshiphp."/mrtgcfg/*";
  
  foreach (glob($mrtgCfgFile) as $fileName) {
    $rdFile=fopen($fileName,'r');
    while (($line=fgets($rdFile)) !== false) {
      
      fwrite($wtFile,$line);
    }
    fclose($rdFile);
    
  }
  fclose($wtFile);
  
}else{
  writeloge($pgm,"newmrtg.cfgのロックに失敗しました");
  $msg = "#error#".$user."#newmrtg.cfgのロックに失敗しました";
  $nextpage = "GraphListPage.php";
  branch($nextpage,$msg);
}
$msg = "#error#".$user."#ホスト".$host."のグラフ作成登録を削除しました";
$nextpage = "GraphListPage.php";
branch($nextpage,$msg);

?>

