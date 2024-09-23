<?php

date_default_timezone_set('Asia/Tokyo');
require_once 'BaseFunction.php';
require_once 'mysqlkanshi.php';
require_once 'phpsendmail.php';
require_once 'alarmwindow.php';
require_once "hostping.php";

function ckset($data){
  $rtdata='';
  if(isset($data)){
    $rtdata=$data;
  }
  return $rtdata;
}

function mailstatset($server,$port,$authuser,$passwd,$from,$to,$subj,$body){
  $pingsw=hostping($server);
  if ($pingsw==0){
    $upsql="update mailserver set server='".$server."', port=".$port.",user='".$authuser."',password='".$passwd."'";
    putdata($upsql);
    $upsql="update admintb set sender='".$from."',receiver='".$to."',subject='".$subj."',body='".$body."'";
    putdata($upsql);
    delstatus('Mail Server InActive');    /// mailserverからping応答があるので
    delstatus('Mail Server Active');      /// InActiveからActiveにする　
    setstatus('0','Mail Server Active');
    return 0;
    
  }else{
    delstatus('Mail Server InActive');     /// mailserverからping応答がないので
    delstatus('Mail Server Active');       /// InActiveにする
    setstatus("1","Mail Server InActive");
    $sql='update mailserver set status="1"';
    putdata($sql);    
    return 1;
  }
}
$pgm="PHPMailSend.php";
$user="";
$brcode="";
$brmsg="";
print '<html><head>';
print '<meta><link rel="stylesheet" href="css/kanshi1.css">';
print '</head><body>';
///-------------------------------------------------
///--------セッションデータのユーザ取得-------------
///-------------------------------------------------
if (!isset($_GET['param']) and !isset($_GET['set']) and !isset($_GET['send'])){
  paramGet($pgm);
///------------------------------------------------------------------
///------ユーザ情報取得、入力画面処理--------------------------------
///--param=<user> 又は　#<code>#<user>#<message>のフォーマット-------
///
}elseif(isset($_GET['param'])){ 
  paramSet();
  ///
}else{
  /// 送信、設定共通
  $server=ckset($_GET['server']); /// mailserver server
  $port=ckset($_GET['port']);     /// mailserver port
  $authuser=ckset($_GET['authuser']); ///認証ユーザー
  $passwd=ckset($_GET['passwd']); /// 認証パスワード
  $from=ckset($_GET['from']);     /// admintb sender
  $to=ckset($_GET['to']);         /// admintb receiver
  $subj=ckset($_GET['subj']);     /// admintb subject
  $body=ckset($_GET['body']);     /// admintb body
  $user=$_GET['user'];
  /// メールサーバ設定  
  if( isset($_GET['set']) ){
    /// 設定
    $type='set';
    $flg=mailstatset($server,$port,$authuser,$passwd,$from,$to,$subj,$body);
    if ($flg==0){
      $msg='#notic#'.$user.'#設定完了、「送信」で受信を確認して下さい';
      branch($pgm,$msg);
    }else{
      $msg='#error#'.$user.'#設定失敗';
      branch($pgm,$msg);
    }
    /// メール送信テスト
  } elseif( isset($_GET['send']) ){
    /// 送信
    if ($server != '127.0.0.1'){
      $type='send'; 
      $sql="update mailserver set status='0'";  /// mailserverをActiveにする
      putdata($sql);
      $flg=phpsendmail($server,$port,$from,$to,$subj,$body);
      if($flg==0){
        delstatus('Mail Server InActive');
        delstatus('Mail Server Active');
        setstatus('0','Mail Server Active');
        $msg='#notic#'.$user.'#送信完了、受信を確認して下さい';
        branch($pgm,$msg);
      }else{
        $sql="update mailserver set status='1'";
        putdata($sql);
        delstatus('Mail Server InActive');
        delstatus('Mail Server Active');
        setstatus('1','Mail Server InActive');
        $msg='#error#'.$user.'#送信失敗';
        branch($pgm,$msg);
      }
    }else{
      $msg='#alert#'.$user.'#127.0.0.1はメールサーバではありません';
      branch($pgm,$msg);
    }
  }
}
/// 結果表示
if ($brcode=='error' or $brcode=='alert' or $brcode=='notic'){
  print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
}
/// 管理者かチェック
$rdsql="select * from user where userid='".$user."'";
$rows=getdata($rdsql);
$sdata=explode(',',$rows[0]);
$auth=$sdata[2];
/// メールサーバホスト、ポートは mailserverから取得
$rdsql="select * from mailserver";
$rows=getdata($rdsql);
$sdata=explode(',',$rows[0]);
$host=$sdata[0];
$port=$sdata[1];
$authuser=$sdata[2];
if($port==587 and $authuser==""){
  $authuser='Port587は認証ユーザーを入力';
}
$passwd=$sdata[3];
if($port==587 and $passwd==""){
  $passwd='Port587は認証パスワード入力';
}
if ($auth=='1'){
  print '<h2><img src="header/php.jpg" width="30" height="30">&ensp;&ensp;▽　メール送信と設定　▽</h2>';
  print '<h4>PHPメール送信設定と、メール送信が出来ます<br></h4>';
} else {
  print '<h2><img src="header/php.jpg" width="30" height="30">&ensp;&ensp;▽　メール送信　▽</h2>';
  print '<h4>PHPメール送信が出来ます<br></h4>';
}
print '<form name=aform action=PHPMailSend.php method=get>';
print '<table>';
print "<tr><td>SMTP Server:</td><td><input type=text name=server size=38 value={$host} ></td></tr>";
print "<tr><td>SMTP Port:</td><td><input type=text name=port size=38 value={$port} ></td></tr>";
print "<tr><td>SMTP認証ユーザー:</td><td><input type=text name=authuser size=38 value={$authuser} ></td></tr>";
print "<tr><td>SMTP認証パスワード:</td><td><input type=text name=passwd size=38 value={$passwd} ></td></tr>";
print '</table>';
print '<h4>Fromアドレス、Toアドレスは、メールサーバの規則に従うこと<br>';
print 'Subject,Bodyは任意の文字が入れられるが、&gt; &lt;は避けること<br></h4>';

$sql='select * from admintb';
$rows=getdata($sql);
/// 送信元、送信先はadmintbから取得
$row=explode(',',$rows[0]);
$to_email=$row[3];
$fr_email=$row[4];
$ttl=$row[5];
$body=$row[6];
print '<table>';
print "<tr><td>From Address:</td><td><input type=text name=from size=40 value={$fr_email}></td></tr>";
print "<tr><td>To Address:</td><td><input type=text name=to size=40 value={$to_email}></td></tr>";
print "<tr><td>Subject:</td><td><input type=text name=subj size=40 value='".$ttl."'></td></tr>";
print "<tr><td>Body:</td><td><input type=text name=body size=80 value='".$body."'></td></tr></table><br>";
print "<input type=hidden name=user value={$user}>";
if ($auth=='1'){
  print '<tr><td></td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class=button type=submit name=set value="設定"></td>';
}
print '<td></td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class=buttonyell type=submit name=send value="送信"></td></tr>';
print "<br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ</span></a>";
print '</body></html>';

?>
