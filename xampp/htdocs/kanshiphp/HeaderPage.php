<?php
require_once "mysqlkanshi.php";

if(!isset($_GET['param'])){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="HeaderPage.php" method="get">';
  echo '<input type="hidden" name="param" value="">';
  echo '<input type="submit" name="next" style="display:none;" />';
  echo '</form></body></html>';
  echo '<script type="text/javascript">';
  echo 'var keyvalue = sessionStorage.getItem("user");';
  echo 'if (!keyvalue) {';
  echo '  keyvalue = "unknown";';
  echo '}';
  echo 'document.forms["F"].elements["param"].value = keyvalue;';
  echo '</script>';
}else{
  $uid=$_GET['param'];
  echo '<html><head>';
  echo '<meta http-equiv="Refresh" content="120">';
  echo '<link rel="stylesheet" href="kanshihead.css">';
  echo '<script language="JavaScript"><!--';
  echo 'function changeFramehp(){ parent.hframe.location.href = "HeaderPage.php";}';
  echo '--></script>';
  echo '</head><body>';
  $usql='select authority from user where userid="'.$uid.'"';
  $rows=getdata($usql);
  $udata=explode(',',$rows[0]);
  $auth=$udata[0];
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
  
  echo "<body bgcolor={$bgcolor} leftmargin='4' marginheight='4' marginwidth='4' topmargin='4'>";
  echo '<table><tr>';
  echo "<td><img src='header/php.jpg' width='60' height='80'>&ensp;<img src={$rimg} width='700' height='80'></td>";
  echo '<td><iframe scrolling="no" frameborder="no" clocktype="html5" style="overflow:hidden;border:0;margin:0;padding:0;width:100px;height:100px;"src="https://www.clocklink.com/html5embed.php?clock=005&timezone=JST&color=blue&size=100&Title=&Message=&Target=&From=2021,1,1,0,0,0&Color=blue"></iframe></td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td class="back"><div style="display:inline-flex">';
  echo "<form action={$lnkurl1} target='_blank' method='get'>";
  echo "<input class='button' type='submit' name='id' value={$lnkttl1}></form>";  
  echo "<form action={$lnkurl2} target='_blank' method='get'>";
  echo "<input class='button' type='submit' name='id' value={$lnkttl2}></form>";
  echo "<form action={$lnkurl3} target='_blank' method='get'>";
  echo "<input class='button' type='submit' name='id' value={$lnkttl3}></form>";
  echo "<form action={$lnkurl4} target='_blank' method='get'>";
  echo "<input class='button' type='submit' name='id' value={$lnkttl4}></form>";
  echo '</div></td><td><div>';
  echo '<form onsubmit="javascript:changeFramehp()">';
  echo '<input class="buttonhref" type="submit" value="ヘッダ交換"></form>';
  echo '</div></td></tr></table></font>';
  echo '</body></html>';
}
?>
