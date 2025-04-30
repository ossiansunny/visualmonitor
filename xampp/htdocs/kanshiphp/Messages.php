<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "alarmwindow.php";
///
$pgm="Messages.php";
$user=""; ///BaseFunctionでセットされる
$brcode="";
$brmsg="";
///
if(!isset($_GET['param'])){
  paramGet($pgm);
}else{
  paramSet();

  $statMsg="";
  $alertCde="";
  $statRows=getstatus();
  $alertCde=$statRows[0];
  $statMsg=$statRows[1];
  for ($count=1;$count < 5;$count++){
    if (empty($statMsg) || $statMsg==" " || is_null($statMsg)){
      $statRows=getstatus();
      $alertCde=$statRows[0];
      $statMsg=$statRows[1];
      continue;
    }else{
      break;
    }
  } 
  $statJapan='';
  switch ($statMsg){
    case "Mail Server Active":
      $statJapan="メールサーバ有効";
      break;
    case "Mail Server InActive":
      $statJapan="メールサーバ無効";
      break;
    case "Web Log Remain":
      $statJapan="旧Webログあり";
      break;
    case "Kanshi Log Remain":
      $statJapan="旧監視ログあり";
      break;
    case "Plot Log Remain":
      $statJapan="旧プロットログあり";
  }
  $user_sql='select authority,bgcolor from user where userid="'.$user.'"';
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    branch('logout.php',$msg);
  }
  $userArr=explode(',',$userRows[0]);
  $user_Auth=$userArr[0];
  $user_bgColor=$userArr[1];
  $admin_sql='select logout from admintb';
  $adminRows=getdata($admin_sql);
  print '<html lang="ja">';
  print '<head>';
  print '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
  if($adminRows[0]=='0'){
    print '<meta http-equiv="refresh" content="10">';
  }
  print '<link rel="stylesheet" href="css/MsgMenu.css">';
  print '</head>';
  print '<body class="'.$user_bgColor.'">';
  print '<div ><table><tr><td>';
  print '<h5 class="iro'.$alertCde.'">'.$statJapan.'</h5>';
  print '</td></tr></table></div>';
  print '</body>';
  print '</html>';
}
?>

