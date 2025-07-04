﻿<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
///

date_default_timezone_set('Asia/Tokyo');
$pgm = "login.php";
$user= "";
$brcode="";
$brmsg="";
$admin_snmpintval="";

///

function checkProcess($_admin){
  global $pgm;
  $proc_sql='select * from processtb';
  $procArr=getdata($proc_sql);
  $procStr=explode(',',$procArr[0]);
  $coreTime=$procStr[4];  /// 60 60
  $coreStamp=$procStr[5]; /// 1702453795 1647169256 
  $diff=time() - intval($coreStamp); ///現在時刻からcore起動した時刻の差
  $msg='coretime:'.$coreTime.' corestamp:'.$coreStamp.' diff:'.strval($diff).' auth:'.$_admin;
  writelogd($pgm,$msg);
  $rtnCd="";
  if ($_admin=='1'){ 
    ///管理者
    if (intval($diff) < intval($coreTime)*2){ /// 差がcoreitimex2より小さいか
    ///   x             60
      $rtnCd='1'; /// Yes 監視が管理者により実行されている
    }else{
      $rtnCd="0";
    }
  }else{  
    ///一般ユーザ
    if (intval($diff) >= intval($coreTime)){ /// 差がcore起動間隔より大きいか等しいか
      $rtnCd='0'; /// Yes　監視が管理者より実行されていない
    }else{
      $rtnCd="1";      
    }
  }
  return $rtnCd;
}

function setSession($_sessvalue){
  print '<script type="text/javascript">';
  ///print "sessionStorage.setItem('user',{$value});";  この使い方はエラー
  print 'sessionStorage.setItem("user","'.$_sessvalue.'");';
  print '</script>';
}

/////////////////////////////////////////////////

$firstSw=0;  /// 初回=0 param有り=1
$ercde="0"; /// "1":no mailserer
$errSw=0;
/// get admin data
$admin_sql='select authority,receiver,sender,subject,snmpintval from admintb';
$adminRows=getdata($admin_sql);
$adminArr=explode(',',$adminRows[0]);
$admin_Authority=$adminArr[0];/// 管理者がログイン済であると、authority=1になる
$admin_Toaddr=$adminArr[1];
$admin_Fromaddr=$adminArr[2];
$admin_Subject=$adminArr[3];
$admin_snmpintval=$adminArr[4];

