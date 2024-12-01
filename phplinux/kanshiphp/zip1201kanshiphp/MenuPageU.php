<?php
require_once 'varread.php';
require_once 'mysqlkanshi.php';
require_once 'BaseFunction.php';

function mrtgck(){
  $exists='0';
  $mrtgBinPath='';
  $vpathParam=array("vpath_mrtgbase");
  $rtnPath=pathget($vpathParam);
  $mrtgPath=$rtnPath[0];
  if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
    $osDirSep='\\';
    $mrtgBinPath=$mrtgPath.'\\bin\\mrtg';    
  }else{
    $osDirSep='/';
    $mrtgBinPath='/usr/bin/mrtg';    
  }
  if(! file_exists($mrtgBinPath)){
    $exists='1';    
  }
  return $exists;
}
function plotck(){
  $exists='0';
  $plotBinPath='';
  $vpathParam=array("vpath_gnuplotbin");
  $rtnPath=pathget($vpathParam);
  if(!count($rtnPath)==1){
    $exists='1';
  }else{
    if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
      $osDirSep='\\';
      $plotBinPath=$rtnPath[0].'\\gnuplot.exe';      
    }else{
      $osDirSep='/';
      $plotBinPath=$rtnPath[0].'/gnuplot';      
    }
    if(! file_exists($plotBinPath)){      
      $exists='1';
    }
  }
  return $exists;
}
function mailck(){
  $exists='0';
  $vendorPath='';
  $vpathParam=array("vpath_phpmailer");
  $rtnPath=pathget($vpathParam);
  if(!count($rtnPath)==1){
    $exists='1';
  }else{
    if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
      $osDirSep='\\';
      $vendorPath=$rtnPath[0].'\\vendor\\phpmailer\\phpmailer\\src\\PHPMailer.php';      
    }else{
      $osDirSep='/';
      $vendorPath=$rtnPath[0].'/vendor/phpmailer/phpmailer/src/PHPMailer.php';      
    }
    if(! file_exists($vendorPath)){      
      $exists='1';
    }
  }
  return $exists;
}
$pgm="MenuPageU.php";
$user="";
$brcode="";
$brmsg="";
///
if(!isset($_GET['param'])){
  paramGet($pgm);
  ///
}else{
  paramSet();
///

$user_sql='select authority,bgcolor from user where userid="'.$user.'"';
$userRows=getdata($user_sql);
$userArr=explode(',',$userRows[0]);
$auth=$userArr[0];
$bgColor=$userArr[1];

print '<html lang="ja">';
print '<head>';
print '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
print '<link rel="stylesheet" href="css/MenuPage.css">';
print '<title>Welcome to My WebSite</title>';
print '</head>';
print "<body class='".$bgColor."'>";
print '<div id="body">';
  print '<hr id="bar">';
  print '<div class="sidebar" id="left">';
  print '<h2><img src="./header/alerm1mini.png"> アラーム表示</h2>';
  print '<iframe id="mesg" src="Messages.php" width="170" height="60">message</iframe>';
  print '<hr id="bar">';
  print '<h2>▼　表示メニュー</h2>';
  print '<ul>';
        print '<li><a href="MonitorManager.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;監視モニター</span></a></li>';
        print '<li><a href="EventLogPage.php" target="sframe""><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;イベントログ</span></a></li>';
        print '<li><a href="EventMemoPage.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;イベントメモ</span></a></li>';
        print '<li><a href="SnmpStatPage.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;SNMP監視結果</span></a></li>';
        $ismrtg=mrtgck();
        if ($ismrtg=='0'){
          print '<li><a href="GraphListPage.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;リソースグラフ</span></a></li>';
        }else{
          print '<li><a><img src="header/php.jpg" class="pysize"><span class="dmy">&ensp;リソースグラフ</span></a></li>';
        }
        $isplot=plotck();
        if ($isplot=='0'){
          print '<li><a href="GraphListPlotPage.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;プロットグラフ</span></a></li>';
        }else{
          print '<li><a><img src="header/php.jpg" class="pysize"><span class="dmy">&ensp;プロットグラフ</span></a></li>';
        }
  print '</ul>';
  print '<h2>▼　サポートメニュー</h2>';
  print '<ul >';
         print '<li><a href="ReadLogPage.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;監視ログ</a></li>';
         $ismail=mailck();
         if ($ismail=='0'){
           print '<li><a href="PHPMailSend.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;メール設定・送信</a></li>';
         }else{
           print '<li><a><img src="header/php.jpg" class="pysize"><span class="dmy">&ensp;メール設定・送信</a></li>';
         }
         print '<li><a href="WebErrorLog.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;Webエラーログ</a></li>';
         print '<li><a href="PlotLog.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;プロットログ</a></li>';
         print '<li><a href="ManualPageU.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;マニュアル</a></li>';
         print '<li><a href="UserPageU.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;ユーザー管理</a></li>';
  print '</ul>';
print '</div>';

print '</body>';
print '</html>';
}
?>
