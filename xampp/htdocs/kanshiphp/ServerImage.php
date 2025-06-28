<?php
require_once 'BaseFunction.php';
require_once 'mysqlkanshi.php';
require_once 'serverimagedisplay.php';
require_once 'varread.php';

$pgm="ServerImage.php";
$user="";
$brcode="";
$brmsg="";

if (!isset($_GET['param'])){
  paramGet($pgm);
  ///
}else{
  paramSet();
  $user_sql="select authority,bgcolor from user where userid='".$user."'";
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    branch('logout.php',$msg);
  }
  $vpathParam=array('vpath_kanshiphp');
  $vpathArr=pathget($vpathParam);
  $hostImageDir=$vpathArr[0].'/hostimage';
  $userArr=explode(',',$userRows[0]);
  $authority=$userArr[0];
  $bgColor=$userArr[1];
  ///
  print '<html><head><meta>';
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';
  print "</head><body class={$bgColor}>";
  if ($brcode=='error' or $brcode=='notic' or $brcode=='alert'){
    print "<h4 class={$brcode}>{$brmsg}</h4><hr>";
  }
  print '<h2><img src="header/php.jpg" width="30" height="30">▽　サーバー画像管理　▽</h2>';
  print '<h3>モニターマネージャで表示するサーバ画像の登録、削除</h3>';
  print '<h4>☆画像名はpngファイルのみ許容、詳細はマニュアル参照<br>';
  print '☆画像名の重複は不可<br>';
  print '☆画像名、サーバー名の変更は不可、削除して新たなメニューで登録する</h4>';
  /// ホスト画像表示
  $image_sql="select * from serverimage order by image";
  $imageRows=getdata($image_sql);
  if (empty($imageRows)){
    print "<h4 class='alert'>画像がありません、登録して下さい</h4><hr>";
  }
  hostimagelist($imageRows,2);
  print '<br>';
  ///
  print '<h4>削除を選択して、<span class=trred>「削除実行」</span>をクリック</h4>';
  print '<form  type="get" action="serverimageinsdeldb.php">';
  print '<table border=1>';
  print '<tr><th>削除</th><th width="150">画像名</th><th width="248">サーバー名</th></tr>';
  
  $isSw=0;
  $imageArr=array();
  foreach ($imageRows as $imageRowsRec){
    $isSw=1;
    $imageArr = explode(',',$imageRowsRec);
    print '<tr>';
    print "<td><input type='checkbox' name='fckbox[]' value={$imageArr[0]}></td>";
    print "<td><input type=text name=image size=20 value={$imageArr[0]}></td>";
    print "<td><input type=text name=name size=40 value={$imageArr[1]}></td>";
    print '</tr>';
  }
  if ($isSw==0){
    print '<tr>';
    print '<td><input type=text name=dummy size=1 value=""></td>';
    print '<td><input type=text name=image size=20 value="No data"></td>';
    print '<td><input type=text name=name size=40 value="No data"></td>';
    print '</tr>';
    print '</table>';
  }else{
    print '</table>';
    print "<input type=hidden name=user value={$user}>";
    print '<br>&emsp;<input class=buttondel type="submit" name="del" value="削除実行">';
  }
  print '<br>';
  print '</form><hr>';
  $fileArray=array();
  $files=glob($hostImageDir."/*1.png");
  foreach($files as $file){
    
    $fileNameOk=str_replace('1','',basename($file));
    $bypassSw=0;
    foreach($imageRows as $imageRowsRec){
      $imageRowsRecArr=explode(',',$imageRowsRec);
      if($fileNameOk==$imageRowsRecArr[0]){
        $bypassSw=1;
        continue;
      }
    }
    if($bypassSw==0){
      array_push($fileArray,$fileNameOk);
    }
  }
  hostimagelist($fileArray,1);
  print '<h4>新たに登録するサーバー画像の画像名を選択、サーバー名を入力し<span class=trblk>「登録実行」</span>をクリック</h4>';
  print '<form type="get" action="serverimageinsdeldb.php">';
  print '<table border=1>';
  print '<tr><th>画像名</th><th>サーバー名</th></tr>';
  
  print '<tr>';
  //print '<td><input type=text name=image value="" size=20></td>';
  print '<td><select name=image>';
  foreach($fileArray as $imageName){
    print "<option value-{$imageName}>{$imageName}</option>";
  }
  print '</select></td>';
  print '<td><input type=text name=name size=40 value=""></td>';
  print '</tr>';
  print '</table>';
  print "<input type=hidden name=user value={$user}>";
  print '<br>&emsp;<input class=button type="submit" name="ins" value="登録実行">';
  print '</form>';
  print '<br>';
  
  print "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
  print '</body>';
  print '</html>';
}
?>

