<?php

//error_reporting(E_ALL & ~E_NOTICE);
//require_once "mysqlkanshi.php";
//require_once "mailsendany.php";
//
//date_default_timezone_set('Asia/Tokyo');
$pgm = "logoutu.php";
//
if(!isset($_GET['param'])){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="logout.php" method="get">';
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
  $user=$_GET['param'];
  /// 終了メール
  //$now=date('ymdHis');
  //$tstamp = $now;
  //$adminsql='select * from admintb';
  //$arows=getdata($adminsql);
  //$adata=explode(',',$arows[0]);
  //$to=$adata[3];
  //$from=$adata[4];
  //$logname='LOGOUT '.$user;
  /// Write Event Log
  //$insql = "insert into eventlog (host, eventtime, eventtype) values('".$logname."','" . $tstamp . "','0')";
  //putdata($insql); 
  //$msg = $logname . " Eventlog Insert sql: " . $insql;
  //writeloge($pgm,$msg); 
  /// 終了メール
  //$message=$user.' Logged out';
  //echo "進行中・・・";
  //$rtn=mailsendany('loginlogout',$from,$to,$sub,$message);

  //  echo 'Content-type: text/html; charset=UTF-8\n';
  echo '<!DOCTYPE html>';
  echo '<html>';
  echo '<head>';
  echo '<meta charset="utf-8">';
  echo '<title>サンプル</title>';
  echo '<link rel="stylesheet" href="login.css">';
  echo '</head>';
  echo '<body>';
  echo '<div class="login">';
  echo '<div class="login-triangle"></div>';
  echo '<h2 class="login-header"><img src="header/php.jpg" width="70" height="70">&emsp;&emsp;ログアウト</h2>';
  echo '<p><font color="white">ブラウザの閉じる「X」でクローズして下さい</font></p>';
  echo '</div>';
  echo '<div class="login">';
  echo '</div>';
  echo '</body>';
  echo '</html>';
}
?>