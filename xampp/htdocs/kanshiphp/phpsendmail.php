<?php
require_once "alarmwindow.php";
require_once "mysqlkanshi.php";
/// メール送信しないため、コメントにする
function phpsendmail($hst, $prt, $from, $to, $subj, $body){
  delstatus('Mail Server InActive');
  delstatus('Mail Server Active');
  setstatus("1","Mail Server InActive");
  $sql='update mailserver set status="1"';
  putdata($sql);
  return 0;
}
?>
