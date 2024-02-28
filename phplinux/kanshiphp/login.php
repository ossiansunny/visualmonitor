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
$user="";
$brcode="";
$brmsg="";
///
function mailstatset($server,$port,$from,$to,$subj,$body){
  $pingsw=hostping($server);
  if ($pingsw==0){
    $upsql="update mailserver set server='".$server."', port=".$port;
    putdata($upsql);
    $upsql="update admintb set sender='".$from."',receiver='".$to."',subject='".$subj."',body='".$body."'";
    putdata($upsql);
    delstatus('Mail Server InActive');
    delstatus('Mail Server Active');
    setstatus('0','Mail Server Active');
    $msg="メールサーバ応答あり、メッセージ欄に「Mail Server Active」設定";
    writelogd($pgm,$msg);    
  }else{
    delstatus('Mail Server InActive');
    delstatus('Mail Server Active');
    setstatus("1","Mail Server InActive");
    $sql='update mailserver set status="1"';
    putdata($sql);
    $msg="メールサーバ応答なし、メッセージ欄に「Mail Server InActive」設定";
    writelogd($pgm,$msg);    
  }
}

function checkproc($kanri){
  $psql='select * from processtb';
  $rows=getdata($psql);
  $pdata=explode(',',$rows[0]);
  $coretime=$pdata[4];  /// 60
  $corestamp=$pdata[5]; /// 1702453795
  $snmptime=$pdata[6];
  $snmpstamp=$pdata[7];
  $mrtgtime=$pdata[8];
  $mrtgstamp=$pdata[9];
  $diff=time() - intval($corestamp); ///現在時刻からcore起動した時刻の差
  $msg='coretime:'.$coretime.' corestamp:'.$corestamp.' diff:'.strval($diff);
  writelogd($pgm,$msg);
  $rtncd="";
  if ($kanri=='1'){ 
    ///管理者
    if ($diff < intval($coretime)){ /// 差がcore起動間隔より小さいか
    ///   x             60
      $rtncd='1'; /// Yes 監視が管理者により実行されている
    }else{
      $rtncd="0";
    }
  }else{  
    ///一般ユーザ
    if ($diff >= intval($coretime)){ /// 差がcore起動間隔より大きいか等しいか
      $rtncd='0'; /// Yes　監視が管理者より実行されていない
    }else{
      $rtncd="1";
    }
  }
  return $rtncd;
}

function setsess($value){
  print '<script type="text/javascript">';
  ///print "sessionStorage.setItem('user',{$value});";  この使い方はエラー
  print 'sessionStorage.setItem("user","'.$value.'");';
  print '</script>';
}
/////////////////////////////////////////////////

$fsw=0;  /// 初回=0 param有り=1
$ercde="0"; /// "1":no mailserer
$esw=0;

