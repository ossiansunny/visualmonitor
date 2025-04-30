<?php
require_once 'varread.php';
require_once 'mysqlkanshi.php';
require_once 'BaseFunction.php';

function mrtgck(){
  $exists='0';
  $mrtgBinPath='';
  $vpathParam=array("vpath_mrtg");
  $rtnPath=pathget($vpathParam);
  $mrtgPath=$rtnPath[0];
  if(! file_exists($mrtgPath)){
    $exists='1';    
  }
  return $exists;
}
function plotck(){
  $exists='0';
  $plotBinPath='';
  $vpathParam=array("vpath_gnuplot");
  $rtnPath=pathget($vpathParam);
  if(!count($rtnPath)==1){
    $exists='1';
  }else{
    if(! file_exists($rtnPath[0])){      
      $exists='1';
    }
  }
  return $exists;
}
function mailck(){
  $exists='0';
  $srcPath='';
  $vpathParam=array("vpath_phpmailer");
  $rtnPath=pathget($vpathParam);
  if(!count($rtnPath)==1){
    $exists='1';
  }else{
    $srcPath=$rtnPath[0].'/src';      
    if(! file_exists($srcPath)){    
      $exists='1';
    }
  }
  return $exists;
}
$pgm="MenuPage.php";
$user="";
$brcode="";
$brmsg="";
///
if(!isset($_GET['param'])){
  paramGet($pgm);
  ///
}else{
  paramSet();
  ///
  $user_sql='select authority,bgcolor from user where userid="'.$user.'"';
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    branch('logout.php',$msg);
  }
  $userArr=explode(',',$userRows[0]);
  $auth=$userArr[0];
  $bgColor=$userArr[1];

  print '<html>';
  print '<head>';
  print "<meta http-equiv='Refresh' content=30>";
  print '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
  print '<link rel="stylesheet" href="css/MenuPage.css">';
  print '</head>';
  print "<body class='".$bgColor."'>";
  print '<div id="body">';
  print '<hr id="bar">';
  //print '<body class=bgtower>';
  //print '<div><hr>';
  print '<div class="sidebar" id="left">';
  print '<h2><img src="./header/alerm1mini.png"> アラート表示</h2>';
  print '<iframe id="mesg" src="Messages.php" width="170" height="60">message</iframe>';
  print '<hr id="bar">';
  print '<h2>▼　表示メニュー</h2>';
  print '<ul>';
        print '<li><a href="MonitorManager.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;監視モニター</span></a></li>';
        print '<li><a href="EventLogPage.php" target="sframe""><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;イベントログ</span></a></li>';
        print '<li><a href="EventMemoPage.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;イベントメモ</span></a></li>';
        print '<li><a href="SnmpStatPage.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;SNMP監視結果</span></a></li>';
        $ismrtg=mrtgck();
        if ($ismrtg=='0'){
          print '<li><a href="GraphListPage.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;リソースグラフ</span></a></li>';
        }else{
          print '<li><a><img src="header/php.jpg" class="pysize"><span class="dmy">&ensp;リソースグラフ</span></a></li>';
        }
        $isplot=plotck();
        if ($isplot=='0'){
          print '<li><a href="GraphListPlotPage.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;プロットグラフ</span></a></li>';
        }else{
          print '<li><a><img src="header/php.jpg" class="pysize"><span class="dmy">&ensp;プロットグラフ</span></a></li>';
        }
  print '</ul>';
  print '<h2>▼　設定メニュー</h2>';
  print '<ul >';
        print '<li><a href="NewHostPage.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;監視ホスト追加</span></a></li>';
        print '<li><a href="HostListPage.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;監視ホスト修正</span></a></li>';
        print '<li><a href="AdminPage.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;管理情報</span></a></li>';
        print '<li><a href="HeaderEditPage.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;ヘッダ情報</span></a></li>';
        print '<li><a href="ServerImage.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;サーバー画像管理</span></a></li>';
  print '</ul>';
  print '<h2>▼　レイアウトメニュー</h2>';
  print '<ul >';
         print '<li><a href="SelectLayout.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;ホスト変更</span></a></li>';
         print '<li><a href="ShowLayout.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;保存・読み込み・削除</span></a></li>';
         print '<li><a href="LayoutGroup1.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;グループ作成</span></a></li>';
         print '<li><a href="LayoutHost1.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;ホスト作成</span></a></li>';
  print '</ul>';
  print '<h2>▼　サポートメニュー</h2>';
  print '<ul >';
         print '<li><a href="ReadLogPage.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;監視ログ</a></li>';
         print '<li><a href="CoreRestart.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;コア再起動</a></li>';
         $ismail=mailck();
         if ($ismail=='0'){
           print '<li><a href="PHPMailSend.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;メール設定・送信</a></li>';
         }else{
           print '<li><a><img src="header/php.jpg" class="pysize"><span class="dmy">&ensp;メール設定・送信</a></li>';
         }
         print '<li><a href="WebErrorLog.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;Webエラーログ</a></li>';
         $isplot=plotck();
         if ($isplot=='0'){        
           print '<li><a href="PlotLog.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;プロットログ</a></li>';
         }else{
           print '<li><a><img src="header/php.jpg" class="pysize"><span class="dmy">&ensp;プロットログ</a></li>';
         }
         print '<li><a href="LogClear.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;ログ削除</a></li>';
         print '<li><a href="ManualPage.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;マニュアル</a></li>';
         print '<li><a href="UserPage.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;ユーザー管理</a></li>';
         print '<li><a href="HistoryPage.php" target="sframe"><img src="header/php.jpg" class="pysize"><span class="bgc">&ensp;覚え書き管理</a></li>';
  print '</ul>';
  print '</div>';

  print '</body>';
  print '</html>';
}
?>
