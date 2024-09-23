<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
$dirSep='';
if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
  /// xampp apache
  $dirSep='\\';
}else{
  /// Linux
  $dirSep='/';
}
$path_vmsetup=__DIR__;
$sepVmsetup=$dirSep.'vmsetup';
$path_kanshiphp=str_replace($sepVmsetup,'',$path_vmsetup);
$path_kanshiphpini=$path_vmsetup.$dirSep."kanshiphp.ini";
$pgm = "init_mysql.php";
$dbname="";
$dbhost="";
$dbuser="";
$dbhost="";
$exhost="";
$exuser="";
$exhost="";
$cfgpath=$path_vmsetup.$dirSep."mysqlsetup.cfg";
$cfp=fopen($cfgpath,'r');
$cfgarray=array();
$extarray=array();
$oksw=0;
if($cfp){
  while ($cline = fgets($cfp)) {
    if (strpos($cline, '[mysql]') !== false){ // not php8.0
      $oksw=1;
      //echo "Found Mysql Tag\r\n";
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
$srcpath=$path_kanshiphp.$dirSep."mysqlkanshi.php";
$dstpath=$path_kanshiphp.$dirSep."mysqlkanshitmp.php";
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
echo 'mysqlkanshitmp.php successfully created.'.PHP_EOL;
/////////////
// createkanshidb.sql作成
//////////////
$vcpath=$path_vmsetup.$dirSep."createkanshidb.sql";
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
$sqlpath=$path_vmsetup.$dirSep."createkanshidb.sql";
$clientcfg=$path_vmsetup.$dirSep."sqlexisting.cfg";
$cmd="mysql --defaults-extra-file={$clientcfg} < ".$sqlpath;
//echo $cmd.PHP_EOL;
exec($cmd,$out,$rtn);
if($rtn==1){
  echo '-------------------------------------------------------------------------'.PHP_EOL;
  echo ' mysql アクセスに失敗しました、ユーザ、データベースを手動で作成して下さい'.PHP_EOL;
  echo ' $ mysql -h <既存ホスト> -u <既存ユーザ> -p < ./createkanshidb.sql       '.PHP_EOL; 
  echo ' Enter password: <既存パスワード>                                        '.PHP_EOL;
  echo ' ...                                                                     '.PHP_EOL;
  echo '-------------------------------------------------------------------------'.PHP_EOL;
}else{
  echo "mysqlにuser='{$dbuser}'@'{$dbhost}'、passwd='{$dbpass}'、db='{$dbname}'を設定しました".PHP_EOL;
}

?>
