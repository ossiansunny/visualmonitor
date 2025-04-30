<?php
require_once "BaseFunction.php";
require_once "varread.php";
require_once 'alarmwindow.php';
require_once 'hostncat.php';
require_once 'mailsend.php';
require_once 'mysqlkanshi.php';

$interval=60;
$pgm='Discover.php';
$user="";
$brcode="";
$brmsg="";

///
if(!isset($_GET['param'])){

  print '<html>';
  print "<body bgcolor=khaki>";
  print '<h4><font color=gray>お待ち下さい....</font></h4>';
  print "</body></html>";

  paramGet($pgm);
}else{
  paramSet();
  //echo 'usaer:'.$user.PHP_EOL;
  $user_sql='select bgcolor from user where userid="'.$user.'"';
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    branch('logout.php',$msg);
  }
  $bgcolor=$userRows[0];
  $adminSql="select logout,loginstamp from admintb";
  $adminRows=getdata($adminSql);
  $adminArr=explode(',',$adminRows[0]);
  $adminLogout=$adminArr[0];
  $adminLoginTime=$adminArr[1];
  print '<html lang="ja">';
  print '<head>';
  if($adminLogout=='0'){
    print "<meta http-equiv='refresh' content={$interval}>";
  }
  print '<link rel="stylesheet" href="css/CoreMenu.css">';
  print '</head>';
  print "<body class={$bgcolor}>";
  print '<div ><table><tr><td >';
  print "<h5><font color=white>メールサーバ・ログ監視 {$interval}秒</font></h5>";
  print '</td></tr></table></div>';
  ///
  $now=new DateTime();
  $ymd=$now->format("ymd");
  /// weblog
  $vpath_weblog="";
  $rtnPath=pathget(array("vpath_weblog"));
  if(count($rtnPath)==1){
    /// vpath_weblogがある場合
    $vpath_weblog=$rtnPath[0];
    $webLogLists=glob($vpath_weblog.'/error_*.log');
    delstatus('Web Log Remain');
    foreach($webLogLists as $webLogFilePath){        
      $webLogFileName=basename($webLogFilePath);
      //echo $webLogFileName.PHP_EOL;
      if (false === strpos($webLogFileName,$ymd)){
        setstatus('1','Web Log Remain'); 
        break;
      }
    }  
  }
  /// kanshi_log
  $vpath_kanshi="";
  $rtnPath=pathget(array("vpath_kanshiphp"));
  if(count($rtnPath)==1){
    $vpath_kanshi=$rtnPath[0];
    $kanshiLogLists=glob($vpath_kanshi.'/logs/kanshi_*.log');
    delstatus('Kanshi Log Remain');
    foreach($kanshiLogLists as $kanshiFileNamePath){        
      $kanshiFileName=basename($kanshiFileNamePath);
      if (false === strpos($kanshiFileName,$ymd)){
        setstatus('1','Kanshi Log Remain'); 
        break;
      }
    }
  }
  /// plot_log 
  $vpath_plot="";
  $rtnPath=pathget(array("vpath_plothome"));
  if(count($rtnPath)==1){
    $vpath_plot=$rtnPath[0];
    $plotLogLists=glob($vpath_plot.'/logs/plot_*.log');
    delstatus('Plot Log Remain');
    foreach($plotLogLists as $plotFileNamePath){        
      $plotFileName=basename($plotFileNamePath);
      if (false === strpos($plotFileName,$ymd)){
        setstatus('1','Plot Log Remain'); 
        break;
      }
    }
  }

 
  /// mailserver active check
  $rtnPath=pathget(array("vpath_phpmailer"));
  if(count($rtnPath)==1){
    $mailSvr_sql='select server,port from mailserver';
    $mailRows=getdata($mailSvr_sql);
    $mailArr=explode(',',$mailRows[0]);
    $server=$mailArr[0];
    $port=$mailArr[1];
    if ($server != '127.0.0.1'){
      $rtnCde=hostncat($server,$port);
      if ($rtnCde==0){
        //echo 'Return hostncat port='.$port.' code = 0<br>'.PHP_EOL;
        delstatus('Mail Server InActive');
        delstatus('Mail Server Active');
        setstatus('0','Mail Server Active');
        ///-------------------------------------------------------------------------
        /// ログイン開始メールはlogin.php時にはメールサーバの動作が確認できないため
        /// メールサーバの動作をチェックするDiscover.php内で行う
        /// ログイン時にadmintbのloginstamp欄にログイン時刻を書き込み、Discoverで
        /// 動作確認した後、この時刻を記したメールを送信、その後loginstamp欄を
        /// '000000000000'にする、そのためメールはログイン時の後１通だけ送信される
        /// -------------------------------------------------------------------------
        $mailSql="update mailserver set status='0'";
        putdata($mailSql);
        /// send login mail
        //$adminSql="select loginstamp from admintb";
        //$adminRows=getdata($adminSql);
        if($adminLoginTime!='000000000000'){
          //echo 'select user '.$user.'<br>'.PHP_EOL;
          $userSql="select username from user where userid='".$user."'";
          $userRows=getdata($userSql);
          //echo 'username:'.$userRows[0].', user:'.$user.'<br>'.PHP_EOL;
          $rtnCd=mailsend('',$user,'6',$userRows[0],$user);
          //echo 'return mailsend:'.$rtnCd.'<br>'.PHP_EOL;
          if($rtnCde==0){
            //echo 'select user return = 0'.'<br>'.PHP_EL;
            $adminSql="update admintb set loginstamp='000000000000'";
            putdata($adminSql);
          }
        }  
      }else{
        /// return code 1
        //echo 'Return code =1'.'<br>'.PHP_EOL;
        delstatus('Mail Server Active');
        delstatus('Mail Server InActive');
        setstatus('1','Mail Server InActive');
        $mailSql="update mailserver set status='1'";
        putdata($mailSql);
      }
    }else{
      /// 127.0.0.1 
      //echo '127.0.0.1 host'.PHP_EOL;
      delstatus('Mail Server Active');
      delstatus('Mail Server InActive');
      setstatus('1','Mail Server InActive');
      $mailSql="update mailserver set status='1'";
      putdata($mailSql);
    }
  }else{
    /// vpath error
    //echo 'vpath error'.PHP_EOL;
    delstatus('Mail Server Active');
    delstatus('Mail Server InActive');
    setstatus('1','Mail Server InActive');
    $mailSql="update mailserver set status='1'";
    putdata($mailSql);   
  }
  
  print '</body></html>';
}
?>  
