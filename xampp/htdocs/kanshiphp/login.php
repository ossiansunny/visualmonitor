<?php
//error_reporting(E_ALL & ~E_NOTICE);
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "hostping.php";
require_once "mailsendany.php";
require_once "alarmwindow.php";
///

date_default_timezone_set('Asia/Tokyo');
$pgm = "login.php";
$user= "";
$brcode="";
$brmsg="";

///
function mailStatSet($_server){
  $pingSw=hostping($_server);
  if ($pingSw==0){
    $mailSvr_sql="update mailserver set status='0'";
    putdata($mailSvr_sql);
    delstatus('Mail Server InActive');
    delstatus('Mail Server Active');
    setstatus('0','Mail Server Active');
    $msg="メールサーバ応答あり、メッセージ欄に「Mail Server Active」設定";
    writelogd($pgm,$msg); 
    return '';   
  }else{
    delstatus('Mail Server InActive');
    delstatus('Mail Server Active');
    setstatus("1","Mail Server InActive");
    $mailSvr_sql='update mailserver set status="1"';
    putdata($mailSvr_sql);
    $msg="メールサーバ応答なし、メッセージ欄に「Mail Server InActive」設定";
    writelogd($pgm,$msg);
    return  "<br>●メールサーバー".$_server."が見つかりません<br>ログイン後メニュー「メール設定・送信」で確認して下さい"; 
  }
}

