<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
<meta http-equiv="refresh" content="60">
<link rel="stylesheet" href="kanshi1.css">
</head>
<body>
<h4>MRTG Refresh 60sec</h4>

<?php
require_once "mysqlkanshi.php";
require_once "varread.php";
date_default_timezone_set('Asia/Tokyo');
$pgm='MrtgAutoRun.php';

$vpatharr=array("vpath_htdocs");
$rtnv=pathget($vpatharr);
$htdocs=$rtnv[0];
//$dat=date('y/m/d H:i:s');
$cmd1=$htdocs.'/bin/mrtgrun.sh '.$htdocs;
$out1 = shell_exec($cmd1);
writelogd($pgm,'shell_exec '.$cmd1);
//echo "shell_exec " . $out1 . "<br>";
$cmd2=$htdocs.'/bin/plotrun.sh '.$htdocs;
$out2 = shell_exec($cmd2);
writelogd($pgm,'shell_exec '.$cmd2);


//$data='192.168.1.209,,,,,NextGull,,';
//mailsendping($data,'PROBLEM');

?>
</body>
</html>
