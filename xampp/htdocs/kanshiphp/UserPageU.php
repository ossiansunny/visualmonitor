<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

$pgm="UserPageU.php";
$brmsg="";
$user="";
$brcode="";

if(isset($_GET['update'])){
  $user=$_GET['user'];
  
  /// 更新処理      
  $userId=$_GET['uid'];
  $password=$_GET['upass'];
  $authority=$_GET['auth'];
  $userName=$_GET['uname'];
  $userCode=$_GET['ucode'];
  $bgColor=$_GET['bgcolor'];
  $audio=$_GET['audio'];
  $timeStamp = date('ymdHis');  
  $user_sql="update user set password='".$password."',authority='".$authority."',username='".$userName."',usercode='".$userCode."',timestamp='".$timeStamp."',bgcolor='".$bgColor."',audio='".$audio."' where userid='".$userId."'";  
  putdata($user_sql);
  $brmsg="ユーザー".$userId."が更新されました";
  $brcode='#notic#'.$userId.'#'.$brmsg;
  branch($pgm,$brcode);

}elseif(isset($_GET['param'])){
  paramSet();
  ///
  $user_sql="select userid,password,username,usercode,timestamp,bgcolor,audio from user where userid='".$user."'";
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    writeloge($pgm,$msg);
    //branch('logout.php',$msg);
  }
  ///
  $userArr=explode(',',$userRows[0]);
  $rd_userId=$userArr[0];
  $rd_password=$userArr[1];
  $rd_authority='0';
  $rd_userName=$userArr[2];
  $rd_userCode=$userArr[3];
  $rd_timeStamp=$userArr[4];
  $rd_bgColor=$userArr[5];
  $rd_audio=$userArr[6];
  $value="";
  $title1='<img src="header/php.jpg" width="30" height="30">';
  $title2=' ▽　ユーザー管理　▽   ';
  $title=$title1 . $title2;
  print '<html><head>';
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';
  print "</head><body class={$rd_bgColor}>";
  /// エラー表示
  if ($brcode=="alert" or $brcode=="error" or $brcode=="notic"){
    print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }
  print "<h2>{$title}</h2>";
  /// 
  ///--------------------表示処理 --------------------
  ///

  /// 変更データ表示
  print '<h3>☆警告音が試聴出来ます、再生ボタンをクリックして下さい<br>';
  print '<font color=yellow>注意：ブラウザの音声自動再生が許可状態に限ります</font></h3>';
  print '<table boder=1>';
  print '<tr><th>無音</th><th>警告音１</th><th>警告音２</th><th>警告音３</th><th>警告音４</th></tr>';
  print '<tr>';
  print '<td><audio  controls src="audio/nonalert.mp3"></audio></td>';
  print '<td><audio  controls src="audio/alert1.mp3"></audio></td>';
  print '<td><audio  controls src="audio/alert2.mp3"></audio></td>';
  print '<td><audio  controls src="audio/alert3.mp3"></audio></td>';
  print '<td><audio  controls src="audio/alert4.mp3"></audio></td>';
  print '</tr>';
  print '</table>';
  print '<hr>';
  print '<h3>☆背景色・画像一覧<br>背景色欄を選択して下さい</h3>';
  print '<table>';
  print '<tr><th>標準</th><th>bgimage1</th><th>bgimage2</th><th>bgimage3</th><th>bgimage4</th></tr>';
  print '<tr><td><img src=header/bgstand.jpg width=120px height=100px></td>';
  print '<td><img src=header/bgimage1.jpg width=120px height=100px></td>';
  print '<td><img src=header/bgimage2.jpg width=120px height=100px></td>';
  print '<td><img src=header/bgimage3.jpg width=120px height=100px></td>';
  print '<td><img src=header/bgimage4.jpg width=120px height=100px></td></tr>';
  print '</table>';
  print '<hr>';
  print '&nbsp;&nbsp;<form method="get" action="UserPageU.php" onsubmit="return check()">';
  print '<h3>☆「変更」するものを１つ選択して下さい</h3>';
  print '<table class="nowrap">';
  print '<tr><th >ユーザーID</th><th>パスワード</th><th>権限</th><th>ユーザー名</th><th>コード</th><th>作成変更日</th><th>背景色</th><th>警告音</th></tr>';
  $cssColor='cwhite';
  $selBgColorArr=array('','','','','','','','','','');
  switch($rd_bgColor){
    case "bgstand": $selBgColorArr[0]="selected"; break;
    case "bgimage1": $selBgColorArr[1]="selected"; break;
    case "bgimage2": $selBgColorArr[2]="selected"; break;
    case "bgimage3": $selBgColorArr[3]="selected"; break;
    case "bgimage4": $selBgColorArr[4]="selected"; break;
  }  
  
  $selAudioArr=array('','','','','');
  switch($rd_audio){
    case "nonalert.mp3": $selAudioArr[0]="selected"; break;
    case "alert1.mp3": $selAudioArr[1]="selected"; break;
    case "alert2.mp3": $selAudioArr[2]="selected"; break;
    case "alert3.mp3": $selAudioArr[3]="selected"; break;
    case "alert4.mp3": $selAudioArr[4]="selected"; break;
  } 
  print '<tr>';
  print "<td ><input class={$cssColor} type=text name=uid value={$rd_userId} size=9 readonly></td>";
  print "<td ><input class={$cssColor} type=text name=upass value={$rd_password} size=9 ></td>";
  print "<td ><input class={$cssColor} type=text name=auth value='一般ユーザー' readonly></td>";
  print "<td ><input class={$cssColor} type=text name=uname value={$rd_userName} size=19 ></td>";
  print "<td ><input class={$cssColor} type=text name=ucode value={$rd_userCode} size=4 readonly></td>";
  print "<td ><input class={$cssColor} type=text name=tstamp value={$rd_timeStamp} size=10 readonly></td>";
  print "<td><select class={$cssColor} name=bgcolor>";
  print "<option value='bgstand' {$selBgColorArr[0]}>標準</option>";
  print "<option value='bgimage1' {$selBgColorArr[1]}>bgimage1</option>";
  print "<option value='bgimage2' {$selBgColorArr[2]}>bgimage2</option>";
  print "<option value='bgimage3' {$selBgColorArr[3]}>bgimage3</option>";
  print "<option value='bgimage4' {$selBgColorArr[4]}>bgimage4</option>";
  print '</select></td>';
  print "<td><select class={$cssColor} name=audio>";
  print "<option value='nonalert.mp3' {$selAudioArr[0]}>無音</option>";
  print "<option value='alert1.mp3' {$selAudioArr[1]}>警告音１</option>";
  print "<option value='alert2.mp3' {$selAudioArr[2]}>警告音２</option>";
  print "<option value='alert3.mp3' {$selAudioArr[3]}>警告音３</option>";
  print "<option value='alert4.mp3' {$selAudioArr[4]}>警告音４</option>";
  print "</select></td>"; 
  print '</tr>';
  
  print '</table>';
  print "<input type=hidden name=user value={$user}>";
  print "<br><input class='button' type='submit' name='update' value='変更実行' >";
  print '<input type="hidden" name="onbtn">';
  print '</form>';
  print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>"; 
  print '</body></html>';
}else{
  paramGet($pgm);
}
?>

