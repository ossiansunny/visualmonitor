<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "mailsendany.php";
///
Function linkcheck($link){  
  if (strlen($link)!=0){
    if (substr($link,0,4)!='http'){
      $nextpage='HeaderEditPage.php';
      $msg="#alert#".$user."#リンクURLがhttpから始まっていません";
      branch($nextpage,$msg);
      exit();
    } 	
  }
  return $link;
}

$pgm="headerupdb.php";
$user=$_GET['user'];
$title = $_GET['title'];
$subtitle=$_GET['subtitle'];
$image1=$_GET['image1'];
$image2=$_GET['image2'];
$image3=$_GET['image3'];
$image4=$_GET['image4'];
$image5="";
$lnkttl1=$_GET['lnkttl1'];
$lnkttl2=$_GET['lnkttl2'];
$lnkttl3=$_GET['lnkttl3'];
$lnkttl4=$_GET['lnkttl4'];
$lnkttl5="";
//$lnkurl1=linkcheck($_GET['lnkurl1']);
//$lnkurl2=linkcheck($_GET['lnkurl2']);
//$lnkurl3=linkcheck($_GET['lnkurl3']);
//$lnkurl4=linkcheck($_GET['lnkurl4']);
$lnkurl1=$_GET['lnkurl1'];
$lnkurl2=$_GET['lnkurl2'];
$lnkurl3=$_GET['lnkurl3'];
$lnkurl4=$_GET['lnkurl4'];
$lnkurl5="";  
$upsql="update header set title='".$title."',subtitle='".$subtitle."',image1='".$image1."',image2='".$image2."',image3='".$image3."',image4='".$image4."',image5='".$image5."',link1title='".$lnkttl1."',link2title='".$lnkttl2."',link3title='".$lnkttl3."',link4title='".$lnkttl4."',link5title='".$lnkttl5."',link1url='".$lnkurl1."',link2url='".$lnkurl2."',link3url='".$lnkurl3."',link4url='".$lnkurl4."',link5url='".$lnkurl5."'"; 
putdata($upsql);
$rdsql="select receiver,sender from admintb";
$rows=getdata($rdsql);
$sdata=explode(',',$rows[0]);
$recv=$sdata[0];
$sender=$sdata[1];
$subj='保守';
$message="ヘッダ情報更新";
mailsendany('headerupdate',$sender,$recv,$subj,$message);
$nextpage='HeaderEditPage.php';
$msg="#notic#".$user."#正常に更新されました";
branch($nextpage,$msg);

?>
