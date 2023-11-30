<?php
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

function branch($_page,$_param){
  //#echo 'Content-type: text/html; charset=UTF-8\n';
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="'.$_page.'" method="get">';
  echo '<input type=hidden name=param value="'.$_param.'">';
  echo '<input type="submit" name="next" value="Waiting...">';
  echo '</form>';
}
echo '<html><head><meta>';
echo '<link rel="stylesheet" href="kanshi1.css">';
echo '</head><body>';
$pgm="serverimageinsdel.php";

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
}


$nextpage="MonitorManager.php";
branch($nextpage,$user);

echo '</body></html>';
?>
