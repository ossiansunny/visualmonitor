<?php
require_once "BaseFunction.php";
require_once "varread.php";
require_once "mysqlkanshi.php";
///
$pgm="PlotLog.php";
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
  print '<html><head><meta>';
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';
  print '</head><body>';
  print '<h2><img src="header/php.jpg" width="30" height="30">&nbsp;&nbsp;▽　プロットトレースログ　▽</h2>';
  $lineNum = 40;   /// 表示する行数
  print "<h3>最新 {$lineNum} 行</h3>";
  $vpath_plothome="";
  $vpathParam=array("vpath_plothome");
  $rtnPath=pathget($vpathParam);
  $now=new DateTime();
  $ymd=$now->format("ymd");
  $plotLogName="plot_".$ymd.".log";
  if(count($rtnPath)==1){
    $vpath_plothome=$rtnPath[0];
    if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
      /// Windows xampp
      $currLogPath = $vpath_plothome."\\logs\\".$plotLogName; 
    }else{
      /// Linux
      $currLogPath = $vpath_plothome."/logs/".$plotLogName;
   }
   if (file_exists($currLogPath)){
      $logRecArr = file($currLogPath , FILE_IGNORE_NEW_LINES);
      $start_index = count($logRecArr) - $lineNum;
      if ( $start_index < 0) {
        $start_index = 0;
      }
      for ( $i=$start_index; $i<count($logRecArr); $i++ ) {
        print $logRecArr[$i] . '<br />';
      }
      print "^^^^^^^^^^^^^ 最終行 ^^^^^^^^^^^^^";
      print '<form action="PlotLog.php" method="get">';
      print "<input type='hidden' name='user' value={$uid} >";
      print '</form>';    
      print "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
      print '</body></html>';
    }else{
      print "<h4>$currLogPath</h4>";
      print "<h3>表示すべき上記ファイルがありません、エラーが無いか又はマニュアルを参照して下さい</h3>";
      print "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
      print '</body></html>';
    }
  }else{
    writeloge($pgm,"variable vpath_plothome could not get path");
    $admin_sql="select * from admintb";
    $adminRows=getdata($admin_sql);
    $adminArr=explode(',',$adminRows[0]);
    $mailToAddr=$adminArr[3];
    $mailFromAddr=$adminArr[4];
    $subject="Path変数不正";
    $body=$pgm."パス変数 vpath_plothome 取得不可";
    mailsendany('other',$mailFromAddr,$mailToAddr,$subject,$body);
    print "&emsp;<h3><font color=red>変数vpath_php取得不可、管理者に通知</font></h3><br>";
    print "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
    print '</body></html>';
  }
}

?>
