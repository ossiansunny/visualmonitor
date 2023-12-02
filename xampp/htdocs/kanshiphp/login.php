<?php
require_once "mysqlkanshi.php";
require_once "winhostping.php";
require_once "mailsendany.php";
require_once "alarmwindow.php";
//
$pgm = "login.php";
//
date_default_timezone_set('Asia/Tokyo');

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
  $coretime=$pdata[4];
  $corestamp=$pdata[5];
  $snmptime=$pdata[6];
  $snmpstamp=$pdata[7];
  $mrtgtime=$pdata[8];
  $mrtgstamp=$pdata[9];
  $diff=time() - intval($corestamp); ///現在時刻からcore起動した時刻の差
  $rtncd="";
  if ($kanri=='1'){ ///管理者
    if ($diff < intval($coretime)){ /// 差がcore起動間隔より小さいか
      $rtncd='1'; /// Yes 監視が管理者により実行されている
    }else{
      $rtncd="0";
    }
  }else{            ///一般ユーザ
    if ($diff >= intval($coretime)){ /// 差がcore起動間隔より大きいか等しいか
      $rtncd='1'; /// Yes　監視が管理者より実行されている
    }else{
      $rtncd="0";
    }
  }
  return $rtncd;
}

function setsess($value){
  echo '<script type="text/javascript">';
  echo 'sessionStorage.setItem("user","'.$value.'");';
  echo '</script>';
}

