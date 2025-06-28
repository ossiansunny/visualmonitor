<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

$pgm='HostListPage.php';
$user='';
$brcode='';
$brmsg='';
///

if (!isset($_GET['param'])){
  paramGet($pgm);
}else{
  
  /// param引数情報の分解  
  $inform=$_GET['param'];
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
  if ($brcode=="alert" || $brcode=="error" || $brcode=="notic"){
    print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }

  print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　ホスト一覧　▽</h2>';
  ///
  ///----画面表示処理 --------------------
  ///
  print '<h3>☆更新・削除するホストの「〇」を１つ選択して<span class=trblk>「選択実行」</span>をクリック<br>';
  print '☆割り当て済：　ホストレイアウトに存在し、かつホストデータが存在する<br>';
  print '☆未割り当て：　ホストデータは存在するが、ホストレイアウトに存在しない</h3>';
  print '<form name="rform" method="get" action="hostupdel.php">';
  print '<table><tr><th width=20">選択</th><th width=100>ホスト</th><th>表示名</th><th>現行レイアウト</th></tr>';
  $layout_sql="select * from layout";
  $layoutRows=getdata($layout_sql);
  $host_sql="select host,groupname,ostype,result,action,viewname,mailopt,tcpport,cpulim,ramlim,disklim,process,image,snmpcomm,agenthost,eventlog,standby from host order by groupname";
  $hostRows=getdata($host_sql);
  foreach ($hostRows as $hostRow){  
    $hostArr = explode(',',$hostRow);
    $host = $hostArr[0];
    $assign='未割り当て';
    $assignColor="iro1";
    foreach ($layoutRows as $layoutRow){
      $layoutArr=explode(',',$layoutRow);
      if ($layoutArr[1]==$host){
        $assign='割り当て済';
        $assignColor="iro0";
        break;
      }
    }
    $viwname = $hostArr[5];
    print "<tr><td class={$assignColor}><input type=radio name=fradio value={$hostRow}></td>";
    print "<td class={$assignColor}>{$host}</td>";
    print "<td class={$assignColor}>{$viwname}</td>";
    print "<td class={$assignColor}>{$assign}</td></tr>";
  }
  print '</table><br>';
  print "<input type=hidden name=user value={$user}>";
  print '&emsp;<input class=button type="submit" name="" value="選択実行" >';
  print '</form>';
  print "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
  print '</body></html>';
  
}

?>

