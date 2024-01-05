<?php
require_once 'BaseFunction.php';
require_once 'mysqlkanshi.php';
error_reporting(E_ERROR | E_PARSE);

print '<html><body><head>';
print '<link rel="stylesheet" href="kanshi1_py.css">';
print '</head><body>';
print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　レイアウト処理結果　▽</h2>';
print '<br>';

$msg='';
$terms=$_GET['terms'];
$tosave=$_GET['tosave'];
$user=$_GET['user'];

//$debug_alert = "<script type='text/javascript'>alert('一時停止');</script>";
//print $debug_alert;

///
if(empty($terms)){
  $msg="#error#".$user."#レイアウトの「選択」がされていません";
  $nextpage="ShowLayout.php";
  branch($nextpage,$msg);
   
}else{
  if (! isset($_GET['dele'])){
    $sql="select dataflag from g".$terms;
    $rows=getdata($sql);
    foreach ($rows as $lflag){
      if ($lflag == "0"){
        $msg="#error#".$user."#レイアウトが完成されていません";
        $nextpage="ShowLayout.php";
        branch($nextpage,$msg);
      }
    }
  }
}

if($terms=='layout' && $tosave==''){  
  $msg="#error#".$user."#レイアウト保存の「保存先」がありません"; 
  $nextpage="ShowLayout.php";
  branch($nextpage,$msg);
    
}else if($terms=='layout' && $tosave!==''){
  ///　レイアウトと保存先がある場合の処理
  ///　現用を保存先へ保存 
  $gdrsql='drop table if exists glayout_'.$tosave;
  $gcrsql='create table glayout_'.$tosave.' select * from glayout';
  $sdrsql='drop table if exists layout_'.$tosave;
  $scrsql='create table layout_'.$tosave.' select * from layout';
  putdata($gdrsql);
  putdata($gcrsql);
  putdata($sdrsql);
  putdata($scrsql);
  $tosavearr=explode('_',$tosave);
  $msg="#notic#".$user."#グループとホストの現用レイアウトが".$tosavearr[1]."へ保存されました";
  $nextpage="ShowLayout.php";
  branch($nextpage,$msg);
  ///
}else if(!empty($terms) && $tosave==''){
  ///  保存先が無い場合は、指定レイアウトを現用にするか、削除か
  if(empty($_GET['dele'])){ 
    /// 指定レイアウトを現用へ
    $loadlay=$_GET['terms'];
    $gdrsql='drop table if exists glayout';
    $gcrsql='create table glayout select * from g'.$loadlay;
    $sdrsql='drop table if exists layout';
    $scrsql='create table layout select * from '.$loadlay;
    putdata($gdrsql);
    putdata($gcrsql);
    putdata($sdrsql);
    putdata($scrsql);
    $loadlayarr=explode('_',$loadlay);
    /// $loadlay.'からlayoutへ読込みレイアウト名を取得し下記表示<br>';
    $msg="#notic#".$user."#レイアウト".$loadlayarr[1]."のグループとホストのレイアウトが現用になりました";
    $nextpage="ShowLayout.php";
    branch($nextpage,$msg);
    ///
  }else{
    /// 指定レイアウトを削除
    $dellay=$_GET['terms']; // termsからlayout_xxが来るので、groupの場合はgをつけglayoutにする
    $gdrsql='drop table if exists g'.$dellay;
    $sdrsql='drop table if exists '.$dellay;
    putdata($gdrsql);
    putdata($sdrsql);
    $dellayarr=explode('_',$dellay);
    /// $loadlay.'からlayoutへ読込みレイアウト名を取得し下記表示<br>';
    $msg="#notic#".$user."#レイアウト".$dellayarr[1]."のグループとホストのレイアウトが削除されました";
    //writeloge($pgm,$msg);
    $nextpage="ShowLayout.php";
    branch($nextpage,$msg);
    ///
  }
}
if($msg!=''){
  print "<h4><font color=red>{$msg}</font></h4>";
}
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body></html>';
?>

