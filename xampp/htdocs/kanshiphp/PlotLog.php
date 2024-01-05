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
  print '<link rel="stylesheet" href="kanshi1_py.css">';
  print '</head><body>';
  print '<h2><img src="header/php.jpg" width="30" height="30">&nbsp;&nbsp;▽　プロットトレースログ　▽</h2>';
  $line_num = 40;   /// 表示する行数
  print "<h3>最新 {$line_num} 行</h3>";
  $vpath_apache="";
  $vpatharr=array("vpath_mrtgbase");
  $rtnv=pathget($vpatharr);
  $now=new DateTime();
  $ymd=$now->format("ymd");
  $currplog="plot_".$ymd.".log";
  if(count($rtnv)==1){
    $vpath_mrtgbase=$rtnv[0]; 
    $currpath = $vpath_mrtgbase."\\ubin\\gnuplot\\logs\\".$currplog; 
    if (file_exists($currpath)){
      $contents = file($currpath , FILE_IGNORE_NEW_LINES);
      $start_index = count($contents) - $line_num;
      if ( $start_index < 0) {
        $start_index = 0;
      }
      for ( $i=$start_index; $i<count($contents); $i++ ) {
        print $contents[$i] . '<br />';
      }
      print "^^^^^^^^^^^^^ 最終行 ^^^^^^^^^^^^^";
      print '<form action="PlotLog.php" method="get">';
      print "<input type='hidden' name='user' value={$uid} >";
      print '</form>';    
      print "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
      print '</body></html>';
    }else{
      print "<h4>$currpath</h4>";
      print "<h3>表示すべき上記ファイルがありません、エラーが無いか又はマニュアルを参照して下さい</h3>";
      print "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
      print '</body></html>';
    }
  }else{
    writeloge($pgm,"variable vpath_apache could not get path");
    $rdsql="select * from admintb";
    $rows=getdata($rdsql);
    $sdata=explode(',',$rows[0]);
    $recv=$sdata[3];
    $sender=$sdata[4];
    $subj="Path変数不正";
    $body=$pgm."パス変数 vpath_apache 取得不可";
    mailsendany('other',$sender,$recv,$subj,$body);
    print "&emsp;<h3><font color=red>変数vpath_php取得不可、管理者に通知</font></h3><br>";
    print "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
    print '</body></html>';
  }
}

?>
