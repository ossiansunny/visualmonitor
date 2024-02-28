<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
$path_vmsetup=__DIR__;
$path_kanshiphp=str_replace('/vmsetup','',$path_vmsetup);
$path_kanshiphpini=$path_vmsetup."/kanshiphp.ini";
$varread=$path_kanshiphp."/varread.php";
require_once $varread;
$pgm = "init_mysql.php";
$vpath_kanshiphp="";
$vpatharr=array("vpath_kanshiphp");
$rtnv=pathget($vpatharr);
$dbname="";
$dbhost="";
$dbuser="";
$dbhost="";
if(count($rtnv)==1){
  $vpath_kanshiphp=$rtnv[0];
  $cfgpath=$vpath_kanshiphp."/vmsetup/mysqlsetup.cfg";
  $cfp=fopen($cfgpath,'r');
  $cfgarray=array();
  $oksw=0;
  if($cfp){
    while ($cline = fgets($cfp)) {
      //if (str_contains($cline, '[mysql]')){  // php8.0
      if (strpos($cline, '[mysql]') !== false){ // not php8.0
        $oksw=1;
        //echo "Found Mysql Tag\r\n";
        continue;
      } elseif($oksw==1){
        //if (str_contains($cline,'[')){ // php8.0
        if (strpos($cline,'[') !== false){ // not php8.0
          break;
        }else{
          $crline=rtrim($cline);
          $ckeyval=explode('=',$crline);
          if ($ckeyval[0]=='kanshi_db'){
            $dbname=trim($ckeyval[1],'"');
          }elseif($ckeyval[0]=='kanshi_host'){
            $dbhost=trim($ckeyval[1],'"');
          }elseif($ckeyval[0]=='kanshi_user'){
            $dbuser=trim($ckeyval[1],'"');
          }elseif($ckeyval[0]=='kanshi_pass'){
            $dbpass=trim($ckeyval[1],'"');
          }
          array_push($cfgarray,$crline);
        }
      } else {
        continue;
      }
    }
  }
  fclose($cfp);
  $srcpath=$vpath_kanshiphp."/mysqlkanshi.php";
  $dstpath=$vpath_kanshiphp."/mysqlkanshitmp.php";
  $wfp = fopen($dstpath,'w');
  $rfp = fopen($srcpath,"r");
  if($rfp){
    while ($dline = fgets($rfp)) {
      $wline="";
      foreach ($cfgarray as $cfgitem){
        $cfgkeyval=explode('=',$cfgitem);
        $cfgkey='$'.$cfgkeyval[0];
        $dkeyval=explode('=',$dline);
        $dkey=$dkeyval[0];
        //echo $dkey."\r\n";
        if ($cfgkey==$dkey) {
          $wline1='$'.rtrim($cfgitem);
          $wline2=rtrim($wline1,';');
          $wline=$wline2.";";
          //echo $wline1.PHP_EOL;
          //echo $wline2.PHP_EOL;
          //echo $wline.PHP_EOL;
          break;
        }
      }
      if ($wline==""){
        fwrite($wfp,$dline);
      }else{
        fwrite($wfp,$wline.PHP_EOL);
      }
    }
  }
  fclose($rfp);
  fclose($wfp);
/////////////
// createkanshidb.sql作成
//////////////
  
  $vcpath=$vpath_kanshiphp."\\vmsetup\\createkanshidb.sql";
  if (file_exists($vcpath)){
    unlink($vcpath);
  }
  $vcfp = fopen($vcpath,'w');
  $vcline1="drop user if exists '".$dbuser."'@'".$dbhost."';";
  $vcline2="create user '".$dbuser."'@'".$dbhost."' identified by '".$dbpass."';";
  $vcline3="grant all privileges on *.* to '".$dbuser."'@'".$dbhost."';";
  $vcline4="drop database if exists kanshi;";
  $vcline5="create database kanshi;";
  fwrite($vcfp,$vcline1.PHP_EOL);
  fwrite($vcfp,$vcline2.PHP_EOL);
  fwrite($vcfp,$vcline3.PHP_EOL);
  fwrite($vcfp,$vcline4.PHP_EOL);
  fwrite($vcfp,$vcline5.PHP_EOL);
  fclose($vcfp);
/////////////

  $sqlpath=$vpath_kanshiphp."/vmsetup/createkanshidb.sql";
  $mysql="/usr/bin/mysql";
  $cmd=$mysql." -u root --skip-password < ".$sqlpath;
  $out = shell_exec($cmd);
  echo 'mysqlにkanshiデータベースを設定しました';
}else{
  echo "パスが得られません、kanshiphp.iniを見直して下さい";
}
?>
