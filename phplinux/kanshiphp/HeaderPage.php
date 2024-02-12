<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
///
$pgm="HeaderPage.php";
$user="";
$brcode="";
$brmsg="";
$auth="";
///
if(!isset($_GET['param'])){
  paramGet($pgm);
}else{
  paramSet();
  ///
  print '<html><head>';
  print '<meta http-equiv="Refresh" content="120">';
  print '<link rel="stylesheet" href="kanshihead.css">';
  print '<script language="JavaScript"><!--';
  print 'function changeFramehp(){ parent.hframe.location.href = "HeaderPage.php";}';
  print '--></script>';
  print '</head><body>';
  if ($user=='unknown'){
    $auth='0';
  }else{
    $usql='select authority from user where userid="'.$user.'"';
    $rows=getdata($usql);
    $udata=explode(',',$rows[0]);
    $auth=$udata[0];
  }
  /// 管理者と一般ユーザーで背景色を変える
  if ($auth=='1'){
    $bgcolor="F75D59"; /// beans red
  }else{
    $bgcolor="lightgreen"; /// light green
  }
  $dir="header/";
  $rdsql="select * from header";
  $rows=getdata($rdsql);
  /// header layout 
  /// title, subtitle, image1,...image5, link1title,...link5title, link1url,...link5url 
  $sdata=explode(',',$rows[0]);
  $title = $sdata[0]; /// title
  $subtitle=$sdata[1]; /// subtitle
  $img=array('','','','','');
  $img[0]=$dir.$sdata[2];
  $img[1]=$dir.$sdata[3];
  $img[2]=$dir.$sdata[4];
  $img[3]=$dir.$sdata[5];
  $img[4]=$dir.'header5.jpg';
  $lnkttl1=$sdata[7];
  $lnkttl2=$sdata[8];
  $lnkttl3=$sdata[9];
  $lnkttl4=$sdata[10];
  $lnkttl5="未割り当て";
  $lnkurl1=$sdata[12];
  $lnkurl2=$sdata[13];
  $lnkurl3=$sdata[14];
  $lnkurl4=$sdata[15];
  $lnkurl5="";
  $imgsel=$sdata[17];
  $imgselint=intval($imgsel);
  $rimg=$img[$imgselint];
  $imgsel=$imgselint+1;
  if ($imgsel > 4){
    $imgsel = 0;
  }
  $upsql='update header set imagesel='.strval($imgsel);
  putdata($upsql);
  ///
  print "<body bgcolor={$bgcolor} leftmargin='4' marginheight='4' marginwidth='4' topmargin='4'>";
  print '<table><tr>';
  print "<td><img src='header/php.jpg' width='60' height='80'>&ensp;";
  if ($user=='unknown'){
    $imglogout=$dir.'logout.png';
    print "<img src={$imglogout} width='700' height='80'></td>";
  } else{
    print "<img  src={$rimg} width='700' height='80'></td>";
  }
  print '<td><iframe scrolling="no" frameborder="no" clocktype="html5" style="overflow:hidden;border:0;margin:0;padding:0;width:100px;height:100px;"src="https://www.clocklink.com/html5embed.php?clock=005&timezone=JST&color=blue&size=100&Title=&Message=&Target=&From=2021,1,1,0,0,0&Color=blue"></iframe></td>';
  print '</tr>';
  print '<tr>';
  print '<td class="back"><div style="display:inline-flex">';
  print "<form action={$lnkurl1} target='_blank' method='get'>";
  print "<input class='button' type='submit' name='id' value={$lnkttl1}></form>";  
  print "<form action={$lnkurl2} target='_blank' method='get'>";
  print "<input class='button' type='submit' name='id' value={$lnkttl2}></form>";
  print "<form action={$lnkurl3} target='_blank' method='get'>";
  print "<input class='button' type='submit' name='id' value={$lnkttl3}></form>";
  print "<form action={$lnkurl4} target='_blank' method='get'>";
  print "<input class='button' type='submit' name='id' value={$lnkttl4}></form>";
  print '</div></td><td>';
  //print '<form onsubmit="javascript:changeFramehp()">';
  //print '<input class="buttonhref" type="submit" value="ヘッダ更新"></form>';
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

