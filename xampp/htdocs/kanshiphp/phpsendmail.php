<?php
///エラー表示
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
///
ini_set('display_errors', 'Off');
mb_internal_encoding("UTF-8");
if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
  ///
  /// Windows xampp
  ///
  $kanshiDir=__DIR__;
  $baseDir=explode('htdocs',$kanshiDir);
  $autoDir=$baseDir[0] . "vendor\autoload.php";
  $langDir=$baseDir[0] . "vendor\phpmailer\phpmailer\language\phpmailer.lang-ja.php";
  require_once $autoDir;
  require_once $langDir;

}else{
  ///
  /// Unix/Linux
  ///
  require_once 'varread.php';
  $vpatharr=array("vpath_phpmailer");
  $rtnv=pathget($vpatharr);
  $srcdir=$rtnv[0]."/vendor/phpmailer/phpmailer";
  $except=$srcdir . "/src/Exception.php";
  $mailer=$srcdir . "/src/PHPMailer.php";
  $smtp=$srcdir . "/src/SMTP.php";
  $lang=$srcdir . "/language/phpmailer.lang-ja.php";
  require_once $except;
  require_once $mailer;
  require_once $smtp;
  require_once $lang;
}
///
/// common
///
require_once 'mysqlkanshi.php';
require_once 'alarmwindow.php';
///
mb_language('uni');
mb_internal_encoding('UTF-8');

function phpsendmail($hst, $prt, $from, $to, $subj, $body){
 $pgm="phpsendmail.php";
 $flg=getmailstatus();
 if ($flg==0){
  $mail_sql="select * from mailserver";
  $mailRows=getdata($mail_sql);
  $mailArr=explode(',',$mailRows[0]);
  if (empty($hst) || $hst==""){
    $host=$mailArr[0]; /// mailserレコードからhostをセット
  } else {
    $host=$hst;
  }
  if (empty($prt) || $prt==""){
    $port=intval($mailArr[1]); /// mailserverレコードからportセット
  } else {
    $port=intval($prt);
  }
  $authuser=$mailArr[2];  ///認証ユーザー
  $passwd=$mailArr[3];    ///認証パスワード
  $status=$mailArr[4]; // mailserverのstatus 0:ok 1:ng
  if ($status == "0" || is_null($status)) {
    /// post : integer
    /// body : array
    /// インスタンスを生成（true指定で例外を有効化）
    $mail = new PHPMailer(true);
    $rcode=0;
    try {
      ///メールサーバー設定
      ///　デバック
      ///    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      
      $mail->Debugoutput = function($str, $level) { syslog(LOG_ERR, "PHP Mailer:" . $str); };                      
      /// SMTPの使用
      $mail->isSMTP();
      /// smtpサーバー設定
      $mail->Host       = $host;
      /// SMTP認証     
      if ($port==587){             
        $mail->SMTPAuth   = true;
        /// -------------------------------------------------------
        /// -----------SMTP認証用ユーザー/ パスワード-------------- 
        /// SMTP ユーザー
        $mail->Username   = $authuser;
        /// SMTP パスワード                   
        $mail->Password   = $passwd;
      }
      /// -------------------------------------------------------
      /// ポート設定                           
      $mail->Port       = $port;  
      ///$mail->Port       = 25;                                  
      ///$mail->Port       = 587;                                    
      $mail->SMTPSecure = false;  ///非暗号化のSMTP認証
      $mail->SMTPAutoTLS= false;  ///TLSなし
      ///送信アドレス
      $mail->setFrom($from, 'vmadmin');
      ///宛先
      $mail->addAddress($to, 'receiver');
      ///cc    
      ///    $mail->addCC('vmadmin@host.domain');
      ///bcc
      ///    $mail->addBCC('vmadmin@host.domain');

      /// htmlメール指定
      ///    $mail->isHTML(true)a
      ///    $mail->Body = '<html>日本語本文</html>';
      ///    $mail->AltBody = '日本語本文';
      ///件名
      $mail->Subject = mb_encode_mimeheader($subj);
      ///内容
      if (is_array($body)){
        $bodystr = '';
        foreach ($body as $item){
          $bodystr=$bodystr.$item.'<br>';
        }
        $bodystr=rtrim($bodystr,'<br>');
      }else{
        $bodystr = $body;
      }
      $mail->CharSet = 'UTF-8';
      $mail->isHTML(false);
      $mail->Body    = $bodystr;
      
      ///メール送信
      $mail->send();
      $rcode=0;
      delstatus('Mail Server InActive');
      delstatus('Mail Server Active');
      setstatus('0','Mail Server Active');
      
      ///添付ファイルクリア      
      //$mail->clearAttachments();
      
      ///送信先情報クリア
      ///$mail->clearAllRecipients();
    } catch (Exception $e) {
      $msg="メッセージ送信不可、PHPMailer不具合か設定ミス: {$mail->ErrorInfo}";
      writeloge($pgm,$msg);
      $rcode=1;
    }
  } else {
    delstatus('Mail Server Active');               /// mail送出がエラーで返って来たので
    setstatus('1','Mail Server InActive');         /// InActiveにしている
    $msg="メールサーバがDBのmailserverテーブルに未登録か状態が非活動";
    writeloge($pgm,$msg);
    $rcode=1;
  }
  return $rcode;
 } else {
  $msg="メールサーバ使用不可能をstatusテーブルで検知"; // getmailstatusでInActiveになっていた
  writeloge($pgm,$msg);
  return 1;
 }
}
/*
$subj="subject 22-22";
$body="body 22-22";
$rtcd=phpsendmail("192.168.1.139",25,"vmadmin@mydomain.jp","mailuser@mydomain.jp", $subj, $body);
var_dump($rtcd);
*/
?>

