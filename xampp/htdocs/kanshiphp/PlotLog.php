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
  $user_sql="select authority,bgcolor from user where userid='".$user."'";
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    branch('logout.php',$msg);
  }
  $userArr=explode(',',$userRows[0]);
  $authority=$userArr[0];
  $bgColor=$userArr[1];
  ///
  print '<html><head><meta>';
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';
  print "</head><body class={$bgColor}>";
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
    $currLogPath = $vpath_plothome."/logs/".$plotLogName;
    print "<h3>{$currLogPath}</h3>";
    if (file_exists($currLogPath)){
      $logRecArr = file($currLogPath , FILE_IGNORE_NEW_LINES);
      $start_index = count($logRecArr) - $lineNum;
      if ( $start_index < 0) {
        $start_index = 0;
      }
      for ( $i=$start_index; $i<count($logRecArr); $i++ ) {
        $utf8_contents=mb_convert_encoding($logRecArr[$i],"utf-8","sjis-win");
        print $utf8_contents . '<br />';
        //print $logRecArr[$i] . '<br />';
      }
      print "^^^^^^^^^^^^^ 最終行 ^^^^^^^^^^^^^";
      print '<form action="PlotLog.php" method="get">';
      print "<input type='hidden' name='user' value={$uid} >";
      print '</form>';    
      print "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
      print '</body></html>';
    }else{
      print "<h3>表示すべき上記ファイルがありません、エラーが無いか又はマニュアルを参照して下さい</h3>";
      print "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
      print '</body></html>';
    }
  }else{
    writeloge($pgm,"variable vpath_plothome could not get path");
    $subject="Path変数不正";
    $msg="パス変数 vpath_mrgbase 取得不可";
    mailsend('PlotLog.php',$user,'5',$body,'','','');
    print "&emsp;<h3><font color=red>変数vpath_php取得不可、管理者に通知</font></h3><br>";
    print "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
    print '</body></html>';
  }
}

?>
