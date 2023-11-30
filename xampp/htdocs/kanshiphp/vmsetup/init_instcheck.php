<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
$path_vmsetup=__DIR__;
$path_kanshiphp=str_replace('\vmsetup','',$path_vmsetup);
$path_varread=$path_kanshiphp."\\varread.php";
$path_mysqlkanshi=$path_kanshiphp."\\mysqlkanshitmp.php";
require_once $path_varread;
require_once $path_mysqlkanshi;
$pgm = "init_instcheck.php";
$vpath_kanshiphp="";
$vpath_xampp="";
$vpatharr=array("vpath_kanshiphp","vpath_xampp");
$rtnv=pathget($vpatharr);
if(count($rtnv)==2){
  $vpath_kanshiphp=$rtnv[0];
  $vpath_xampp=$rtnv[1];
  /// バックアップを削除、現行をバックアップ、新規を現行へ
  $file_old=$vpath_kanshiphp."\\mysqlkanshi.php.old";
  $file_tmp=$vpath_kanshiphp."\\mysqlkanshitmp.php";
  $file=$vpath_kanshiphp."\\mysqlkanshi.php";
  $createdTime = filectime($file);
  $cdate=date("Ymd", $createdTime);
  /// 実行確認
  $ssql="select kanripass from admintb";
  $rows=getdata($ssql);
  $row=explode(',',$rows[0]);
  $bdate=$row[0];
  /// 同日実行回避
  if ($bdate == $cdate){
    echo "同日に再実行は出来ません、admintbのkanripass欄の変更が必要です";
  }else{
    /// mysqlkanshi.php.old削除 　　　
    if(file_exists($file_old)){     
      unlink($file_old);
    }
    /// mysqlkanshi.phpをmysqlkanshi.php.oldへ改名
    rename($file,$file_old);
    /// mysqlkanshitmp.phpを正式なmsqlkanshi.phpへコピー
    copy($file_tmp,$file);
    echo "新しいmysqlkanshi.php が作成されました".PHP_EOL;
    $usql="update admintb set kanripass='".$cdate."'";  
    putdata($usql);
  }

}else{
  echo "パスが得られません、kanshiphp.iniを見直して下さい";
}
?>
