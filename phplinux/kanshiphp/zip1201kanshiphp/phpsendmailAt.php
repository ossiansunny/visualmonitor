<?php
/// usrは重複させずグローバルで宣言する
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

///エラー表示
ini_set('display_errors', 'Off');
mb_internal_encoding("UTF-8");
$mrtgDir="";
$plotDir="";
$osDirSep="";
if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
  ///
  /// Windows xampp
  ///
  $kanshiDir=__DIR__;
  $baseDir=explode('htdocs',$kanshiDir);
  $autoDir=$baseDir[0]."vendor\autoload.php";
  $langDir=$baseDir[0]."vendor\phpmailer\phpmailer\language\phpmailer.lang-ja.php";
  $mrtgDir=$baseDir[0]."htdocs\\mrtg\\mrtgimage";
  $plotDir=$baseDir[0]."htdocs\\plot\\plotimage";
  require_once $autoDir;
  require_once $langDir;
  $osDirSep="\\";
}else{
  ///
  /// Unix/Linux
  ///
  require_once 'varread.php';
  $vpatharr=array("vpath_phpmailer","vpath_htdocs");
  $rtnv=pathget($vpatharr);
  if(count($rtnv)==2){
    $srcdir=$rtnv[0]."/vendor/phpmailer/phpmailer";
    $except=$srcdir . "/src/Exception.php";
    $mailer=$srcdir . "/src/PHPMailer.php";
    $smtp=$srcdir . "/src/SMTP.php";
    $lang=$srcdir . "/language/phpmailer.lang-ja.php";
    $mrtgDir=$rtnv[1]."/mrtg/mrtgimage";
    $plotDir=$rtnv[1]."/plot/plotimage";
    require_once $except;
    require_once $mailer;
    require_once $smtp;
    require_once $lang;
    $osDirSep="/";
  }else{
    $msg="vpath_phpmailer and/or vpath_htdocs invalidi, check kanshiphp.ini";
    writeloge($pgm,$msg);
    return 1;

  }
}
///
/// Common
///
require_once 'mysqlkanshi.php';
require_once 'alarmwindow.php';
///
mb_language('uni');
mb_internal_encoding('UTF-8');

function phpsendmailat($hst, $prt, $from, $to, $subj, $body, $attach){
 global $mrtgDir, $plotDir, $osDirSep;
 $pgm="phpsendmailAt.php";
 $flg=getmailstatus();
 ///$flg=0; ///-----------------------------------debug  
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
  $status=$mailArr[4]; /// mailserverのstatus 0:ok 1:ng
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
      if ( $port==587 ) {
        /// SMTP認証                  
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
      
      /// ファイル添付
      $arrcnt=count($attach);
      if ($arrcnt>0){
        for($i=0;$i<$arrcnt;$i++){
          $attachfile=""; 
          if(strpos($attach[$i],'png')!==false){
            $attachfile=$mrtgDir.$osDirSep.$attach[$i];
          }elseif(strpos($attach[$i],'svg')!==false){
            $attachfile=$plotDir.$osDirSep.$attach[$i];
          }else{
            return 1;  
          }
          $mail->AddAttachment($attachfile);            
        }
      }
      ///メール送信
      $mail->send();
      $rcode=0;
      delstatus('Mail Server InActive');
      delstatus('Mail Server Active');
      setstatus('0','Mail Server Active');
      
      ///添付ファイルクリア      
      $mail->clearAttachments();
      
      ///送信先情報クリア
      ///$mail->clearAllRecipients();
    } catch (Exception $e) {
      $msg="メッセージ送信不可、PHPMailer不具合か設定ミス: {$mail->ErrorInfo}";
      writelogd($pgm,$msg);
      $rcode=1;
    }
  } else {
    delstatus('Mail Server Active');               /// mail送出がエラーで返って来たので
    setstatus('1','Mail Server InActive');
    $msg="メールサーバがDBのmailserverテーブルに未登録か状態が非活動";
    writelogd($pgm,$msg);
    $rcode=1;
  }
  return $rcode;
 } else {
  $msg="メールサーバ使用不可能をstatusテーブルで検知"; /// getmailstatusでInActiveになっていた
  writelogd($pgm,$msg);
  return 1;
 }
}

/// test
/*
$arrayat=array('192.168.1.139.cpu-day.png','192.168.1.139.svg','192.168.1.139.ram-day.png');
$rtncd=phpsendmailat('192.168.1.139',587, $from, $to, '画像添付混合', 'png,svg添付参照',$arrayat);
var_dump($rtncd);
*/
?>
