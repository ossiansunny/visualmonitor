<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
///
$pgm="HeaderPage.php";
$user="";
$brcode="";
$brmsg="";
$authority="";
///
if(!isset($_GET['param'])){
  paramGet($pgm);
}else{
  paramSet();
  ///
  print '<html><head>';
  print '<meta http-equiv="Refresh" content="120">';
  print '<link rel="stylesheet" href="css/kanshihead.css">';
  print '<script language="JavaScript"><!--';
  print 'function changeFramehp(){ parent.hframe.location.href = "HeaderPage.php";}';
  print '--></script>';
  print '</head><body>';
  if ($user=='unknown'){
    $authority='0';
  }else{
    $user_sql='select authority from user where userid="'.$user.'"';
    $userRows=getdata($user_sql);
    $userArr=explode(',',$userRows[0]);
    $authority=$userArr[0];
  }
  /// 管理者と一般ユーザーで背景色を変える
  if ($authority=='1'){
    $bgcolor="F75D59"; /// beans red
  }else{
    $bgcolor="lightgreen"; /// light green
  }
  $headerDir="header/";
  $header_sql="select * from header";
  $headerRows=getdata($header_sql);
  /// header layout 
  /// title, subtitle, image1,...image5, link1title,...link5title, link1Url,...link5Url 
  $headerArr=explode(',',$headerRows[0]);
  $title = $headerArr[0]; /// title
  $subtitle=$headerArr[1]; /// subtitle
  $image=array('','','','','');
  $image[0]=$dir.$headerArr[2];
  $image[1]=$dir.$headerArr[3];
  $image[2]=$dir.$headerArr[4];
  $image[3]=$dir.$headerArr[5];
  $image[4]=$dir.'header5.jpg';
  $linkTtl1=$headerArr[7];
  $linkTtl2=$headerArr[8];
  $linkTtl3=$headerArr[9];
  $linkTtl4=$headerArr[10];
  $linkTtl5="未割り当て";
  $linkUrl1=$headerArr[12];
  $linkUrl2=$headerArr[13];
  $linkUrl3=$headerArr[14];
  $linkUrl4=$headerArr[15];
  $linkUrl5="";
  $imageSel=$headerArr[17];
  $imageSelInt=intval($imageSel);
  $rimage=$image[$imageSelInt];
  $imageSel=$imageSelInt+1;
  if ($imageSel > 4){
    $imageSel = 0;
  }
  $header_sql='update header set imagesel='.strval($imageSel);
  putdata($header_sql);
  $phpImage=$headerDir.'php.jpg';
  ///
  print "<body bgcolor={$bgcolor} leftmargin='4' marginheight='4' marginwidth='4' topmargin='4'>";
  print '<table><tr>';
  print "<td><img src={$phpImage} width='60' height='80'>&ensp;";
  
  if ($user=='unknown'){
    $imageLogOut=$headerDir.'logout.png';
    print "<img src='{$imageLogOut} width='700' height='80'></td>";
  } else{
    $targetImage=$headerDir.$rimage;
    print "<img  src={$targetImage} width='700' height='80'></td>";
  }
  print '<td><iframe scrolling="no" frameborder="no" clocktype="html5" style="overflow:hidden;border:0;margin:0;padding:0;width:100px;height:100px;"src="https://www.clocklink.com/html5embed.php?clock=005&timezone=JST&color=blue&size=100&Title=&Message=&Target=&From=2021,1,1,0,0,0&Color=blue"></iframe></td>';
  print '</tr>';
  print '<tr>';
  print '<td class="back"><div style="display:inline-flex">';
  print "<form action={$linkUrl1} target='_blank' method='get'>";
  print "<input class='button' type='submit' name='id' value={$linkTtl1}></form>";  
  print "<form action={$linkUrl2} target='_blank' method='get'>";
  print "<input class='button' type='submit' name='id' value={$linkTtl2}></form>";
  print "<form action={$linkUrl3} target='_blank' method='get'>";
  print "<input class='button' type='submit' name='id' value={$linkTtl3}></form>";
  print "<form action={$linkUrl4} target='_blank' method='get'>";
  print "<input class='button' type='submit' name='id' value={$linkTtl4}></form>";
  print '</div></td><td>';
  print "<form action='logout.php' target='sframe' method='get'>";
  if ($user=='unknown'){  
    print "<input class='buttonalerm' type='submit' name='id' value='Logout'></form>";
  }else{
    print "<input class='buttonlogout' type='submit' name='id' value='Logout'></form>";
  }
  print '</td></tr></table></font>';
  print '</body></html>';
}
?>

