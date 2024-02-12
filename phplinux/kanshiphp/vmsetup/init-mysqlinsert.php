<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
$path_vmsetup=__DIR__;
$path_kanshiphp=str_replace('/vmsetup','',$path_vmsetup);
$path_kanshiphpini=$path_vmsetup."/kanshiphp.ini";
$path_varread=$path_kanshiphp."/varread.php";
$path_mysqlkanshi=$path_kanshiphp."/mysqlkanshitmp.php";
require_once $path_varread;
require_once $path_mysqlkanshi;
$pgm = "init_mysqlinsert.php";
$vpath_kanshiphp="";
$vpatharr=array("vpath_kanshiphp");
$rtnv=pathget($vpatharr);
if(count($rtnv)==1){
  $vpath_kanshiphp=$rtnv[0];
  $cfgpath=$vpath_kanshiphp."/vmsetup/mysqlvalins.cfg";
  $cfp=fopen($cfgpath,'r');
  $valarray=array();
  $oldtbname="";
  $newtbname="";
  $fsw=0;
  if($cfp){
    while ($cline = fgets($cfp)) {
      if (strpos($cline, '[') !== false){
        $clinex=trim($cline);          // 改行削除
        $cliney=ltrim($clinex,"[");    // 左端[削除
        $newtbname=rtrim($cliney,"]"); // 右端]削除 
        if ($fsw==0){                /// 最初の[
          $oldtbname=$newtbname;       // tbname保存 
          $fsw=1;                      // fsw on
          
        }else{                       /// 最初以外の[
          //oldtbnameで配列の列データでinsert処理
          $key="";
          $val="";
          foreach ($valarray as $valrec){
            $valrecx=trim($valrec);
            $recarray=explode('=',$valrecx);
            $key=$key.$recarray[0].",";
            $val=$val.$recarray[1].",";
          }
          $key=rtrim($key,",");
          $val=rtrim($val,",");
          $delsql="delete from ".$oldtbname;
          $rtn=create($delsql);
          $inssql="insert into ".$oldtbname." (".$key.") values(".$val.")";
          //echo $inssql.PHP_EOL;
          $rtn=create($inssql);
          if ($rtn==0){
            echo $oldtbname." data successfully inserted".PHP_EOL;
          }else{
            echo $oldtbname." data insert failed".PHP_EOL;
          }
          $valarray=array();
          $oldtbname=$newtbname;
          
        }
      }else{
        array_push($valarray,$cline);    // 列データ配列追加
      }  
    }
  }
}else{
  echo "パスが得られません、kanshiphp.iniを見直して下さい";
}

?>
