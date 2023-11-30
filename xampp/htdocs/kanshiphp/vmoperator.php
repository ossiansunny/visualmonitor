<?php

if(!isset($_GET['param'])){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="vmoperator.php" method="get">';
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
  $value=$_GET['param'];
  echo '<html><head><meta>';
  echo '<link rel="stylesheet" href="kanshi1.css">';
  echo '</head><body>';
  echo '<table><tr><td>';
  $logout = $value.": ログアウト";
  echo "</td><td><a href='logoutu.php?param={$value}' target='_top'><span class=buttonyell>{$logout}</span></a></td></tr></table>";
  echo '</body></html>';
}
?>
