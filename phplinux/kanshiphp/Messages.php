<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
require_once "mysqlkanshi.php";
require_once "alarmwindow.php";
///
$pgm="messages.php";
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

print '<html lang="ja">';
print '<head>';
print '<meta http-equiv="refresh" content="10">';
print '<link rel="stylesheet" href="css/kanshi1.css">';
print '</head>';
print '<body>';
print '<h4 class="iro'.$alertCde.'">'.$statMsg.'</h4>';
print '</body>';
print '</html>';
?>

