<?php
require_once 'BaseFunction.php';
require_once 'mysqlkanshi.php';
error_reporting(E_ERROR | E_PARSE);

print '<html><body><head>';
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
print '</head><body>';
print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　レイアウト処理結果　▽</h2>';
print '<br>';

$alertMsg='';
$get_layout=$_GET['terms'];
$get_tosave=$_GET['tosave'];
$user=$_GET['user'];
///
///  デバッグでアラートウインドウを表示 
///$debug_alert = "<script type='text/javascript'>alert('一時停止');</script>";
///print $debug_alert;
///
///
if(empty($get_layout)){
  $alerMsg="#error#".$user."#レイアウトの「選択」がされていません";
  $nextpage="ShowLayout.php";
  branch($nextpage,$alerMsg);
}else{
  if (! isset($_GET['dele'])){
    $layout_sql="select dataflag from g".$get_layout;
    $layoutRows=getdata($layout_sql);
    foreach ($layoutRows as $layoutCheckFlag){
      if ($layoutCheckFlag == "0"){
        $alerMsg="#error#".$user."#レイアウトが完成されていません";
        $nextpage="ShowLayout.php";
        branch($nextpage,$alerMsg);
      }
    }
  }
}

if($get_layout=='layout' && $get_tosave==''){  
  $alerMsg="#error#".$user."#レイアウト保存の「保存先」がありません"; 
  $nextpage="ShowLayout.php";
  branch($nextpage,$alerMsg);
    
}else if($get_layout=='layout' && $get_tosave!==''){
  ///　レイアウトと保存先がある場合の処理
  ///　現用を保存先へ保存 
  $layout_sql='drop table if exists glayout_'.$get_tosave;
  putdata($layout_sql);
  $layout_sql='create table glayout_'.$get_tosave.' select * from glayout';
  putdata($layout_sql);
  $layout_sql='drop table if exists layout_'.$get_tosave;
  putdata($layout_sql);
  $layout_sql='create table layout_'.$get_tosave.' select * from layout';
  putdata($layout_sql);
  $toSaveArr=explode('_',$get_tosave);
  $alerMsg="#notic#".$user."#グループとホストの現用レイアウトが".$toSaveArr[1]."へ保存されました";
  $nextpage="ShowLayout.php";
  branch($nextpage,$alerMsg);
  ///
}else if(!empty($get_layout) && $get_tosave==''){
  ///  保存先が無い場合は、指定レイアウトを現用にするか、削除か
  if(empty($_GET['dele'])){ 
    /// 指定レイアウトを現用へ
    $get_layout=$_GET['terms'];
    $layout_sql='drop table if exists glayout';
    putdata($layout_sql);
    $layout_sql='create table glayout select * from g'.$get_layout;
    putdata($layout_sql);
    $layout_sql='drop table if exists layout';
    putdata($layout_sql);
    $layout_sql='create table layout select * from '.$get_layout;
    putdata($layout_sql);
    $layoutArr=explode('_',$get_layout);
    /// $get_layout.'からlayoutへ読込みレイアウト名を取得し下記表示<br>';
    $alerMsg="#notic#".$user."#レイアウト".$layoutArr[1]."のグループとホストのレイアウトが現用になりました";
    $nextpage="ShowLayout.php";
    branch($nextpage,$alerMsg);
    ///
  }else{
    /// 指定レイアウトを削除
    $get_layout=$_GET['terms']; /// termsからlayout_xxが来るので、groupの場合はgをつけglayoutにする
    $layout_sql='drop table if exists g'.$get_layout;
    putdata($layout_sql);
    $layout_sql='drop table if exists '.$get_layout;
    putdata($layout_sql);
    $layoutArr=explode('_',$get_layout);
    /// $loadlay.'からlayoutへ読込みレイアウト名を取得し下記表示<br>';
    $alerMsg="#notic#".$user."#レイアウト".$layoutArr[1]."のグループとホストのレイアウトが削除されました";
    $nextpage="ShowLayout.php";
    branch($nextpage,$alerMsg);
    ///
  }
}
if($alerMsg!=''){
  print "<h4><font color=red>{$alerMsg}</font></h4>";
}
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body></html>';
?>

