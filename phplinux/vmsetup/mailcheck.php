<?php
require_once 'init-varread.php';
$vpathParam=array('vpath_phpmailer','vpath_kanshiphp','vpath_ncat');
$vpathArr=pathget($vpathParam);
if(count($vpathArr)!=3){
  echo 'mailcheck:init-varread.php error'.PHP_EOL;
}else{
  $vpath_vmsetup=__DIR__;
  $vpath_base=explode('/vmsetup',$vpath_vmsetup);
  $vpath_ubin=$vpath_base[0].'/ubin';
  ///
  /// PHPMailerアプリの存在チェック phpmailer
  ///
  $phpmailersw=0;
  $phpmailer=$vpathArr[0];
  if(is_dir($phpmailer)){
    echo "mailcheck:PHPMailer は存在します".PHP_EOL;
  }else{
    $phpmailersw=1;
    echo "mailcheck:PHPMailerが確認出来ません".PHP_EOL;
  }
  ///
  /// メールサーバ取得
  ///
  $vpath_kanshiphp=$vpathArr[1];
  $vpath_ncat=$vpathArr[2];
  $vpathSql=$vpath_kanshiphp.'/mysqlkanshi.php';
  require_once $vpathSql;
  $mServerSql='select server from mailserver';
  $mServerRows=getdata($mServerSql);
  if(empty($mServerRows)){
    echo 'mailcheck:監視メールテーブルにアクセス出来ません'.PHP_EOL;
  }else{
    $mServer=$mServerRows[0];
    ///
    /// portポートのチェック 25 587
    ///
    $output=null;
    $result=null;
    $cmd='"'.$vpath_ncat.'" -z -w 0.5 '.$mServer.' 25';
    #echo 'mailcheck: path='.$cmd.PHP_EOL;    
    exec($cmd,$output,$result);
    if($result==0){
      echo 'mailcheck:Server '.$mServer.' Port 25 は開いています'.PHP_EOL;
    }else{
      echo 'mailcheck:Server '.$mServer.' Port 25 は確認出来ません',PHP_EOL;
    }
    $output=null;
    $result=null;
    $cmd='"'.$vpath_ncat.'" -z -w 0.5 '.$mServer.' 587';
    exec($cmd,$output,$result);
    if($result==0){
      echo 'mailcheck:Server '.$mServer.' Port 587 は開いています'.PHP_EOL;
    }else{
      echo 'mailcheck:Server '.$mServer.' Port 587 は確認出来ません'.PHP_EOL;
    }
  }
  ///
  /// メール監視アプリ入れ替え diff
  ///
  $mCurrent=$vpath_kanshiphp.'/phpsendmail.php';
  $noSend=$vpath_kanshiphp.'/phpsendmail.php.nosend';
  if(filesize($mCurrent)==filesize($noSend)){
    //delete current and rename send to current
    echo 'mailcheck:phpsendmail.phpの入れ替え終了'.PHP_EOL;
  }
  $mCurrent=$vpath_kanshiphp.'/phpsendmailAt.php';
  $noSend=$vpath_kanshiphp.'/phpsendmailAt.php.nosend';
  if(filesize($mCurrent)==filesize($noSend)){
    //delete currentAt and rename sendAt to currentAt
    echo 'mailcheck:phpsendmailAt.phpの入れ替え終了'.PHP_EOL;
  }
}
?>
