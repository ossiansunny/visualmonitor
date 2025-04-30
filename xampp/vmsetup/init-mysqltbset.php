<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
$path_vmsetup=__DIR__;
$path_varread=$path_vmsetup."/init-varread.php";
$path_mysqlkanshi="";
require_once $path_varread;
$pgm = "init_mysqltbset.php";
$vpath_base="";
$vpatharr=array("vpath_base","vpath_kanshiphp");
$rtnv=pathget($vpatharr);
if(count($rtnv)==2){
  $vpath_base=$rtnv[0];
  $vpath_kanshiphp=$rtnv[1];
  $vpath_mysqlkanshi=$vpath_kanshiphp."/mysqlkanshitmp.php";
  require_once $vpath_mysqlkanshi;
  $cfgpath=$vpath_base."/vmsetup/mysqltbset.cfg";
  $cfp=fopen($cfgpath,'r');
  $cfgarray=array();
  $tbname="";
  if($cfp){
    while ($cline = fgets($cfp)) {
      if (strpos($cline, '[') !== false){
        $clinex=ltrim($cline,'[');
        $cliney=rtrim($clinex);
        $tbname=rtrim($cliney,"]");
        continue;        
      }else{        
        $delsql="drop table if exists ".$tbname;
        $rtntb=create($delsql);
        if ($rtntb==-1){
          echo "table could not created ... check mysqltbset.cfg".PHP_EOL;
          echo "Suspend further processing".PHP_EOL;
          break;
        }
        $crtsql=rtrim($cline);
        $rtntb=create($crtsql);
        if ($rtntb==0){
          echo "<<< ".$tbname." Successfully created".PHP_EOL;
        }else{
          echo ">>>>>>>>>>>>>>> ".$tbname." Creation failed".PHP_EOL;
        }
      } 
    }
  }
  fclose($cfp);
}else{
  echo "パスが得られません、kanshiphp.iniを見直して下さい";
}

?>
