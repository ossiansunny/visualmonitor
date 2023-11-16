<?php

//error_reporting(E_ALL & ~E_NOTICE);
require_once "mysqlkanshi.php";
//
$pgm = "Reset.php";
//
date_default_timezone_set('Asia/Tokyo');


function branch($page,$param){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo "<form name='F' action={$page} method='get'>";
  echo "<input type=hidden name=param value={$param}>";
  echo '<input type="submit" name="next" value="Waiting...">';
  echo '</form>';
}
$esw=0;
$errorcde="";
$errormsg="";
/// get admin data
$adminsql='select * from admintb';
$arows=getdata($adminsql);
$adata=explode(',',$arows[0]);
$reset=$adata[0]; // AdminPageで初期化すると'reset'、デフォルトはNULL
if (isset($_GET['param'])){
  writelogd($pgm,$_GET['patam']);
  $inform=$_GET['param'];   /// branchで戻った時の処理
  if (substr($inform,0,1)=="#") {
    $brarr=explode("#",ltrim($inform,"#"),3);
    $errorcde=$brarr[0];
    $errormsg=$brarr[1];
    writelogd($pgm,$errormsg);
    if ($errorcde=='alert'){
      $esw=1;
    }
  } else {
    $errorcde="";
    $errormsg=$_GET['param'];
    writelogd($pgm,$errormsg);
    $esw=1;
  }
}else{
  writelogd($pgm,$_GET['user']);
  if (isset($_GET['user'])){
    $user=$_GET['user'];
    $passwd=$_GET['passwd'];
    if ($user=='admin' && $passwd=='manager'){
      if (isset($_GET['reset'])){  /// reset ボタン押した時の処理
        if ($reset=='reset'){ // admintbに'reset登録済か
          echo "Initialize";
          $upsql='update admintb set kanriname=null';
          putdata($upsql);
          $msg='VisualMonitorが初期化されました、これを閉じてログインして下さい';
          writeloge($pgm,$msg);
          branch('reset.php',"#notic#".$msg);
          echo '</body></html>';
        }else{
          $esw=1;
          $msg='admintbに初期化設定していません';
          writelogd($pgm,$msg);
          branch('reset.php',"#alert#".$msg);
          echo '</body></html>';
        }
      }
    }else{
      $esw=1;
      $msg='管理者IDかパスワードが違います';
      writelogd($pgm,$msg);
      branch('reset.php',"#alert#".$msg);
      echo '</body></html>';
    }
  }
}
/// 最初の処理
//echo 'Content-type: text/html; charset=UTF-8\n';
echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<meta charset="utf-8">';
echo '<title>サンプル</title>';
echo '<link rel="stylesheet" href="reset.css">';
echo '</head>';
echo '<body>';
echo '<div class="reset">';
echo '<div class="reset-triangle"></div>';
echo '<h2 class="reset-header"><img src="header/php.jpg" width="70" height="70">&emsp;&emsp;監視モニター初期化</h2>';
echo '<form class="reset-container" type="get" action="reset.php">';
echo '<p><input type="text" name="user" value="" placeholder="管理者ID" required></p>';
echo '<p><input type="password" name="passwd" placeholder="パスワード" required></p>';
echo "<input type='hidden' name='errorcde' value={$ercde}>";
echo '<p><input type="submit" name="reset" value="リセット"></p>';
echo '</form>';
echo '</div>';
echo '<div class="reset">';
///

if ($esw == 1){
  echo "<div><h4><font color=red>{$errormsg}</font></h4></div>";
}else if ($esw == 2){
  echo "<div><h4><font color=yellow>{$errormsg}</font></h4></div>";
}else {
  echo "<div><h4><font color=white>{$errormsg}</font></h4></div>";
}
echo '</div>';
echo '</body>';
echo '</html>';
?>
