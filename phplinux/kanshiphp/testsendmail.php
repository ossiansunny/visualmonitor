<?php
///エラー表示
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
//
require_once "/usr/local/bin/vendor/phpmailer/phpmailer/src/Exception.php";
require_once "/usr/local/bin/vendor/phpmailer/phpmailer/src/PHPMailer.php";
require_once "/usr/local/bin/vendor/phpmailer/phpmailer/src/SMTP.php";
///
mb_language('uni');
mb_internal_encoding('UTF-8');
$mail = new PHPMailer(true);
$mail->CharSet = 'utf-8';
try {
  $mail->isSMTP();
  $mail->Host = '192.168.1.139';       
  /// SMTP認証     
  $mail->SMTPAuth   = true;
  $mail->Username   = 'oshima@sunnyblue.mydns.jp';
  $mail->Password   = 'amihsho1306';
  $mail->Port       = 587;                                    
  $mail->setFrom('vmadmin@sunnyblue.mydns.jp', 'vmadmin');
  $mail->addAddress('oshima@sunnyblue.mydns.jp', 'receiver');
  $mail->Subject = '件名';
  $mail->Body    = 'メッセージ本体';
  $mail->send();
} catch (Exception $e) {
  echo "error: {$mail->ErrorInfo}";
}

?>

