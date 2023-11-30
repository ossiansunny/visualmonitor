<?php
require_once 'mysqlkanshi.php';
error_reporting(E_ERROR | E_PARSE);

function branch($_page,$_param){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo "<form name='F' action={$_page} method='get'>";
  echo "<input type=hidden name=param value={$_param}>";
  echo '<input type="submit" name="next" value="Waiting...">';
  echo '</form>';
}

echo '<html><body><head>';
echo '<link rel="stylesheet" href="kanshi1_py.css">';
echo '</head><body>';
echo '<h2>▽　レイアウト処理結果　▽</h2>';
echo '<br>';

$msg='';
$terms=$_GET['terms'];
$tosave=$_GET['tosave'];
$user=$_GET['user'];

//$debug_alert = "<script type='text/javascript'>alert('一時停止');</script>";
//echo $debug_alert;

///
if(empty($terms)){
  $msg='#error#".$user."#レイアウトの「選択」がされていません';
  $nextpage="ShowLayout.php";
  branch($nextpage,$msg);
  exit; 
}else{
  $sql="select dataflag from g".$terms;
  $rows=getdata($sql);
  foreach ($rows as $lflag){
    if ($lflag == "0"){
      $msg='#error#".$user."#レイアウトが完成されていません';
      $nextpage="ShowLayout.php";
      branch($nextpage,$msg);
      exit;
    }
  }
}

if($terms=='layout' && $tosave==''){  
  $msg='#error#".$user."#レイアウト保存の「保存先」がありません'; 
  $nextpage="ShowLayout.php";
  branch($nextpage,$msg);
  exit;  
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
  echo "<h4>のグループとホストの現用レイアウトが {$tosavearr[1]} へ保存されました</h4>";
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
    echo "<h4>レイアウト {$loadlayarr[1]} のグループとホストのレイアウトが現用になりました</h4>";
    
  }else{
    /// 指定レイアウトを削除
    $dellay=$_GET['terms']; // termsからlayout_xxが来るので、groupの場合はgをつけglayoutにする
    $gdrsql='drop table if exists g'.$dellay;
    $sdrsql='drop table if exists '.$dellay;
    putdata($gdrsql);
    putdata($sdrsql);
    $dellayarr=explode('_',$dellay);
    /// $loadlay.'からlayoutへ読込みレイアウト名を取得し下記表示<br>';
    echo "<h4>レイアウト {$dellayarr[1]} のグループとホストのレイアウトが削除されました</h4>";
    
  }
}

/*
レイアウト保存時
tosave value: custom2
toload value: current
terms value: layout
レイアウト読込時
tosave value:
toload value: current
terms value: layout_map3

//layout_xxからayoutを作る
drop table if exists glayout;
create table glayout select * from glyaout_xx;
drop table if exists layout;
create table layout select * from lyaout_xx;

//layoutからlayout_xxへ保存
drop table if exists glayout_xx;
create table glayout_xx select * from glayout;
drop table if exists layout_xx;
create table layout_xx select * from layout;
*/
if($msg!=''){
  echo "<h4><font color=red>{$msg}</font></h4>";
}

echo "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
echo '</body></html>';
?>
