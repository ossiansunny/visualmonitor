<?php
require_once "mysqlkanshi.php";
require_once "mailsendany.php";

function branch($_page,$_param){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo "<form name='F' action={$_page} method='get'>";
  echo "<input type=hidden name=param value={$_param}>";
  echo '<input type="submit" name="next" value="Waiting...">';
  echo '</form>';
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
$nextpage="MonitorManager.php";
branch($nextpage,$user);
?>
