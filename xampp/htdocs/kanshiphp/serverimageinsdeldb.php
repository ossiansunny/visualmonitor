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
print '<link rel="stylesheet" href="css/kanshi1.css">';
print '</head><body>';

$user=$_GET['user'];
///
/// DELETE
///
if (isset($_GET['del'])){ 
  if (isset($_GET['fckbox'])){
    $imageCheckBox=$_GET['fckbox'];
    $imageArr=arraycheck($imageCheckBox);
    foreach ($imageArr  as  $imageName) {
      $image_sql='delete from serverimage where image="'.$imageName.'"';
      putdata($image_sql); 
      writelogd($pgm,$image_sql);
      $msg="#notic#".$user."#正常にイメージ".$imageName."が削除されました";
      $nextpage="ServerImage.php";
      branch($nextpage,$msg);
    }
  }
///
/// INSERT
/// 
}else{ 
  $imageView=nullcheck($_GET['name']); 
  $imageName=nullcheck($_GET['image']); 
  $image_sql='insert into serverimage (image, name, comment) values("'.$image.'","'.$name.'"," ")';
  putdata($image_sql);
  writelogd($pgm,$image_sql); 
  $msg="#notic#".$user."#正常にイメージ".$imageName."が登録されました";
  $nextpage="ServerImage.php";
  branch($nextpage,$msg);
}


$nextpage="MonitorManager.php";
branch($nextpage,$user);

print '</body></html>';
?>