function branch($page,$param){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo "<form name='F' action={$page} method='get'>";
  echo "<input type=hidden name=param value={$param}>";
  echo '<input type="submit" name="next" value="Waiting...">';
  echo '</form>';
}
$fsw=0;  /// 初回=0 param有り=1
$ercde="0"; /// "1":no mailserer
$esw=0;
$errorcde="";
$errormsg="";
$mailserver="";
$mailport=25;
/// get admin data
$adminsql='select * from admintb';
$arows=getdata($adminsql);
$adata=explode(',',$arows[0]);
$reset=$adata[0]; // AdminPageで初期化すると'reset'、デフォルトはNULL
$authority=$adata[2];
$toaddr=$adata[3];
$fromaddr=$adata[4];
$subject=$adata[5];
$body=$adata[6];
if (isset($_GET['param'])){   /// branchで戻った時の処理
  if (substr($_GET['param'],0,1)=="#" && substr($_GET['param'],5,1)=="#") {
    $errorcde=mb_substr($_GET['param'],0,6);
    $errormsg=mb_substr($_GET['param'],6);
  } else {
    $errorcde="";
    $errormsg=$_GET['param'];
  }
  $fsw=1;
  $esw=1; 
}else{
  if (isset($_GET['login'])){  /// login ボタン押した時の処理
    if (isset($_GET['init'])){ /// init is NULL or 'init'
      if ($_GET['init']=="on"){
        echo "Initialize";
        $upsql='update admintb set kanriname=null';
        putdata($upsql);
        writeloge($pgm,"----- VisualMonitor has Initialized -----");
        $sub = "Initialize " . $user;
        $message=$user.' Initialized';
        mailsendany('loginlogout',$fromaddr,$toaddr,$sub,$message);
      }
    }
    $passwd=$_GET['passwd'];
    $user=$_GET['user'];
    $ercde=$_GET['errorcde'];
    $ucode="";
    $auth="";
    $uname="";
    $selsql='select * from user where userid="'.$user.'"';
    $udata=getdata($selsql);
    $c=count($udata);
    if ($c==0){ /// userなし
      $msg="●入力したユーザー".$user."がありません";
      writeloge($pgm,$msg);
      branch("login.php","#2001#".$msg);
      echo '</body></html>';
    }else{ // userあり
      foreach ($udata as $urec){  
        $sdata=explode(',',$urec);
        if ($passwd != $sdata[1]){
          $msg=$user." ●パスワードが不正です、再ログインして下さい";
          writeloge($pgm,$msg);
          branch('login.php',"#2002#".$msg);
          echo '</body></html>';
        }else{
          $msg=$user." Login Successfull";
          writelogd($pgm,$msg);
          $userid=$sdata[0];
          $auth=$sdata[2];
          $uname=$sdata[3];
          $ucode=$sdata[4];
          setsess($user);
          if ($auth == '1'){ // auth=1
            $rtn=checkproc($auth);
            if ($rtn=="0"){
              $now=date('ymdHis');
              $tstamp = $now;
              $upsql='update processtb set admin="'.$user.'",starttime="'.$tstamp.'"';
              putdata($upsql);
              /// echo 開始イベントログ
              $logname = "LOGIN_" . $user; 
              $insql = "insert into eventlog (host,eventtime,eventtype,kanrisha,kanrino) values('".$logname."','".$tstamp."','0','".$userid."','')";
              putdata($insql); 
              $msg = $logname . " Eventlog Insert sql: " . $insql;
              writeloge($pgm,$msg);              
              /// echo 開始メール 
              $sub=$logname;
              $message=$user.' Logged in';
              mailsendany('loginlogout',$fromaddr,$toaddr,$sub,$message);
              /// check mailserver
              mailstatset($server,$port,$fromaddr,$toaddr,$subject,$body);
              $upsql='update admintb set kanriname="'.$userid.'",authority="'.$auth.'",kanrino="'.$ucode.'"';
              putdata($upsql);
              branch("MainIndexphp.html","");
            }else{ /// auth=0
              $msg="●既に監視が管理者で実行されています";
              branch("login.php","#2003#".$msg);
            } 
          }else{ /// auth=0
            $rtn=checkproc($auth);
            if ($rtn=="0" and $authority=="1"){
              /// echo 開始イベントログ
              $logname = "LOGIN_" . $user; 
              $insql = "insert into eventlog (host,eventtime,eventtype,kanrisha,kanrino) values('".$logname."','" . $tstamp . "','0','".$userid."','')";
              putdata($insql); 
              $msg = $logname . " Eventlog Insert sql: " . $insql;
              writeloge($pgm,$msg);  
              /// echo 開始メール 
              
              $sub=$logname;
              $message=$user.' Logged in';
              mailsendany('loginlogout',$fromaddr,$toaddr,$sub,$message);
              branch("MainIndexUphp.html","");
            }else{
              $msg="●監視が管理者によって実行されていません";
              branch("login.php","#2004#".$msg);
            }
          }          
          echo '</body></html>';
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
$pingsw=hostping($mailserver);
$sql='';
if ($pingsw != 0) {
  $errormsg=$errormsg."<br>●メールサーバー".$mdata[0]."が見つかりません<br>ログイン後メニュー「メール設定・送信」で確認して下さい";
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

echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<meta charset="utf-8">';
echo '<title>サンプル</title>';
echo '<link rel="stylesheet" href="login.css">';
echo '</head>';
echo '<body>';
echo '<div class="login">';
echo '<div class="login-triangle"></div>';
echo '<h2 class="login-header"><img src="header/php.jpg" width="70" height="70">&emsp;&emsp;監視ログイン</h2>';
echo '<form class="login-container" type="get" action="login.php">';
echo '<p><input type="text" name="user" value="" placeholder="ユーザID" required></p>';
echo '<p><input type="password" name="passwd" placeholder="パスワード" required></p>';
/// $resetはadmintbのkanriname、AdminPageで初期化すると'reset'になる 
if ($reset=='reset'){
  echo '<table><tr><td>&emsp;&emsp;初期化オプション</td><td><input type="checkbox" name="init"></td></tr></table>';
}
echo "<input type='hidden' name='errorcde' value={$ercde}>";
echo '<p><input type="submit" name="login" value="ログイン"></p>';
echo '</form>';
echo '</div>';
echo '<div class="login">';
///

if ($esw == 1){
  echo "<div><h4><font color=red>{$errormsg}</font></h4></div>";
}else if ($esw == 2){
  echo "<div><h4><font color=yellow>{$errormsg}</font></h4></div>";
}else {
  echo "<div><h4><font color=white>{$errormsg}</font></h4></div>";
}
echo '</div>';
echo '</body>';
echo '</html>';
?>
