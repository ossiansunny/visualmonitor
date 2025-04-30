<?php
require_once "BaseFunction.php";
require_once "varread.php";
require_once "mysqlkanshi.php";
///
$pgm="WebErrorLog.php";
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
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    branch('logout.php',$msg);
  }
  $userArr=explode(',',$userRows[0]);
  $userAuth=$userArr[0];
  $bgColor=$userArr[1];
  print '<html><head>';
  print '<meta http-equiv="Refresh" content="120">';
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';
  print "</head><body class={$bgColor}>";
  print '<h2><img src="header/php.jpg" width="30" height="30">&nbsp;&nbsp;▽　Webエラーログ　▽</h2>';
  $lineNum = 20;   /// 表示する行数
  print "<h3>リフレッシュ 120秒　最新 {$lineNum} 行表示</h3>";
  $vpathParam=array("vpath_weblog");
  $vpathArr=pathget($vpathParam);
  if(count($vpathArr)==1){
    $vpath_weblog=$vpathArr[0];
    $now=new DateTime();
    $ymd=$now->format("Ymd");
    $currErrLog="error_".$ymd.".log";
    $currPath = $vpath_weblog."/".$currErrLog;
    print "<h3>{$currPath}</h3>";
    if (file_exists($currPath)){
      $contents = file($currPath , FILE_IGNORE_NEW_LINES);
      $start_index = count($contents) - $lineNum;
      if ( $start_index < 0) {
        $start_index = 0;
      }
      for ( $i=$start_index; $i<count($contents); $i++ ) {
        $utf8_contents=mb_convert_encoding($contents[$i],"utf-8","sjis-win");
        //print '<font color=white>'.$contents[$i] . '</font><br />';  ///////// windows only???
        print '<font color=white>'.$utf8_contents . '</font><br />';
      }
      print "^^^^^^^^^^^^^ 最終行 ^^^^^^^^^^^^^";
      print '<form action="WebErrorLog.php" method="get">';
      print "<input type='hidden' name='user' value={$uid} >";      
      print '</form>';    
      print "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
      print '</body></html>';
    }else{
      print "<h4>$currpath</h4>";
      print "<h3>表示すべき上記ファイルがありません、エラーが無いか又はマニュアルを参照して下さい</h3>";
      
    }
    
  }else{
    print "<h3>kanshiphp.iniにvpath_weblogがありません</h3>";
    print "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
  }
  print '</body></html>';
}
///
?>