/// 
if (isset($_GET['param'])){   /// branchで戻った時の処理
  paramSet();
  $firstSw=1;
  $errSw=1; 
}else{
  /// login ボタン押した時の処理
  if (isset($_GET['login'])){  
    /// login処理
    $passwd=$_GET['passwd'];
    $user=$_GET['user'];
    $ercde=$_GET['brcode'];
    $userCode="";
    $userAuth="";
    $userName="";
    $user_sql='select userid,password,authority,username,usercode,timestamp,bgcolor,audio from user where userid="'.$user.'"';
    $userRows=getdata($user_sql);
    if(empty($userRows)){
      $msg="#2002#".$user."#●入力したユーザー".$user."がありません、<br>ログイン出来るユーザーでログインして下さい";
      writelogd($pgm,$msg);
      //branch($pgm,$msg);
    }else{ 
      /// userあり
      foreach ($userRows as $userRow){  
        $userArr=explode(',',$userRow);
        if ($passwd != $userArr[1]){
          /// password一致せず
          $msg="#2002#".$user."#●パスワードが不正です、<br>正しいパスワードでログインして下さい";
          writelogd($pgm,$msg);
          branch($pgm,$msg);
        }else{
          /// password一致
          $msg=$user." Login Success";
          writelogd($pgm,$msg);
          $nowDate=date('ymdHis');
          $timeStamp = $nowDate;
          $userId=$userArr[0]; /// userid
          $userAuth=$userArr[2];   /// login userのauthority
          $userName=$userArr[3];  /// username
          $userCode=$userArr[4];  /// usercode
          //setSession($user);
          if ($userAuth == '1'){ 
            /// ログインが管理者
            $rtn=checkProcess($userAuth); ///管理者で実行されているかチェック
            if ($rtn=="0"){
              /// まだ管理者で実行されていない->正常  
              setSession($user);            
              $proc_sql='update processtb set admin="'.$user.'",starttime="'.$timeStamp.'"';
              putdata($proc_sql);
              /// 開始イベントログ作成
              $logName = "LOGIN_" . $user; 
              $evtLog_sql = "insert into eventlog (host,eventtime,eventtype,snmpvalue,kanrisha,kanrino) values('ログイン','".$timeStamp."','0',' ','".$user."','')";
              putdata($evtLog_sql); 
              $msg = $logName . " Eventlog Insert sql: " . $evtLog_sql;
              writelogd($pgm,$msg);  
            
              /// 開始メール送信 ログインメールはメールサーバのチェックをするDiscover.phpが送信
              /// Discover.phpの送信条件はloginstamp欄に0以外の時刻があるとき、Discover.phpで0にする
              /// logoutはlogout.phpで'1'、HeaderPage.phpがリフレッシュさせるため、login.phpで'0'にする
              $snmpInitTime=sprintf('%s',time());
              $admin_sql='update admintb set kanriname="'.$userId.'",
		authority="'.$admin_Authority.'",
		kanrino="'.$userCode.'",
		kanripass="'.$snmpInitTime.'",
		logout="0",
		loginstamp="'.$nowDate.'"';
              putdata($admin_sql);
              /*
              ///--------------------------------------------- 
              /// statisticsのgtypeを9(スタンバイ)にするなど
              setStandby();
              
              */
              ///---------------------------------------------
              /// MainIindexphp呼び出し
              $nextPage="MainIndex.php";
              branch($nextPage,"");
              ///
            }else{ /// auth=0
              $msg="#2003#".$user."#●既に管理者監視が実行されています、<br>確認してしばらくしてからログインして下さい";
              branch($pgm,$msg);
            } 
          }else{ 
            /// ログインが一般操作員
            $rtn=checkProcess($userAuth); /// 管理者で実行されているかチェック
            if ($rtn=="1"){ ///管理者で実行している
              setSession($user);
              /// 開始イベントログ作成
              $logName = "LOGIN_" . $user; 
              $evtLog_sql = "insert into eventlog (host,eventtime,eventtype,kanrisha,kanrino) values('ログイン','" . $timeStamp . "','0','".$user."','')";
              putdata($evtLog_sql); 
              $msg = $logName . " Eventlog Insert sql: " . $insql;
              writelogd($pgm,$msg);  
              /// 開始メール送信               
              //mailsend($hostArr,$user,'6','一般ユーザーログイン',$user,'','');
              /// MainIndexUphp.php呼び出し
              $nextPage="MainIndexU.html";
              branch($nextPage,"");
            }else{  /// $rtn=="0" or $admin_Authority="0"
              $msg="#2004#".$user."#●管理者監視が実行されていません、<br>しばらくしてからログインするか、管理者監視を確認して下さい";
              branch($pgm,$msg);
            }
          }          
          print '</body></html>';
        }  
      }
    }
  }
  $firstSw=1;
}
/// 最初の処理
///

$random=rand(1,5);
$bodyColor='bgimage'.strval($random);
print '<!DOCTYPE html>';
print '<html>';
print '<head>';
print '<meta charset="utf-8">';
print '<title>監視アプリ</title>';
print '<link rel="stylesheet" href="css/login.css">';
print '</head>';
print "<body class={$bodyColor}>";
print '<div class="login">';
print '<h2 class="login-header"><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;監視ログイン</h2>';
print '<form class="login-container" type="get" action="login.php">';
print '<p><input type="text" name="user" value="" placeholder="ユーザID" required></p>';
print '<p><input type="password" name="passwd" placeholder="パスワード" required></p>';
print "<input type='hidden' name='brcode' value={$ercde}>";
print '<p><input type="submit" name="login" value="ログイン"></p>';
print '</form>';
print '</div>';
print '<div class="login">';
///
if ($errSw == 1){ /// loginエラー
  print "<div><h4><font color=red>{$brmsg}</font></h4></div>";
}else if ($errSw == 2){ /// mailserver エラー
  print "<div><h4><font color=yellow>{$brmsg}</font></h4></div>";
}else {
  print "<div><h4><font color=white>{$brmsg}</font></h4></div>";
}
print '</div>';
print '</body>';
print '</html>';

?>
