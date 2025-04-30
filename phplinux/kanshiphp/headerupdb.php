<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "mailsend.php";
///
Function linkcheck($_link){  
  if (strlen($_link)!=0){
    if (substr($_link,0,4)!='http'){
      $nextpage='HeaderEditPage.php';
      $msg="#alert#".$user."#リンクURLがhttpから始まっていません";
      branch($nextpage,$msg);
      exit();
    } 	
  }
  return $_link;
}
///
$pgm="headerupdb.php";
$user=$_GET['user'];
$title = $_GET['title'];
$subtitle=$_GET['subtitle'];
$lnkttl1=$_GET['lnkttl1'];
$lnkttl2=$_GET['lnkttl2'];
$lnkttl3=$_GET['lnkttl3'];
$lnkttl4=$_GET['lnkttl4'];
$lnkurl1=$_GET['lnkurl1'];
$lnkurl2=$_GET['lnkurl2'];
$lnkurl3=$_GET['lnkurl3'];
$lnkurl4=$_GET['lnkurl4'];
$imageMax=$_GET['imageMax'];  
$header_sql="update header set title='".$title."',subtitle='".$subtitle."',link1title='".$lnkttl1."',link2title='".$lnkttl2."',link3title='".$lnkttl3."',link4title='".$lnkttl4."',link5title='".$lnkttl5."',link1url='".$lnkurl1."',link2url='".$lnkurl2."',link3url='".$lnkurl3."',link4url='".$lnkurl4."',link5url='".$lnkurl5."',imagemax=".$imageMax; 
putdata($header_sql);
mailsend('',$user,'0','ヘッダ情報更新','','','');
$nextpage='HeaderEditPage.php';
$msg="#notic#".$user."#正常に更新されました";
branch($nextpage,$msg);

?>

