<?php

function branchtarget($_page,$_param,$_target,$_jump){
  print '<html lang="ja">';
  print '<meta http-equiv="Content-Type" content="text/html;charset=utf-8">';
  print '<meta http-equiv="refresh" content="1;'.$_jump.'">';
  print '<body onLoad="document.F.submit();">';
  print "<form name='F' action={$_page} target={$_target} method='get'>";
  print '<input type=hidden name=param value="'.$_param.'">';
  print '<input type="submit" name="next" value="お待ち下さい...">';
  print '</form>';
  exit();
}
?>
