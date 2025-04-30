<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
$path_vmsetup=__DIR__;
$path_varread=$path_vmsetup."/init-varread.php";
require_once $path_varread;
$vpathArr=array("vpath_kanshiphp");
$rtnv=pathget($vpathArr);
if(count($rtnv)==1){
  $vpath_kanshiphp=$rtnv[0];
 
  $path_kanshiphpini=$path_vmsetup."/kanshiphp.ini";
  $pgm = "init_mysql.php";
  $dbname="";
  $dbhost="";
  $dbuser="";
  $dbhost="";
  $exhost="";
  $exuser="";
  $exhost="";
  $cfgpath=$path_vmsetup."/mysqlsetup.cfg";
  $cfp=fopen($cfgpath,'r');
  $cfgarray=array();
  $extarray=array();
  $oksw=0;
  if($cfp){
    while ($cline = fgets($cfp)) {
      if (strpos($cline, '[mysql]') !== false){ // not php8.0
        $oksw=1;        
        continue;
      } elseif  (strpos($cline,'[existing]') !== false){ 
        $oksw=2;
        continue;
      } elseif (strpos($cline,'[anytag]') !== false){
        break;
      }
      if ($oksw==1){
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
        echo $crline.PHP_EOL;
        continue;
      } elseif($oksw==2){
        $crline=rtrim($cline);
        $ckeyval=explode('=',$crline);
        if ($ckeyval[0]=='kanshi_host'){
          $exhost=trim($ckeyval[1],'"');
        }elseif($ckeyval[0]=='kanshi_user'){
          $exuser=trim($ckeyval[1],'"');
        }elseif($ckeyval[0]=='kanshi_pass'){
          $expass=trim($ckeyval[1],'"');
        }
        array_push($extarray,$crline);
        echo $crline.PHP_EOL;
        continue;
      }else{
        echo 'mysqlsetup.cfgを見直して下さい'.PHP_EOL;
      } 
    }
  }
  /// mysqlkanshi.phpにhost,user,pass,dbを埋め込み
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
        if ($cfgkey==$dkey) {
          $wline1='$'.rtrim($cfgitem);
          $wline2=rtrim($wline1,';');
          $wline=$wline2.";";
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
  echo 'mysqlkanshitmp.php successfully created.'.PHP_EOL;
  /////////////
  // createkanshidb.sql作成
  //////////////
  $vcpath=$path_vmsetup."/createkanshidb.sql";
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
  echo 'createkanshidb.sql successfully created.'.PHP_EOL;
  /////////////
  $sqlpath=$path_vmsetup."/createkanshidb.sql";
  $clientcfg=$path_vmsetup."/sqlexisting.cfg";
  $cmd="mysql --defaults-extra-file={$clientcfg} < ".$sqlpath;
  exec($cmd,$out,$rtn);
  if($rtn==1){
    echo '-------------------------------------------------------------------------'.PHP_EOL;
    echo ' mysql アクセスに失敗しました、ユーザ、データベースを手動で作成して下さい'.PHP_EOL;
    echo ' 作成例は、kanshiデータベース手動作成.txtにあります'.PHP_EOL;
    echo '-------------------------------------------------------------------------'.PHP_EOL;
  }else{
    echo "mysqlにuser='{$dbuser}'@'{$dbhost}'、passwd='{$dbpass}'、db='{$dbname}'を設定しました".PHP_EOL;
  }
}else{
  echo 'varreadパラメータが存在しない可能性があります。kanshiphp,iniをチェック'.PHP_EOL;
}



?>
