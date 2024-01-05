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
/*
if(isset($_GET['remove'])){
  $user = $_GET['user'];
  $dellogs=$_GET['dellog'];
  $delarr = explode(',',$dellogs);
  $vpath_apache="";
  $vpatharr=array("vpath_apache");
  $rtnv=pathget($vpatharr);
  $vpath_apache=$rtnv[0];  
  foreach($delarr as $delrec){
    unlink($vpath_apache.'\\logs\\'.$delrec);
  }  
  $nextpage="MonitorManager.php";
  branch($nextpage,$user);
}elseif(!isset($_GET['param'])){
*/
if(!isset($_GET['param'])){
  paramGet($pgm);
  ///
}else{
  paramSet();
  ///
  $usql='select authority from user where userid="'.$user.'"';
  $rows=getdata($usql);
  $udata=explode(',',$rows[0]);
  $auth=$udata[0];
  print '<html><head><meta>';
  print '<link rel="stylesheet" href="kanshi1_py.css">';
  print '</head><body>';
  print '<h2><img src="header/php.jpg" width="30" height="30">&nbsp;&nbsp;▽　Webエラーログ　▽</h2>';
  $line_num = 20;   /// 表示する行数
  print "<h3>最新 {$line_num} 行</h3>";
  $vpath_apache="";
  $vpatharr=array("vpath_apache");
  $rtnv=pathget($vpatharr);
  $now=new DateTime();
  $ymd=$now->format("Ymd");
  $currelog="error_".$ymd.".log";
  $curralog="access_".$ymd.".log";
  if(count($rtnv)==1){
    $vpath_apache=$rtnv[0]; 
    $currpath = $vpath_apache."\\logs\\".$currelog; 
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
      print '<form action="WebErrorLog.php" method="get">';
      print "<input type='hidden' name='user' value={$uid} >";
      //print '<input class=button type="submit" name="end" value="表示終了" />';
      if ($auth=='1'){      
        /*
        $result=glob($vpath_apache.'\logs\*_*.log');
        $dellog="";
        foreach($result as $rec){        
          $filename=basename($rec);
          if (!($filename==$currelog || $filename==$curralog)){
            $erac=explode('_',$filename);
            if ($erac[0]=='error' || $erac[0]=='access'){
              $dellog=$dellog.$filename.',';
            }
          }
        }
        $dellog=rtrim($dellog,",");
        if ($dellog!=""){
          print "<hr><h3>削除出来るログ</h3><table><tr>";
          $dellogarr=explode(',',$dellog);
          foreach($dellogarr as $arrrec){
            print "<td>{$arrrec}&emsp;</td>";
          }
          print '</tr></table>';
          print "<input type='hidden' name='dellog' value={$dellog} />";
          print "<input type=hidden name=user value={$user}>";
          print '&nbsp;&nbsp;<input class=buttondel type="submit" name="remove" value="上記ログ削除" />';
        }
        */
      }
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
