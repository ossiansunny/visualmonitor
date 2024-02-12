<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
$path_vmsetup=__DIR__;
$path_kanshiphp=str_replace('/vmsetup','',$path_vmsetup);
$path_kanshiphpini=$path_vmsetup."/kanshiphp.ini";
$path_varread=$path_kanshiphp."/varread.php";
$path_mysqlkanshi=$path_kanshiphp."/mysqlkanshitmp.php";
require_once $path_varread;
require_once $path_mysqlkanshi;
$pgm = "init_mysqltbset.php";
$vpath_kanshiphp="";
$vpatharr=array("vpath_kanshiphp");
$rtnv=pathget($vpatharr);
if(count($rtnv)==1){
  $vpath_kanshiphp=$rtnv[0];
  $cfgpath=$vpath_kanshiphp."/vmsetup/mysqltbset.cfg";
  $cfp=fopen($cfgpath,'r');
  $cfgarray=array();
  $tbname="";
  if($cfp){
    while ($cline = fgets($cfp)) {
      if (strpos($cline, '[') !== false){
        $clinex=ltrim($cline,'[');
        //echo $clinex.PHP_EOL;
        $cliney=rtrim($clinex);
        $tbname=rtrim($cliney,"]");
        //echo $tbname.PHP_EOL;
        continue;        
      }else{        
        $delsql="drop table if exists ".$tbname;
        //echo $delsql.PHP_EOL;
        $rtntb=create($delsql);
        if ($rtntb==-1){
          echo "table could not created ... check mysqltbset.cfg".PHP_EOL;
          echo "Suspend further processing".PHP_EOL;
          break;
        }
        $crtsql=rtrim($cline);
        $rtntb=create($crtsql);
        if ($rtntb==0){
          echo $tbname." Successfully created".PHP_EOL;
        }else{
          echo $tbname." Creation failed".PHP_EOL;
        }
      } 
    }
  }
  fclose($cfp);
}else{
  echo "パスが得られません、kanshiphp.iniを見直して下さい";
}

?>
