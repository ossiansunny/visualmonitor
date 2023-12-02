<?php

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
