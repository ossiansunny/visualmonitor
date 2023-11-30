<?php
echo '<html><head>';
echo '<meta><link rel="stylesheet" href="kanshi1.css">';
echo '</head><body>';
date_default_timezone_set('Asia/Tokyo');
require_once 'mysqlkanshi.php';
require_once 'phpsendmail.php';
require_once 'alarmwindow.php';
require_once "winhostping.php";

function ckset($data){
  $rtdata='';
  if(isset($data)){
    $rtdata=$data;
  }
  return $rtdata;
}

function mailstatset($server,$port,$from,$to,$subj,$body){
  $pingsw=hostping($server);
  if ($pingsw==0){
    $upsql="update mailserver set server='".$server."', port=".$port;
    putdata($upsql);
    $upsql="update admintb set sender='".$from."',receiver='".$to."',subject='".$subj."',body='".$body."'";
    putdata($upsql);
    delstatus('Mail Server InActive');                    // mailserverからping応答があるので
    delstatus('Mail Server Active');                      // InActiveからActiveにする　
    setstatus('0','Mail Server Active');
    echo '<br><h3><font color=green>設定完了</font></h3>';
  }else{
    delstatus('Mail Server InActive');                    // mailserverからping応答がないので
    delstatus('Mail Server Active');                      // InActiveにする
    setstatus("1","Mail Server InActive");
    $sql='update mailserver set status="1"';
    putdata($sql);
    echo '<br><h3><font color=green>設定失敗</font></h3>';
  }
}
///-------------------------------------------------
///--------セッションデータのユーザ取得-------------
///-------------------------------------------------
if (!isset($_GET['param'])){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="PHPMailSend.php" method="get">';
  echo '<input type="hidden" name="param" value="">';
  echo '<input type="submit" name="next" style="display:none;" />';
  echo '</form></body></html>';
  echo '<script type="text/javascript">';
  echo 'var keyvalue = sessionStorage.getItem("user");';
  echo 'if (!keyvalue) {';
  echo '  keyvalue = "unknown";';
  echo '}';
  echo 'document.forms["F"].elements["param"].value = keyvalue;';
  echo '</script>';
///------------------------------------------------------------------
///------ユーザ情報取得、入力画面処理--------------------------------
///--param=<user> 又は　#<code>#<user>#<message>のフォーマット-------
///
}else{ 
  $user=$_GET['param'];
  $server=ckset($_GET['server']); // mailserver server
  $port=ckset($_GET['port']);     // mailserver port
  $from=ckset($_GET['from']);     // admintb sender
  $to=ckset($_GET['to']);         // admintb receiver
  $subj=ckset($_GET['subj']);     // admintb subject
  $body=ckset($_GET['body']);     // admintb body
  /// メールサーバ設定
  if( isset($_GET['set']) ){
    $type='set';
    mailstatset($server,$port,$from,$to,$subj,$body);
    /// メール送信テスト
  } elseif( isset($_GET['send']) ){
    $type='send'; 
    $sql="update mailserver set status='0'";
    putdata($sql);
    $flg=phpsendmail($server,$port,$from,$to,$subj,$body);
    if($flg==0){
      echo '<br><h3><font color=green>送信完了</font></h3>';
      echo '<h3><font color=gray>メーラ等でメールボックスを確認して下さい</font></h3><br>';
      delstatus('Mail Server InActive');
      delstatus('Mail Server Active');
      setstatus('0','Mail Server Active');
    }else{
      echo '<br><h3><font color=green>送信失敗</font></h3>';
      $sql="update mailserver set status='1'";
      putdata($sql);
      delstatus('Mail Server InActive');
      delstatus('Mail Server Active');
      setstatus('1','Mail Server InActive');
    }
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
  if ($auth=='1'){
    echo '<h2>メール送信と設定</h2>';
    echo '<h4>PHPメール送信設定と、メール送信が出来ます<br></h4>';
  } else {
    echo '<h2>メール送信</h2>';
    echo '<h4>PHPメール送信が出来ます<br></h4>';
  }
  echo '<form name=aform action=PHPMailSend.php method=get>';
  echo '<table>';
  echo "<tr><td>SMTP Server:</td><td><input type=text name=server size=38 value={$host} ></td></tr>";
  echo "<tr><td>SMTP Port:</td><td><input type=text name=port size=38 value={$port} ></td></tr>";
  echo '</table>';
  echo '<h4>Fromアドレス、Toアドレスは、メールサーバの規則に従うこと<br>';
  echo 'Subject,Bodyは任意の文字が入れられるが、&gt; &lt;は避ける<br></h4>';

  $sql='select * from admintb';
  $rows=getdata($sql);
  /// 送信元、送信先はadmintbから取得
  $row=explode(',',$rows[0]);
  $to_email=$row[3];
  $fr_email=$row[4];
  $ttl=$row[5];
  $body=$row[6];
  echo '<table>';
  echo "<tr><td>From Address:</td><td><input type=text name=from size=40 value={$fr_email}></td></tr>";
  echo "<tr><td>To Address:</td><td><input type=text name=to size=40 value={$to_email}></td></tr>";
  echo "<tr><td>Subject:</td><td><input type=text name=subj size=40 value={$ttl}></td></tr>";
  echo "<tr><td>Body:</td><td><input type=text name=body size=80 value={$body}></td></tr></table><br>";
  if ($auth=='1'){
    echo '<tr><td></td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class=button type=submit name=set value="設定"></td>';
  }
  echo '<td></td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class=buttonyell type=submit name=send value="送信"></td></tr>';
  echo "<br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ</span></a>";
  echo '</body></html>';
}
?>
