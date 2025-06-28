<?php
///////////////////////////////////////////////////////////////
///
/// /// 変更履歴 ///　最終更新日 2025/4/21	
/// 2025/4   /usr/bin/mrtgrun /usr/bin/plotgraph パス指定へ変更
/// 2025/6   parameter changes when calling plotgraph.sh and mrtgrun.sh
///
//////////////////////////////////////////////////////////////
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";
///

$user="";
$brcode="";
$brmsg="";
$pgm="MrtgAutoRun.php";

if(!isset($_GET['param'])){
  
  print '<html>';
  print "<body bgcolor=khaki>";
  print '<h4><font color=gray>お待ち下さい....</font></h4>';
  print "</body></html>";
  paramGet($pgm);
}else{
  paramSet();
  
  $user_sql='select bgcolor from user where userid="'.$user.'"';
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    branch('logout.php',$msg);
 }
  $bgcolor=$userRows[0];
  ///
  $admin_sql='select monintval,debug,logout from admintb';
  $adminRows=getdata($admin_sql);
  $adminArr=explode(',',$adminRows[0]);
  $monitorInterval=$adminArr[0];
  $debug=$adminArr[1];
  $logout=$adminArr[2];
  print '<html lang="ja">';
  print '<head>';
  if ($logout=='0'){
    print "<meta http-equiv='refresh' content={$monitorInterval}>";
  }
  print '<link rel="stylesheet" href="css/CoreMenu.css">';
  print '</head>';
  print "<body class={$bgcolor}>";
  print '<div><table><tr><td>';  
  ///
  $core_sql='select mrtg from coretimetb';
  $coreRows=getdata($core_sql);
  $mrtgTimeStamp=$coreRows[0];
  $currentTimeStamp=date('ymdHis');
  $diffTime=intval($currentTimeStamp) - intval($mrtgTimeStamp);
  if ($diffTime > intval($monitorInterval)*3){
    print "<h5><font color=white>MRTG Refresh {$monitorInterval}sec</font></h5>";
    print '</td></tr></table></div>';
    date_default_timezone_set('Asia/Tokyo');
    if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
      /// windows xampp
      $vpathParam=array("vpath_mrtg","vpath_mrtghome","vpath_plothome","vpath_base","vpath_perl","vpath_ubin");
      $rtnPath=pathget($vpathParam);
      if(count($rtnPath)==6){
        $vpath_mrtg=$rtnPath[0];
        $vpath_mrtghome=$rtnPath[1];
        $vpath_plothome=$rtnPath[2];
        $vpath_base=$rtnPath[3];
        $vpath_perl=$rtnPath[4];
        $vpath_ubin=$rtnPath[5];
        /// mrtg
        $cmdMrtgRun=$vpath_perl." ".$vpath_mrtg." ".$vpath_mrtghome."/newmrtg.cfg";
        $mrtgRtn = shell_exec($cmdMrtgRun);
        writelogd($pgm,"call ".$cmdMrtgRun);
        /// plot
        $cmdPlotGraph=$vpath_ubin.'/plotgraph.exe '.$vpath_base. ' '. $debug;  
        $plotRtn = shell_exec($cmdPlotGraph);
        writeloge($pgm,"call ".$cmdPlotGraph);
        ///
      }else{
        $msg="Invalid path , Check kanshiphp.ini";
        writelogd($pgm,$msg);
        print "<h4>{$msg}</h4>";
      }
    }else{
      /// Linux and MacOSX
      $vpathParam=array("vpath_ubin","vpath_mrtg","vpath_gnuplot","vpath_mrtghome","vpath_plothome");
      $rtnPath=pathget($vpathParam);
      if(count($rtnPath)==5){
        $vpath_ubin=$rtnPath[0];
        $vpath_mrtg=$rtnPath[1];
        $vpath_gnuplot=$rtnPath[2];
        $vpath_mrtghome=$rtnPath[3];
        $vpath_plothome=$rtnPath[4];
        /// mrtg 2025/6/16 $vpath_mrtg 追加
        $cmdMrtgRun=$vpath_ubin.'/mrtgrun.sh '.$vpath_ubin.' '.$vpath_mrtg.' '.$vpath_mrtghome.' '.$vpath_plothome.' '.$debug;
        $mrtgRtn = shell_exec($cmdMrtgRun);
        writelogd($pgm,'shell_exec '.$cmdMrtgRun);
        /// plot 2025/6/16 $vpath_gnuplot 追加
        $cmdPlotGraph=$vpath_ubin.'/plotgraph.sh '.$vpath_ubin.' '.$vpath_mrtghome.' '.$vpath_plothome.' '.$vpath_gnuplot.' '.$vpath_mrtg.' '.$debug; 
        $plotRtn = shell_exec($cmdPlotGraph);
        writeloge($pgm,'shell_exec '.$cmdPlotGraph);
      }else{
        $msg="Invalid path , Check kanshiphp.ini";
        writelogd($pgm,$msg);
        print "<h4>{$msg}</h4>";
      }
    }
  }else{
    print "<h4>MRTG Daemon Running</h4>";
    print '</td></tr></table></div>';
  }
  print "</body></html>";
}
?>
