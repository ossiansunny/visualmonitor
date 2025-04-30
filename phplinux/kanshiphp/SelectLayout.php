<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

$pgm="SelectLayout.php";
$brcode="";
$brmsg="";
$user="";
///
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
  $userArr=explode(',',$userRows[0]);
  $authority=$userArr[0];
  $bgColor=$userArr[1];
  ///
  print '<html><head><meta>';
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';
  print "</head><body class={$bgColor}>";
  if ($brcode=='error' or $brcode=='alert' or $brcode=='notic'){
    print "<h4 class={$brcode}>{$brmsg}</h4><hr>";
  }
  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　修正レイアウト選択　▽</h2>';
  print '<h3>レイアウト名の<span class=trblk>「○」</span>で選択し、下の<span class=trblk>「選択実行」</span>をクリック<br>';
  print 'レイアウト名<span class=trylw>「現用」</span>は現在使われいるレイアウト</h3>';
  ///
  $show_sql='show tables like "layout%"';
  $showRows=getdata($show_sql);
  print '<br><table border=0 class="tablelayout"><form type=GET action="layouthostedit.php">';
  print '<tr><th align=center>レイアウト名</th></tr>';
  ///
  foreach ($showRows as $showRowsRec){ /// item ('layout',) itemlayout[0]='layout'
    $showArr=explode(',',$showRowsRec);
    print '<tr>';
    if ($showArr[0]=='layout'){
      print "<td class=trylw><span class=trblk><input type='radio' name='terms' value={$showArr[0]}></span>現用</td>";
    }else{
      $layoutName=explode('_',$showArr[0]);
      print "<td class=trylw><span class=trblk><input type='radio' name='terms' value={$showArr[0]}></span>{$layoutName[1]}</td>";
    }
    print '</tr>';
  }
  print '</table><br>';
  print "<input type=hidden name=user value={$user}>";
  print "<input type=hidden name=bgcolor value={$bgColor}>";
  print '&nbsp;&nbsp;<input type="submit" name="button" value="&nbsp;選択実行" class="button">';
  print '</form><br>';
  print "<br>&nbsp;&nbsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell> 監視モニターへ戻る</span></a>";
  print '</body></html>';
}
?>

