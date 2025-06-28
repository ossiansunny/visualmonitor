<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
$path_vmsetup=__DIR__;
$path_htdocs=str_replace('/vmsetup','',$path_vmsetup);
$path_kanshiphpini=$path_vmsetup."/kanshiphp.ini";
$varread=$path_vmsetup."/init-varread.php";
require_once $varread;
$pgm = "init_workdir.php";
$vpatharr=array("vpath_kanshiphp","vpath_mrtghome","vpath_plothome","vpath_mrtgbase");
$rtnv=pathget($vpatharr);
if(count($rtnv)==4){
  $vpath_kanshiphp=$rtnv[0];
  $vpath_mrtghome=$rtnv[1];
  $vpath_plothome=$rtnv[2];
  $vpath_mrtgbase=$rtnv[3];
  /// ----------------------------------------------
  /// create mrtgcfg and 0.0.0.0.cfg
  $kpath=$vpath_kanshiphp."/mrtgcfg";
  If (file_exists($kpath)){
    $fileName=$kpath."/*";
    foreach(glob($fileName) as $val) {
      //echo $val.PHP_EOL;
      unlink($val);
    }
  }else{
    mkdir($kpath);
  }
  $mrtgcfg=$vpath_kanshiphp."/mrtgcfg/0.0.0.0.cfg";
  $wfp = fopen($mrtgcfg,'w');
  fwrite($wfp,"EnableIPv6: no\r\n");
  $wpath='WorkDir: '.$vpath_mrtghome;
  fwrite($wfp,$wpath."\r\n");
  fclose($wfp);
  echo $mrtgcfg." sucessfully created".PHP_EOL;
  /// ---------------------------------------------
  /// create newmrtg.cfg 
  $mrtgpath=$vpath_mrtgbase;
  If (file_exists($mrtgpath)){
    $fileName=$mrtgpath."/*";
    foreach(glob($fileName) as $val) {
      echo 'Deleted '.$val.PHP_EOL;
      unlink($val);
    }
  }else{
    mkdir($mrtgpath);
  }
  touch($mrtgpath."/newmrtg.cfg");
  echo "<<< Create newmrtg.cfg".PHP_EOL;
  /// ----------------------------------------------
  /// write contents on newmrtg.cfg
  $newmrtgcfg=$vpath_mrtgbase."/newmrtg.cfg";
  $wfp = fopen($newmrtgcfg,'w');
  fwrite($wfp,"EnableIPv6: no\r\n");
  $wpath='WorkDir: '.$vpath_mrtghome;
  fwrite($wfp,$wpath."\r\n");
  fclose($wfp);
  echo "<<< ".$newmrtgcfg." successfully created".PHP_EOL;
  /// --------------------------------------------- 
  /// create mrtgimage
  $kpath=$vpath_mrtghome."/mrtgimage";
  If (file_exists($kpath)){
    $fileName=$kpath."/*";
    foreach(glob($fileName) as $val) {
      //echo $val.PHP_EOL;
      unlink($val);
    }
  }else{
    mkdir($kpath);
  }
  echo "<<< ".$kpath." sucessfully created".PHP_EOL;
  /// ---------------------------------------------
  /// create plotimage
  $kpath=$vpath_plothome."/plotimage";
  If (file_exists($kpath)){
    $fileName=$kpath."/*";
    foreach(glob($fileName) as $val) {
      //echo $val.PHP_EOL;
      unlink($val);
    }
  }else{
    mkdir($kpath);
  }
  echo "<<< ".$kpath." sucessfully created".PHP_EOL;

}else{
  echo ">>>>>>>>>>>>>>> パスが得られません、kanshiphp.iniを見直して下さい";
}
?>
