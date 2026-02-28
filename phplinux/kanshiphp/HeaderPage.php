<html>
<head>
<meta http-equiv="Content-Type=" content="text/html;charset=utf-8">
</head>
<script src="js/inputCheck.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script type="text/javascript">
<!--
function viewPopup(data) {
  swal.fire({
    title: '',
    width:250,
    height:600,
    html: data,
    showConfirmButton: false,
    confirmButtonText: 'クローズ',
    background: '#dcdcdc',
  });
}
-->
</script>
</html>

<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
///
function popupMsgSet($dataStr){
  $popArr=explode("//",$dataStr);
  $popupData="";
  $firstSw=0;
  foreach($popArr as $popItem){
    if($firstSw==0){
      $popItem="&lt;p&gt;".$popItem."&lt;/p&gt;&lt;p&gt;&lt;font size=2&gt;";
      $firstSw=1;
    }
    $popupData=$popupData.$popItem;    
  }
  $popupData=$popupData."&lt;/font&gt;&lt;/p&gt;";
  return $popupData;
}
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
    $user_sql='select authority,bgcolor from user where userid="'.$user.'"';
    $userRows=getdata($user_sql);
    if(empty($userRows)){
      $msg="#error#unkown#ユーザを見失いました";
      branch('logout.php',$msg);
    }
    $userArr=explode(',',$userRows[0]);
    $authority=$userArr[0];
    $bgColor=$userArr[1];
    $adminSql="select logout from admintb";
    $adminRows=getdata($adminSql);
    $logout=$adminRows[0];
    print '<html><head>';
    /// $logoutはlogout.phpで"1"にする、ログアウト後にリフレッシュしないように制御
    /// $logoutはlogin.phpで"0"にする
    if($logout=="0"){
      print '<meta http-equiv="Refresh" content="120">';
    }
    print '<link rel="stylesheet" href="css/Header.css">';
    print '<link rel="stylesheet" href="css/MouseOver.css">';
    print "</head>";
    $headerDir="header/";
    $header_sql="select * from header";
    $headerRows=getdata($header_sql);
    /// header layout 
    /// title, subtitle, image1,...image5, link1title,...link5title, link1Url,...link5Url 
    $headerArr=explode(',',$headerRows[0]);
    $title = $headerArr[0]; /// title
    $subtitle=$headerArr[1]; /// subtitle
    $linkTtl1=$headerArr[2];
    $linkTtl2=$headerArr[3];
    $linkTtl3=$headerArr[4];
    $linkTtl4=$headerArr[5];
    $linkTtl5="未割り当て";
    $linkUrl1=$headerArr[7];
    $linkUrl2=$headerArr[8];
    $linkUrl3=$headerArr[9];
    $linkUrl4=$headerArr[10];
    $linkUrl5="";
    $imageSel=$headerArr[12];
    $imageMax=$headerArr[13];
    $rimage='header'.strval($imageSel).'.jpg';
    $imageInt=intval($imageSel)+1;
    if ($imageInt > intval($imageMax)){
      $imageInt = 1;
    }
    $header_sql='update header set imagesel='.$imageInt;
    putdata($header_sql);
    $phpImage=$headerDir.'php.jpg';
    ///
    print "<body class={$bgColor} leftmargin='4' marginheight='4' marginwidth='4' topmargin='4'>";
    ///
    print '<table><tr>';
    /// PHP画像とヘッダ画像
    print "<td><img src={$phpImage} width='60' height='80'>&ensp;";
    $targetImage=$headerDir.$rimage;
    print "<img  src={$targetImage} width='600' height='80'>";
    print '</td>';
    /// 時計
    print '<td>&nbsp;';
    print '<iframe src="https://free.timeanddate.com/clock/i9vj2e3l/n248/szw110/szh110/hoc4169e1/hbw0/hfc4169e1/cf100/hnce1ead6/fas30/fdi66/mqc000/mql15/mqw4/mqd98/mhc000/mhl15/mhw4/mhd98/mmc000/mml10/mmw1/mmd98/hhs2/hms2" frameborder="0" width="110" height="110"></iframe>';
    print '</td>';    
    print '<td>&nbsp;';
    print '<iframe src="https://free.timeanddate.com/clock/i9vj2e3l/n248/fs26/fcf5f5f5/tc4169e1/ftb/tt0/tw0/tm1/th1/tb4" frameborder="0" width="161" height="64"></iframe>';
    print '</td>';
    print '<td><font size=1>&nbsp;&nbsp;ログアウト、リセット<br>&nbsp;&nbsp;再ログインおよびURLは<br>&nbsp;&nbsp;右のスクロールバーで<br>&nbsp;&nbsp;下方に出現する</font></td></font>';
    print '</tr>';
    ///
    /// ログアウト、リセット、再ログイン　説明
    ///    
    print '<tr><td class="back"><div style="display:inline-flex">';
    print "<form action={$linkUrl1} target='_blank' method='get'>";
    print "<input class='button' type='submit' name='id' value={$linkTtl1}></form>";  
    print "<form action={$linkUrl2} target='_blank' method='get'>";
    print "<input class='button' type='submit' name='id' value={$linkTtl2}></form>";
    print "<form action={$linkUrl3} target='_blank' method='get'>";
    print "<input class='button' type='submit' name='id' value={$linkTtl3}></form>";
    print "<form action={$linkUrl4} target='_blank' method='get'>";
    print "<input class='button' type='submit' name='id' value={$linkTtl4}></form>";
    print '</div></td>';
    $popDataLogout="監視対象ホストの&#13;&#10;状態を保持した&#13;&#10;まま監視・表示&#13;&#10;を終了する";
    $popDataReset="監視対象ホストを&#13;&#10;初期化し、監視前&#13;&#10;の状態にする";
    $popDataReLogin="監視対象ホストの&#13;&#10;状態を保持した&#13;&#10;ままログイン画面&#13;&#10;を表示する。&#13;&#10;ログアウトの処理&#13;&#10;をしない";
    //print "<td class='mousemsg0'><a href='logout.php' target='_parent' class='buttonlogout'>ログアウト<span class='mousemsg'>{$popDataLogout}</span></a></td>";
    print "<td><div><td class='mousemsg0'>";
    print '<form action="logout.php" target="_parent" onSubmit="return confirmEnd(\'ログアウト\');">';
    print "<font size=2><input type='submit' value={$popDataLogout} class='mousemsg'></font><span class='buttonlogout'>ログアウト</span>";
    print '</form>';
    print '</td>';
    if ($authority=='1'){
      /// reset
      //print "<td class='mousemsg0'><a href='reset.php' target='_parent' class='buttonalerm'>リセット<span class='mousemsg'>{$popDataReset}</span></a></td>";
      print "<td class='mousemsg0'>";
      print '<form action="reset.php" target="_parent" onSubmit="return confirmEnd(\'リセット\');">';
      print "<font size=2><input type='submit' value={$popDataReset} class='mousemsg'></font><span class='buttonalerm'>リセット</span>";
      print '</form>';
      print '</td>';
      /// re-login
      //print "<td class='mousemsg0'><a href='login.php' target='_parent' class='buttonalerm'>再ログイン<span class='mousemsg'>{$popDataReLogin}</span></a></td>";
      print "<td class='mousemsg0'>";
      print '<form action="login.php" target="_parent" onSubmit="return confirmEnd(\'再ログイン\');">';
      print "<font size=2><input type='submit' value={$popDataReLogin} class='mousemsg'></font><span class='buttonalerm'>再ログイン</span>";
      print '</form>';
      print '</td>';
    }
    print '</div></td></tr></table></body></html>';
  
}
?>