function checkProcess($_admin){
  $proc_sql='select * from processtb';
  $procArr=getdata($proc_sql);
  $procStr=explode(',',$procArr[0]);
  $coreTime=$procStr[4];  /// 60
  $coreStamp=$procStr[5]; /// 1702453795
  $diff=time() - intval($coreStamp); ///現在時刻からcore起動した時刻の差
  $msg='coretime:'.$coreTime.' corestamp:'.$coreStamp.' diff:'.strval($diff);
  writelogd($pgm,$msg);
  $rtnCd="";
  if ($_admin=='1'){ 
    ///管理者
    if ($diff < intval($coreTime)){ /// 差がcore起動間隔より小さいか
    ///   x             60
      $rtnCd='1'; /// Yes 監視が管理者により実行されている
    }else{
      $rtnCd="0";
    }
  }else{  
    ///一般ユーザ
    if ($diff >= intval($coreTime)){ /// 差がcore起動間隔より大きいか等しいか
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

function deleteStatistics(){
  $stat_sql='delete from statistics';
  putdata($stat_sql);
}

function setStandby(){
  $statis_sql='update statistics set gtype="9"';
  putdata($statis_sql);
}

/////////////////////////////////////////////////

$firstSw=0;  /// 初回=0 param有り=1
$ercde="0"; /// "1":no mailserer
$errSw=0;
/// get admin data
$admin_sql='select * from admintb';
$adminRows=getdata($admin_sql);
$adminArr=explode(',',$adminRows[0]);
$admin_Authority=$adminArr[2];/// 管理者がログイン済であると、authority=1になる
$admin_Toaddr=$adminArr[3];
$admin_Fromaddr=$adminArr[4];
$admin_Subject=$adminArr[5];
$admin_Body=$adminArr[6];
/// メールサーバ死活チェック
//mailStatSet($admin_Fromaddr,$admin_Toaddr,$admin_Subject,$admin_Body);
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
    $user_sql='select * from user where userId="'.$user.'"';
    $userRows=getdata($user_sql);
    //$c=count($userArr);
    if (count($userRows)==0){ 
      /// userなし
      $msg="#2002#".$user."#●入力したユーザー".$user."がありません、<br>ログイン出来るユーザーでログインして下さい";
      writeloge($pgm,$msg);
      branch($pgm,$msg);
    }else{ 
      /// userあり
      foreach ($userRows as $userRow){  
        $userArr=explode(',',$userRow);
        if ($passwd != $userArr[1]){
          /// password一致せず
          $msg="#2002#".$user."#●パスワードが不正です、<br>正しいパスワードでログインして下さい";
          writeloge($pgm,$msg);
          branch($pgm,$msg);
        }else{
          /// password一致
          $msg=$user." Login Successfull";
          writelogd($pgm,$msg);
          $nowDate=date('ymdHis');
          $timeStamp = $nowDate;
          $userId=$userArr[0]; /// userid
          $userAuth=$userArr[2];   /// login userのauthority
          $userName=$userArr[3];  /// username
          $userCode=$userArr[4];  /// usercode
          setSession($user);
          if ($userAuth == '1'){ 
            /// ログインが管理者
            $rtn=checkProcess($userAuth); ///管理者で実行されているかチェック
            if ($rtn=="0"){
              /// まだ管理者で実行されていない->正常              
              $proc_sql='update processtb set admin="'.$user.'",starttime="'.$timeStamp.'"';
              putdata($proc_sql);
              /// 開始イベントログ作成
              $logName = "LOGIN_" . $user; 
              $evtLog_sql = "insert into eventlog (host,eventtime,eventtype,kanrisha,kanrino) values('".$logName."','".$timeStamp."','0','".$user."','')";
              putdata($evtLog_sql); 
              $msg = $logName . " Eventlog Insert sql: " . $insql;
              writelogd($pgm,$msg);  
            
              /// 開始メール送信 
              $mailSubject=$logName;
              $mailBody=$user.' Logged in';
              mailsendany('loginlogout',$admin_Fromaddr,$admin_Toaddr,$mailSubject,$mailBody);
              
              $snmpInitTime=sprintf('%s',time());
              $admin_sql='update admintb set kanriname="'.$userId.'",authority="'.$admin_Authority.'",kanrino="'.$userCode.'",kanripass="'.$snmpInitTime.'"';
              putdata($admin_sql);
              /// statisticsのgtypeを9(スタンバイ)にする
              //setStandby();
              /// 監視を初期状態から始める
              deleteStatistics();
              /// MainIdexphp.php呼び出し
              $nextPage="MainIndexphp.html";
              branch($nextPage,"");
            }else{ /// auth=0
              $msg="#2003#".$user."#●既に管理者監視が実行されています、<br>確認してしばらくしてからログインして下さい";
              branch($pgm,$msg);
            } 
          }else{ 
            /// ログインが一般操作員
            $rtn=checkProcess($userAuth); /// 管理者で実行されているかチェック
            if ($rtn=="1"){ ///管理者で実行している
              /// 開始イベントログ作成
              $logName = "LOGIN_" . $user; 
              $evtLog_sql = "insert into eventlog (host,eventtime,eventtype,kanrisha,kanrino) values('".$logName."','" . $timeStamp . "','0','".$user."','')";
              putdata($evtLog_sql); 
              $msg = $logName . " Eventlog Insert sql: " . $insql;
              writelogd($pgm,$msg);  
              /// 開始メール送信               
              $MailSubject=$logName;
              $mailBody=$user.' Logged in';
              mailsendany('loginlogout',$admin_Fromaddr,$admin_Toaddr,$mailSubject,$mailBody);
              /// MainIndexUphp.php呼び出し
              $nextPage="MainIndexUphp.html";
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
$mailSvr_sql='select * from mailserver';
$mailSvrRows=getdata($mailSvr_sql);
$mailSvrArr=explode(',',$mailSvrRows[0]);
$mailServer=$mailSvrArr[0];
///
$mailErr = mailStatSet($mailServer);
$brmsg = $brmsg . $mailErr;

///
print '<!DOCTYPE html>';
print '<html>';
print '<head>';
print '<meta charset="utf-8">';
print '<title>サンプル</title>';
print '<link rel="stylesheet" href="css/login.css">';
print '</head>';
print '<body>';
print '<div class="login">';
print '<div class="login-triangle"></div>';
print '<h2 class="login-header"><img src="header/php.jpg" width="70" height="70">&emsp;&emsp;監視ログイン</h2>';
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
