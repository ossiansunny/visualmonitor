<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
$path_vmsetup=__DIR__;
$path_kanshiphp=str_replace('\vmsetup','',$path_vmsetup);
$path_kanshiphpini=$path_vmsetup."\kanshiphp.ini";
$varread=$path_kanshiphp."\\varread.php";
require_once $varread;
$pgm = "init_workdir.php";
$vpatharr=array("vpath_kanshiphp","vpath_mrtghome","vpath_mrtgbase");
$rtnv=pathget($vpatharr);
if(count($rtnv)==3){
  $vpath_kanshiphp=$rtnv[0];
  $vpath_mrtghome=$rtnv[1];
  $vpath_mrtgbase=$rtnv[2];
  $cfgfile=$vpath_kanshiphp."\\mrtgcfg\\0.0.0.1.cfg";
  $mrtgfile=$vpath_mrtgbase."\\newmrtg.cfg";
  unlink($cfgfile);
  $fp = fopen($cfgfile,'w');
  fwrite($fp,"EnableIPv6: no\r\n");
  $wpath='WorkDir: '.$vpath_mrtghome;
  fwrite($fp,$wpath."\r\n");
  fclose($fp);
  copy($cfgfile,$mrtgfile);
  echo $cfgfile." sucessfully created".PHP_EOL;
  echo $mrtgfile." successfully created".PHP_EOL;
}else{
  echo "パスが得られません、kanshiphp.iniを見直して下さい";
}
?>
