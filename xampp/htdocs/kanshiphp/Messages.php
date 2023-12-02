<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
require_once "mysqlkanshi.php";
require_once "alarmwindow.php";
$statmsg="";
$cde="";
$rows=getstatus();
$statmsg=$rows[1];
$cde=$rows[0];
$pgm="messages.php";
echo '<html lang="ja">';
echo '<head>';
echo '<meta http-equiv="refresh" content="10">';
echo '<link rel="stylesheet" href="kanshi1.css">';
echo '</head>';
echo '<body>';
echo "<h4 class=iro.{$cde}>{$statmsg}</h4>";
echo '</body>';
echo '</html>';
?>
