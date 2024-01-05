<?php
require_once 'BaseFunction.php';
require_once 'mysqlkanshi.php';

function arraycheck($data){
  $dataarr=array();
  if (is_array($data)){
    $dataarr=$data;
  }else{
    $dataarr[0]=$data;
  }
  return $dataarr;
}

function nullcheck($data){
  if (!isset($data)){
    return ' ';
  }else{
    return $data;
  }
}

$pgm="serverimageinsdeldb.php";
$user="";
///
print '<html><head><meta>';
print '<link rel="stylesheet" href="kanshi1.css">';
print '</head><body>';

$user=$_GET['user'];
///
/// DELETE
///
if (isset($_GET['del'])){ 
  if (isset($_GET['fckbox'])){
    $ffckbox=$_GET['fckbox'];
    $fckbox=arraycheck($ffckbox);
    foreach ($fckbox  as  $data) {
      $delsql='delete from serverimage where image="'.$data.'"';
      putdata($delsql); 
      writelogd($pgm,$delsql);
      $msg="#notic#".$user."#正常にイメージ".$data."が削除されました";
      $nextpage="ServerImage.php";
      branch($nextpage,$msg);
    }
  }
///
/// INSERT
/// 
}else{ 
  $name=nullcheck($_GET['name']); 
  $image=nullcheck($_GET['image']); 
  $inssql='insert into serverimage (image, name, comment) values("'.$image.'","'.$name.'"," ")';
  putdata($inssql);
  writelogd($pgm,$inssql); 
  $msg="#notic#".$user."#正常にイメージ".$data."が登録されました";
  $nextpage="ServerImage.php";
  branch($nextpage,$msg);
}


$nextpage="MonitorManager.php";
branch($nextpage,$user);

print '</body></html>';
?>

