<?php

date_default_timezone_set('Asia/Tokyo');
require_once 'BaseFunction.php';
require_once 'mysqlkanshi.php';
require_once 'phpsendmail.php';
require_once 'alarmwindow.php';
require_once "hostping.php";
require_once "varread.php";

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

/// メールサーバ設定  
if( isset($_GET['set']) ){
  $server=ckset($_GET['server']); /// mailserver server
  $port=ckset($_GET['port']);     /// mailserver port
  $authuser=ckset($_GET['authuser']); ///認証ユーザー
  $passwd=ckset($_GET['passwd']); /// 認証パスワード
  $from=ckset($_GET['from']);     /// admintb sender
  $to=ckset($_GET['to']);         /// admintb receiver
  $subj=ckset($_GET['subj']);     /// admintb subject
  $body=ckset($_GET['body']);     /// admintb body
  $user=$_GET['user'];
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
  $server=ckset($_GET['server']); /// mailserver server
  $port=ckset($_GET['port']);     /// mailserver port
  $authuser=ckset($_GET['authuser']); ///認証ユーザー
  $passwd=ckset($_GET['passwd']); /// 認証パスワード
  $from=ckset($_GET['from']);     /// admintb sender
  $to=ckset($_GET['to']);         /// admintb receiver
  $subj=ckset($_GET['subj']);     /// admintb subject
  $body=ckset($_GET['body']);     /// admintb body
  $user=$_GET['user'];
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
    }else if($flg==2){
      $sql="update mailserver set status='1'";
      putdata($sql);
      delstatus('Mail Server InActive');
      delstatus('Mail Server Active');
      setstatus('1','Mail Server InActive');
      $msg='#alert#'.$user.'#送信不可、送信可能phpsendmail.php.sendを置き換えて下さい';
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
}elseif(isset($_GET['param'])){
  paramSet();
  
  /// 結果表示
  print '<html><head>';
  print '<meta><link rel="stylesheet" href="css/kanshi1_py.css">';
  $user_sql="select authority,bgcolor from user where userid='".$user."'";
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザが無くなりました";
    branch('logout.php',$msg);
  }
  $userArr=explode(',',$userRows[0]);
  $auth=$userArr[0];
  $bgColor=$userArr[1];
  print "</head><body class={$bgColor}>";
  if ($brcode=='error' or $brcode=='alert' or $brcode=='notic'){
    print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }
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
    print '<h3>PHPメール送信設定と、メール試験送信が出来ます。<br>ただしメール試験送信は実際のメールサーバが使用できる状態に限り可能です</h3>';
  } else {
    print '<h2><img src="header/php.jpg" width="30" height="30">&ensp;&ensp;▽　メール送信　▽</h2>';
    print '<h3>PHPメール送信が出来ます<br></h3>';
  }
  print '<form name=aform action=PHPMailSend.php method=get>';
  print '<table>';
  print "<tr><td>SMTP Server:</td><td><input type=text name=server size=38 value={$host} ></td></tr>";
  print "<tr><td>SMTP Port:</td><td><input type=text name=port size=38 value={$port} ></td></tr>";
  print "<tr><td>SMTP認証ユーザー:</td><td><input type=text name=authuser size=38 value={$authuser} ></td></tr>";
  print "<tr><td>SMTP認証パスワード:</td><td><input type=text name=passwd size=38 value={$passwd} ></td></tr>";
  print '</table>';
  print '<h3>Fromアドレス、Toアドレスは、メールサーバの規則に従います<br>';
  print 'Subject,Bodyは任意の文字が入れられますが、改行は&lt;br&gt;とします<br></h3>';

  $sql='select receiver,sender,subject,body from admintb';
  $adminRows=getdata($sql);
  /// 送信元、送信先はadmintbから取得
  $adminArr=explode(',',$adminRows[0]);
  $to_email=$adminArr[0];
  $fr_email=$adminArr[1];
  $ttl=$adminArr[2];
  $body=$adminArr[3];
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
}else{
  paramGet($pgm);
}
?>
