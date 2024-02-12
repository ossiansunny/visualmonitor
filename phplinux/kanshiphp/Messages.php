<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
require_once "mysqlkanshi.php";
require_once "alarmwindow.php";
///
$pgm="messages.php";
$statmsg="";
$cde="";
$rows=getstatus();
$cde=$rows[0];
$statmsg=$rows[1];
for ($count=1;$count < 5;$count++){
  if (empty($statmsg) || $statmsg==" " || is_null($statmsg)){
    $rows=getstatus();
    $cde=$rows[0];
    $statmsg=$rows[1];
    continue;
  }else{
    break;
  }
} 

print '<html lang="ja">';
print '<head>';
print '<meta http-equiv="refresh" content="10">';
print '<link rel="stylesheet" href="kanshi1.css">';
print '</head>';
print '<body>';
print '<h4 class="iro'.$cde.'">'.$statmsg.'</h4>';
print '</body>';
print '</html>';
?>

