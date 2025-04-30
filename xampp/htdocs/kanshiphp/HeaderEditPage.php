<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
///
$pgm='HeaderEditPage.php';
$user="";
$brcode="";
$brmsg="";
///
if (!isset($_GET['param'])){
  paramGet($pgm);
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
///
  if($brcode=="error" or $brcode=="notic" or $brcode=="alert"){
    print "<h4 class={$brcode}>{$brmsg}</h4><hr>";
  }
///
  $head_sql="select * from header";
  $headRows=getdata($head_sql);
  $headArr=explode(',',$headRows[0]);
  $title = $headArr[0]; ///host
  $subTitle=$headArr[1];
  $lnkTitle1=$headArr[2];
  $lnkTitle2=$headArr[3];
  $lnkTitle3=$headArr[4];
  $lnkTtile4=$headArr[5];
  $lnkTitle5='';
  $lnkUrl1=$headArr[7];
  $lnkUrl2=$headArr[8];
  $lnkUrl3=$headArr[9];
  $lnkUrl4=$headArr[10];
  $lnkUrl5='';
  $imageSel=$headArr[12];
  $imageMax=$headArr[13];
  print '<h2><img src="header/php.jpg" width="30" height="30">&ensp;&ensp;▽　ヘッダー情報更新　▽</h2>';
  print '<h3>☆ヘッダ画像一覧</h3>';
    //print '<table boder=1>';
    //print '<tr><th>無音</th><th>警告音１</th><th>警告音２</th><th>警告音３</th><th>警告音４</th></tr>';
    //print '<tr>';
    print '<table>';
    $iImage=1;
    $jImage=1;
    $iMax=$imageMax;
    $jMax=$imageMax;
    if ($imageMax > 8 ) {
      $imageNum=8;
    }else{
      $imageNum=$imageMax;
    }
    while (! $iMax==0){
      $i=1;
      $j=1;
      print '<tr>';
      while ($i < $imageNum){
        print "<th>header{$iImage}</th>";
        $i++;
        $iImage++;
        $iMax--;
        if ($iMax == 0){
          break;
        }
      }
      print '</tr>';
      
      print '<tr>';
      while ($j < $imageNum){
        print "<td><img src=header/header{$jImage}.jpg width=120px height=30px></td>";
        $j++;
        $jImage++;
        $jMax--;
        if ($jMax == 0){
          break;
        }
      }
      print '</tr>';
    }
    print '</table>';
    
    print '<hr>';
  print '<h3>必要な項目を修正し、<span class=trblk>「更新実行」</span>をクリック</h3>';
  print '<h4>☆リンク名はURLをhttp://又はhttps://のように入力　例：他の監視サイト http://mysite.com/kanshiphp/login.php<br>';
  print '☆タイトル、サブタイトルはモニターマネージャ、メールの送信元で使用<br>';
  print '☆ヘッダ画像は一覧をリフレッシュ毎に自動変更するので入力不要</h4>';
  print '<form name="headeredit" type="get" action="headerupdb.php">';
  print '<table border=1>';
  print '<tr><th colspan=2>タイトル</th><th colspan=2>サブタイトル</th></tr>';
  print '<tr>';
  print '<td colspan=2><input type=text name=title size=56 value="'.$title.'" ></td>';
  print '<td colspan=2><input type=text name=subtitle size=56 value="'.$subTitle.'"></td>';
  print '</tr>';
///
  
  print '<tr><th>リンク名１</th><th>リンク名２</th><th>リンク名３</th><th>リンク名４</th></tr>';
  print '<tr>';
  print '<td><input type=text name=lnkttl1 size=25 value='.$lnkTitle1.'></td>';
  print '<td><input type=text name=lnkttl2 size=25 value='.$lnkTitle2.'></td>';
  print '<td><input type=text name=lnkttl3 size=25 value='.$lnkTille3.'></td>';
  print '<td><input type=text name=lnkttl4 size=25 value='.$lnkTitle4.'></td>';
  print '</tr>';
  print '<tr><th>リンクURL１</th><th>リンクURL２</th><th>リンクURL３</th><th>リンクURL４</th></tr>';
  print '<tr>';
  print '<td><input type=text name=lnkurl1 size=25 value='.$lnkUrl1.'></td>';
  print '<td><input type=text name=lnkurl2 size=25 value='.$lnkUrl2.'></td>';
  print '<td><input type=text name=lnkurl3 size=25 value='.$lnkUrl3.'></td>';
  print '<td><input type=text name=lnkurl4 size=25 value='.$lnkUrl4.'></td>';
  print '</tr>';

  print '</table>';
  print '<br>';
  print '<input type=hidden name=user value="'.$user.'">';
  print '&emsp;<input class=button type="submit" name="up" value="更新実行">';
  print '</form>';
  print '<br>';
  
  print '&emsp;<a href="MonitorManager.php?param='.$user.'"><span class=buttonyell>監視モニターへ戻る</span></a>';
  print '</body>';
  print '</html>';
}
?>

