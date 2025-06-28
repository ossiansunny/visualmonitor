<?php
require_once 'init-varread.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
/// エラー処理、表示言語、タイムゾーン
ini_set('display_errors', 'Off');
mb_internal_encoding("UTF-8");
date_default_timezone_set('Asia/Tokyo');
$vpath_phpmailser="";
$vpath_kanshiphp="";
$vpath_ncat="";
$vpath_ubin="";
$vpathParam=array('vpath_phpmailer','vpath_kanshiphp','vpath_ncat','vpath_ubin');
$vpathArr=pathget($vpathParam);
if(count($vpathArr)!=4){
  echo 'mailcheck: vpath_phpmailser or/and vpath_kanshiphp or/and vpath_ncat or/and vpath_ubinエラー、kanshiphp,iniをチェックして下さい'.PHP_EOL;
}else{
  $vpath_phpmailer=$vpathArr[0];
  $vpath_kanshiphp=$vpathArr[1];
  $vpath_ncat=$vpathArr[2];
  $vpath_ubin=$vpathArr[3];
}
$except=$vpath_phpmailer . "/src/Exception.php";
$mailer=$vpath_phpmailer . "/src/PHPMailer.php";
$smtp=$vpath_phpmailer . "/src/SMTP.php";
$lang=$vpath_phpmailer . "/language/phpmailer.lang-ja.php";
require_once $except;
require_once $mailer;
require_once $smtp;
require_once $lang;
mb_language('uni');
mb_internal_encoding('UTF-8');
/////////////////////////
/// sendmail function
/////////////////////////
function sendmail($host, $port, $from, $to, $authuser, $passwd, $subj, $body){
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
      ///件名
      $mail->Subject = mb_encode_mimeheader($subj);
      ///内容
      $bodystr = $body;      
      $mail->CharSet = 'UTF-8';
      $mail->isHTML(false);
      $mail->Body    = $bodystr;
      ///メール送信
      $mail->send();
      $rcode=0;      
    } catch (Exception $e) {
      $rcode=1;
    }
}
/////////////////////////
/// メイン
/////////////////////////
$rtnCde=0;
if( $argc != 9 ){
  echo "mailcheck: 引数8つ指定(メールサーバ、ポート、送信元、送信先、認証ユーザ、認証パスワード、件名、本文)して下さい".PHP_EOL;
  $rtnCde=1;
}else{
  $mServer=$argv[1];
  $mPort=$argv[2];
  $mFrom=$argv[3];
  $mTo=$argv[4];
  $mAuth=$argv[5];
  $mPass=$argv[6];
  $mSubj=$argv[7];
  $mBody=$argv[8];
  ///
  /// PHPMailerアプリの存在チェック phpmailer
  ///
  $phpmailersw=0;
  if(is_dir($vpath_phpmailer)){
    echo "mailcheck:PHPMailer は存在します".PHP_EOL;
  }else{
    $phpmailersw=1;
    echo "mailcheck:PHPMailerが確認出来ません".PHP_EOL;
  }
  ///
  /// portポートのチェック 25 587
  ///
  $output=null;
  $result=null;
  $cmd='"'.$vpath_ncat.'" -z -w 0.5 '.$mServer.' 25';
  #echo 'mailcheck: path='.$cmd.PHP_EOL;    
  exec($cmd,$output,$result);
  if($result==0){
    echo 'mailcheck: Server '.$mServer.' Port 25 は開いています'.PHP_EOL;
  }else{
    echo 'mailcheck: Server '.$mServer.' Port 25 は確認出来ません',PHP_EOL;
  }
  $output=null;
  $result=null;
  $cmd='"'.$vpath_ncat.'" -z -w 0.5 '.$mServer.' 587';
  exec($cmd,$output,$result);
  if($result==0){
    echo 'mailcheck: Server '.$mServer.' Port 587 は開いています'.PHP_EOL;
  }else{
    echo 'mailcheck: Server '.$mServer.' Port 587 は確認出来ません'.PHP_EOL;
  }
  
  ///
  /// メール監視アプリ入れ替え diff
  ///
  $Current=$vpath_kanshiphp.'/phpsendmail.php';
  $Send=$vpath_kanshiphp.'/phpsendmail.php.send';
  if(filesize($Current) < filesize($Send)){
    unlink($Current);
    copy($Send,$Current);
    echo 'mailcheck: phpsendmail.phpの入れ替え終了'.PHP_EOL;
  }else{
    echo 'mailcheck: phpsendmail.phpは入れ替えの必要ありません'.PHP_EOL;
  }
  $Current=$vpath_kanshiphp.'/phpsendmailAt.php';
  $Send=$vpath_kanshiphp.'/phpsendmailAt.php.send';
  if(filesize($Current) < filesize($Send)){
    unlink($Current);
    copy($Send,$Current);
    echo 'mailcheck: phpsendmailAt.phpの入れ替え終了'.PHP_EOL;
  }else{
    echo 'mailcheck: phpsendmailAt.phpは入れ替えの必要ありません'.PHP_EOL;
  }
  /// 
  /// send mail port 25
  ///
  $mRtn=sendmail($mServer,$mPort,$mFrom,$mTo,'','',$mSubj,$mBody);
  if($mRtn==1){
    echo 'mailcheck: ポート25のメール送信に失敗しました'.PHP_EOL;
  }else{
    echo 'mailcheck: ポート25のメールを送信しました'.PHP_EOL;
  }
  /// 
  /// send mail port 587
  ///
  $mRtn=sendmail($mServer,$mPort,$mFrom,$mTo,$mAuth,$mPass,$mSubj,$mBody);
  if($mRtn==1){
    echo 'mailcheck: ポート587のメール送信に失敗しました'.PHP_EOL;
  }else{
    echo 'mailcheck: ポート587のメールを送信しました'.PHP_EOL;
  }
}
return $rtnCde;
?>
