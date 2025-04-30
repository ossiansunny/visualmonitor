<html>
<head>
<meta http-equiv="Content-Type=" content="text/html;charset=utf-8">
</head>
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
//background: '#FFFF00',
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
    print "<img  src={$targetImage} width='700' height='80'>";
    print '</td>';
    /// 時計
    print '<td>';
    print '<iframe scrolling="no" frameborder="no" clocktype="html5" style="overflow:hidden;border:0;margin:0;padding:0;width:100px;height:100px;"src="https://www.clocklink.com/html5embed.php?clock=005&timezone=JST&color=blue&size=100&Title=&Message=&Target=&From=2021,1,1,0,0,0&Color=blue"></iframe>';
    print '</td>';
    /// ログアウト、リセット、再ログイン　説明
    
    print '<td>';
    print '<table><tr>';
    /*
    $popData1="ログアウト//監視対象ホストの状態を保持したまま監視・表示を終了する";
    $popupRtnData=popupMsgSet($popData1);
    $amsg='<a  style="text-decoration:none;" href="" onclick="viewPopup(\''.$popupRtnData.'\'); return false;"><font size=1 color=white>ログアウト？</font></a>';
    print "<td>{$amsg}</td>";
    ///
    $popData2="リセット//監視対象ホストを初期化し、監視前の状態にする";
    $popupRtnData=popupMsgSet($popData2);
    $amsg='<a  style="text-decoration:none;" href="" onclick="viewPopup(\''.$popupRtnData.'\'); return false;"><font size=1 color=white>リセット？</font></a>';
    print "<td>{$amsg}</td>";
    ///
    $popData3="再ログイン//監視対象ホストの状態を保持したままログイン画面を表示する。ログアウトの処理をしない";
    $popupRtnData=popupMsgSet($popData3);
    $amsg='<a  style="text-decoration:none;" href="" onclick="viewPopup(\''.$popupRtnData.'\'); return false;"><font size=1 color=white>再ログイン？</font></a>';
    print "<td>{$amsg}</td>";
    */    
    print '</tr></table>';
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
    $popDataLogout="監視対象ホストの状態を保持したまま監視・表示を終了する";
    $popDataReset="監視対象ホストを初期化し、監視前の状態にする";
    $popDataReLogin="監視対象ホストの状態を保持したままログイン画面を表示する。ログアウトの処理をしない";
    print "<td class='mousemsg0'><a href='logout.php' target='sframe' class='buttonlogout'>ログアウト<span class='mousemsg'>{$popDataLogout}</span></a></td>";
    
    if ($authority=='1'){
      /// reset
      print "<td class='mousemsg0'><a href='reset.php' target='sframe' class='buttonalerm'>リセット<span class='mousemsg'>{$popDataReset}</span></a></td>";
      /// re-login
      print "<td class='mousemsg0'><a href='login.php' target='_parent' class='buttonalerm'>再ログイン<span class='mousemsg'>{$popDataReLogin}</span></a></td>";
    }
    print '</tr></table></body></html>';
  
}
?>

