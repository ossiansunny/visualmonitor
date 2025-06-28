<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
$path_vmsetup=__DIR__;
$path_kanshiphp=str_replace('/vmsetup','',$path_vmsetup);
$path_kanshiphpini=$path_vmsetup."/kanshiphp.ini";
$varread=$path_kanshiphp."/varread.php";
require_once $varread;
$pgm = "init_filedel.php";
$vpath_kanshiphp="";
$vpatharr=array("vpath_kanshiphp","vpath_mrtghome","vpath_plothome");
$rtnv=pathget($vpatharr);
if(count($rtnv)==3){
  $vpath_kanshiphp=$rtnv[0];
  $vpath_mrtghome=$rtnv[1];
  $vpath_plothome=$rtnv[2];
  /// kanshiphp\mrtgcfg\<host>.{cpu,disk,ram}.cfg
  $cfgdir=$vpath_kanshiphp."/mrtgcfg/*.*";
  $result = glob($cfgdir);
  foreach($result as $tfile){
    if (str_contains($tfile, '.cpu.cfg') || str_contains($tfile,'.disk.cfg') ||  str_contains($tfile,'.ram.cfg')) {
      echo $tfile . "deleted\r\n";
      unlink($tfile);
    }
  }
  echo "init_filedel.php ".$vpath_kanshiphp."\mrtgcfg内のファイルをリセットしました\r\n";
//// htdocs\mrtg/mrtgimage/<host>.{cpu,disk,ram}.cfg
  $mrtgdir=$vpath_mrtghome."/mrtgimage/*.*";
  $result = glob($mrtgdir);
  foreach($result as $tfile){
    if (str_contains($tfile, '.cpu') || str_contains($tfile,'.disk') ||  str_contains($tfile,'.ram')) {
      echo $tfile . "deleted\r\n";
      unlink($tfile);
    }
  }
  echo " init_filedel.php ".$vpath_mrtghome."/mrtgimage内のファイルをリセットしました\r\n";
///// htdocs\plot\plotimage/<host>.{log,ad,ok,exe,log.backup}
  $plotdir=$vpath_plothome."/plotimage/*.*";
  $result = glob($plotdir);
  foreach($result as $tfile){
    echo $tfile . "deleted\r\n";
    unlink($tfile);
  }
  echo "init_filedel.php ".$vpath_plothome."/plotimage内のファイルをリセットしました\r\n";

}else{
  echo "パスが得られません、kanshiphp.iniを見直して下さい";
}
?>
