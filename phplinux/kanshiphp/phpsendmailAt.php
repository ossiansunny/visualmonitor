<?php
///エラー表示
ini_set('display_errors', 'Off');
require_once 'varread.php';
$vpatharr=array("vpath_phpmailer","vpath_htdocs");
$rtnv=pathget($vpatharr);
$srcdir=$rtnv[0]."/vendor/phpmailer/phpmailer";
$except=$srcdir."/src/Exception.php";
$mailer=$srcdir."/src/PHPMailer.php";
$smtp=$srcdir."/src/SMTP.php";
$lang=$srcdir."/language/phpmailer.lang-ja.php";
$mrtgdir=$rtnv[1]."/mrtg/mrtgimage";
$plotdir=$rtnv[1]."/plot/plotimage";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
mb_internal_encoding("UTF-8");
//
require_once $except;
require_once $mailer;
require_once $smtp;
require_once $lang;
require_once 'mysqlkanshi.php';
require_once 'alarmwindow.php';
///
mb_language('uni');
mb_internal_encoding('UTF-8');

function phpsendmailat($hst, $prt, $from, $to, $subj, $body, $attach){
 global $mrtgdir;
 global $plotdir;
 $pgm="phpsendmailAt.php";
 $flg=getmailstatus();
 //echo 'flg:'.$flg.PHP_EOL;
 ///$flg=0; ///-----------------------------------debug  
 if ($flg==0){
  $sql="select * from mailserver";
  $rows=getdata($sql);
  $row=explode(',',$rows[0]);
  if (empty($hst) || $hst==""){
    $host=$row[0]; /// mailserレコードからhostをセット
  } else {
    $host=$hst;
  }
  if (empty($prt) || $prt==""){
    $port=intval($row[1]); /// mailserverレコードからportセット
  } else {
    $port=intval($prt);
  }
  $authuser=$row[2];  ///認証ユーザー
  $passwd=$row[3];    ///認証パスワード
  $status=$row[4]; // mailserverのstatus 0:ok 1:ng
  ///$status="0"; //---------------------------------debug
  //echo 'status:'.strval($status).' host:'.$host.' '.strval($port).PHP_EOL; //--------debug
  if ($status == "0" || is_null($status)) {
    /// post : integer
    /// body : array
    /// インスタンスを生成（true指定で例外を有効化）
    //echo 'before instannce'.PHP_EOL;
    $mail = new PHPMailer(true);
    //echo 'after instannce'.PHP_EOL;
    $rcode=0;
    try {
      ///メールサーバー設定
      ///　デバック
      ///    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      
      $mail->Debugoutput = function($str, $level) { syslog(LOG_ERR, "PHP Mailer:" . $str); };                      
      /// SMTPの使用
      $mail->isSMTP();
      // smtpサーバー設定
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
      ///    $mail->addCC('vmadmin@centos75.sunnyblue.mydns.jp');
      ///bcc
      ///    $mail->addBCC('vmadmin@centos75.sunnyblue.mydns.jp');

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
      //echo strval($arrcnt).'<br>'.PHP_EOL;       
      if ($arrcnt>0){
        for($i=0;$i<$arrcnt;$i++){
          $attachfile=""; 
          if(strpos($attach[$i],'png')!==false){
            $attachfile=$mrtgdir."/".$attach[$i];
            //echo $attachfile.'<br>'.PHP_EOL;
          }elseif(strpos($attach[$i],'svg')!==false){
            $attachfile=$plotdir."/".$attach[$i];
            //echo $attachfile.'<br>'.PHP_EOL;
          }else{
            //echo $attach[i].'<br>'.PHP_EOL;
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
      //delstatus('Mail Server Active');
      //setstatus('1','Mail Server InActive');
      $msg="Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
      writelogd($pgm,$msg);
      //echo $msg; 
      $rcode=1;
    }
  } else {
    delstatus('Mail Server Active');               /// mail送出がエラーで返って来たので
    setstatus('1','Mail Server InActive');         /// InActiveにしている
    $msg="Mail Server not registered in database";
    writelogd($pgm,$msg);
    //echo $msg;
    $rcode=1;
  }
  return $rcode;
 } else {
  $msg="Mail Server inActive"; // getmailstatusでInActiveになっていた
  writelogd($pgm,$msg);
  return 1;
 }
}

// test
/*
$arrayat=array('192.168.1.139.cpu-day.png','192.168.1.139.svg','192.168.1.139.ram-day.png');
$from='';
$to='';
$rtncd=phpsendmailat('192.168.1.139',587, $from, $to, '画像添付混合', 'png,svg添付参照',$arrayat);
var_dump($rtncd);
*/
?>