$mailserver="";
$mailport=587;
/// get admin data
$adminsql='select * from admintb';
$arows=getdata($adminsql);
$adata=explode(',',$arows[0]);
$reset=$adata[0];    /// AdminPageで初期化すると'reset'、デフォルトはNULL
$authority=$adata[2];/// 管理者がログイン済であると、authority=1になる
$toaddr=$adata[3];
$fromaddr=$adata[4];
$subject=$adata[5];
$body=$adata[6];
if (isset($_GET['param'])){   /// branchで戻った時の処理
  paramSet();
  $fsw=1;
  $esw=1; 
}else{
  /// login ボタン押した時の処理
  if (isset($_GET['login'])){  
    /// 初期化処理（現在使用していない）
    //if (isset($_GET['init'])){ 
    //  if ($_GET['init']=="on"){
    //    print "Initialize";
    //    $upsql='update admintb set kanriname=null';
    //    putdata($upsql);
    //    writeloge($pgm,"----- VisualMonitor has Initialized -----");
    //    $sub = "Initialize " . $user;
    //    $message=$user.' Initialized';
    //    mailsendany('loginlogout',$fromaddr,$toaddr,$sub,$message);
    //  }
    //}
    /// login処理
    $passwd=$_GET['passwd'];
    $user=$_GET['user'];
    $ercde=$_GET['brcode'];
    $ucode="";
    $uauth="";
    $uname="";
    $selsql='select * from user where userid="'.$user.'"';
    $udata=getdata($selsql);
    $c=count($udata);
    if ($c==0){ 
      /// userなし
      $msg="#2002#".$user."#●入力したユーザー".$user."がありません、<br>ログイン出来るユーザーでログインして下さい";
      writeloge($pgm,$msg);
      branch($pgm,$msg);
    }else{ 
      /// userあり
      foreach ($udata as $urec){  
        $sdata=explode(',',$urec);
        if ($passwd != $sdata[1]){
          /// password一致せず
          $msg="#2002#".$user."#●パスワードが不正です、<br>正しいパスワードでログインして下さい";
          writeloge($pgm,$msg);
          branch($pgm,$msg);
        }else{
          /// password一致
          $msg=$user." Login Successfull";
          writelogd($pgm,$msg);
          $userid=$sdata[0]; /// userid
          $uauth=$sdata[2];   /// login userのauthority
          $uname=$sdata[3];  /// username
          $ucode=$sdata[4];  /// usercode
          setsess($user);
          if ($uauth == '1'){ 
            /// ログインが管理者
            $rtn=checkproc($uauth); ///管理者で実行されているかチェック
            if ($rtn=="0"){
              /// まだ管理者で実行されていない
              $now=date('ymdHis');
              $tstamp = $now;
              $upsql='update processtb set admin="'.$user.'",starttime="'.$tstamp.'"';
              putdata($upsql);
              /// 開始イベントログ作成
              $logname = "LOGIN_" . $user; 
              $insql = "insert into eventlog (host,eventtime,eventtype,kanrisha,kanrino) values('".$logname."','".$tstamp."','0','".$userid."','')";
              putdata($insql); 
              $msg = $logname . " Eventlog Insert sql: " . $insql;
              writelogd($pgm,$msg);              
              /// 開始メール送信 
              $sub=$logname;
              $message=$user.' Logged in';
              mailsendany('loginlogout',$fromaddr,$toaddr,'LOGIN',$message);
              /// メールサーバ死活チェック
              mailstatset($server,$port,$fromaddr,$toaddr,$subject,$body);
              $upsql='update admintb set kanriname="'.$userid.'",authority="'.$uauth.'",kanrino="'.$ucode.'"';
              putdata($upsql);
              /// MainIdexphp.php呼び出し
              $nextpage="MainIndexphp.html";
              branch($nextpage,"");
            }else{ /// auth=0
              $msg="#2003#".$user."#●既に管理者監視が実行されています、<br>確認してしばらくしてからログインして下さい";
              branch($pgm,$msg);
            } 
          }else{ 
            /// ログインが一般操作員
            $rtn=checkproc($uauth); /// 管理者で実行されているかチェック
            if ($rtn=="1"){ ///管理者で実行している
              /// 開始イベントログ作成
              $now=date('ymdHis');
              $tstamp = $now;
              $logname = "LOGIN_" . $user; 
              $insql = "insert into eventlog (host,eventtime,eventtype,kanrisha,kanrino) values('".$logname."','" . $tstamp . "','0','".$userid."','')";
              putdata($insql); 
              $msg = $logname . " Eventlog Insert sql: " . $insql;
              writelogd($pgm,$msg);  
              /// 開始メール送信               
              $sub=$logname;
              $message=$user.' Logged in';
              mailsendany('loginlogout',$fromaddr,$toaddr,$sub,$message);
              /// MainIndexUphp.php呼び出し
              $nextpage="MainIndexUphp.html";
              branch($nextpage,"");
            }else{  /// $rtn=="0" or $authority="0"
              $msg="#2004#".$user."#●管理者監視が実行されていません、<br>しばらくしてからログインするか、管理者監視を確認して下さい";
              branch($pgm,$msg);
            }
          }          
          print '</body></html>';
        }  
      }
    }
  }
  $fsw=1;
}
/// 最初の処理
$mlsvrsql='select * from mailserver';
$mrows=getdata($mlsvrsql);
$mdata=explode(',',$mrows[0]);
$mailserver=$mdata[0];
$mailport=$mdata[1];
///
$pingsw=hostping($mailserver);
$sql='';
if ($pingsw != 0) {
  $brmsg=$brmsg."<br>●メールサーバー".$mdata[0]."が見つかりません<br>ログイン後メニュー「メール設定・送信」で確認して下さい";
  $esw=2;
  $ercde="2";
  delstatus("Mail Server InActive");
  delstatus("Mail Server Active");
  setstatus("1","Mail Server InActive");
  $sql='update mailserver set status="1"';
  putdata($sql);
} else {
  delstatus("Mail Server InActive");
  delstatus("Mail Server Active");
  setstatus("0","Mail Server Active");
  $sql='update mailserver set status="0"';
  putdata($sql);
}
///
print '<!DOCTYPE html>';
print '<html>';
print '<head>';
print '<meta charset="utf-8">';
print '<title>サンプル</title>';
print '<link rel="stylesheet" href="login.css">';
print '</head>';
print '<body>';
print '<div class="login">';
print '<div class="login-triangle"></div>';
print '<h2 class="login-header"><img src="header/php.jpg" width="70" height="70">&emsp;&emsp;監視ログイン</h2>';
print '<form class="login-container" type="get" action="login.php">';
print '<p><input type="text" name="user" value="" placeholder="ユーザID" required></p>';
print '<p><input type="password" name="passwd" placeholder="パスワード" required></p>';
/// $resetはadmintbのkanriname、AdminPageで初期化すると'reset'になる 
if ($reset=='reset'){
  print '<table><tr><td>&emsp;&emsp;初期化オプション</td><td><input type="checkbox" name="init"></td></tr></table>';
}
print "<input type='hidden' name='brcode' value={$ercde}>";
print '<p><input type="submit" name="login" value="ログイン"></p>';
print '</form>';
print '</div>';
print '<div class="login">';
///
if ($esw == 1){ /// loginエラー
  print "<div><h4><font color=red>{$brmsg}</font></h4></div>";
}else if ($esw == 2){ /// mailserver エラー
  print "<div><h4><font color=yellow>{$brmsg}</font></h4></div>";
}else {
  print "<div><h4><font color=white>{$brmsg}</font></h4></div>";
}
print '</div>';
print '</body>';
print '</html>';
?>
