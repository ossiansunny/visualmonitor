<?php
require_once "BaseFunction.php";
///
$pgm='LayoutHost1.php';
$user="";
$brcode="";
$brmsg="";
///
if (!isset($_GET['param'])){
  paramGet($pgm);
  
}else{  
  /// 引数情報の分解  param=#<code>#<user>#<message>　または param=<user>
  paramSet(); 
  $user_sql="select authority,bgcolor from user where userid='".$user."'";
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    branch('logout.php',$msg);
  }
  $userArr=explode(',',$userRows[0]);
  $authority=$userArr[0];
  $bgColor=$userArr[1]; 
  ///
  print '<html lang="ja">';
  print '<head>';
  print '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';
  print "</head><body class={$bgColor}>";
  ///
  if ($brcode=='error' or $brcode=='notic' or $brcode=='alert'){
    print "<h4 class={$brcode}>{$brmsg}</h4><hr>";
  }
  ///
  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　ホストレイアウト作成　その１　▽</h2>';
  print '<h3>☆新たなレイアウトにホストデータを配置<br>';
  print '☆グループで作成したレイアウト略称を選択し<span class="trblk">「選択実行」</span>をクリック</h3>';
  print '<br>';
  print '<form name=myform action=layouthost2.php method=get>'; 
  print '<table border=1>';
  print '<tr><th>レイアウト名称</th></tr>';
   
  $show_sql='show tables like "glayout%"';
  $showRows=getdata($show_sql);
  print '<tr><td><select name=laynick>';
  foreach ($showRows as $showRowsRec){
    $layout=explode('_',$showRowsRec);
    if(! is_null($layout[1])){
      print "<option value={$layout[1]}>{$layout[1]}</option>";
    }
  }
  print '</select></td></tr>';
   
  print '</table>';
  print "<input type=hidden name=user value={$user}>";
  print '<br>';
  print '&ensp;<input class=button type=submit name=exe value=選択実行>';
  print '</form>'; 
  print '<br><br>';
}
print "&ensp;<a href='MonitorManager.php}param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body>';
print '</html>';
?>

