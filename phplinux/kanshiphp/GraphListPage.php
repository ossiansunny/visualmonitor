<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";
$brcode=""; /// 通知コード
$user=""; /// ユーザID
$brmsg=""; ///メッセージ $user = "";
$pgm="GraphListPage.php";
$vpath_kanshiphp="";
$osDirSep='';
$exists='0';
///
$vpathParam=array("vpath_kanshiphp","vpath_mrtgbase");
$rtnPath=pathget($vpathParam);
$vpath_kanshiphp=$rtnPath[0];
$mrtgPath=$rtnPath[1];
///

///
function mrtgcfgck($_host){
  global $vpath_kanshiphp, $osDirSep;
  $files = glob($vpath_kanshiphp.$osDirSep."mrtgcfg".$osDirSep.$_host.".*");
  if (empty($files)){
    return 1;
  }else{
    return 0;
  }
}
///
if(!isset($_GET['param'])){
  paramGet($pgm);
  ///
}else{
  paramSet();
  ///
  $mrtgBinPath='';
  if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
    $osDirSep='\\';
    $mrtgBinPath=$mrtgPath.'\\bin\\mrtg';
    //echo $mrtgBinPath.'<br>';
  }else{
    $osDirSep='/';
    $mrtgBinPath='/usr/bin/mrtg';
    //echo $mrtgBinPath.'<br>';
  }
  if(! file_exists($mrtgBinPath)){
    //echo 'not found<br>';
    print '<html><body>';
    print '<h4><br>Mrtgが見つかりません、インストール済か初期設定をチェックして下さい</h4>';
    $exists='1';
    
  }
  
  
///
  $title1='<img src="header/php.jpg" width="30" height="30">';
  $title2='　▽　グラフホスト一覧　▽　';
  $title=$title1.$title2;
  print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
  print '<html lang="ja">';
  print '<head>';
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';
  print '<title>Host List</title>';
  print '</head>';
  print '<body>';
  ///
  if ($brcode=="error" or $brcode=="alert" or $brcode=="notic"){
    print "<h3 class={$brcde}>{$brmsg}</h3><hr>";
  }
  ///
  if ($exists=='0'){
    print "<h2>{$title}</h2>";
    ///
    ///---SNMP監視対象一覧を表示---
    ///
    print "<h4>☆ホストを１つ選択して「グラフ表示/メール添付」「グラフ登録」「グラフ削除」のいずれかをクリックする<br>";
    print "☆グラフのメール添付には、ホストのメール「自動送信」が必要です</h4>";
    ///
    $layout_sql="select host from layout";
    $layoutRows=getdata($layout_sql);
    print '<form name="rform" method="get" action="viewgraph.php">';
    print '<table><tr><th></th><th width=100>ホスト</th><th>グラフ種類</th><th>表示名</th><th>状態</th></tr>';
    $exeSw=0;
    $active="";
    $disable="";
    foreach ($layoutRows as $layoutRowsRec){
      if (!($layoutRowsRec=='' or $layoutRowsRec=='NoAssign')){
        $host_sql="select * from host where host='".$layoutRowsRec."'";
        $hostRows=getdata($host_sql);
        if (isset($hostRows)){
          $hostArr=explode(',',$hostRows[0]);
          if($hostArr[4]=="2" or $hostArr[4]=="3"){ /// snmp監視 snmp監視通知なし
            $color="colorred"; 
            if($hostArr[3]=="1"){$active="グラフ登録済";$disable="";$color="colorgreen";}
            if($hostArr[3]!="1"){$active="非稼働";$disable="";}
            $cfgrtn=mrtgcfgck($hostArr[0]);
            if($cfgrtn==1){$active="グラフ未登録";$disable="";$color="colorred";}
            if($hostArr[8]!="" || $hostArr[9]!="" || $hostArr[10]!="") {
              $graphType="";
              if($hostArr[8]!=""){$graphType="CPU";}
              if($hostArr[9]!=""){$graphType=$graphType . ";" . "RAM";}
              if($hostArr[10]!=""){$graphType=$graphType . ";" . "Disk";}
              $graphType=trim($graphType,';');
              print "<tr><td><input type=radio name=fradio value={$hostRows[0]} {$disable}></td>";
              print "<td><input type=text name=host value={$hostArr[0]}></td>";
              print "<td><input type=text name=graphtype value={$graphType}></td>";
              print "<td><input type=text name=viewname value={$hostArr[5]}></td>";
              print "<td><input class={$color} type=text name=active value={$active}></font></td></tr>";
              print "<input type=hidden name=user value={$user}>";
              $exeSw=1;
            }
          }
        }
      }
    }


    if ($exeSw==1){    
      $user_sql='select authority from user where userid="'.$user.'"';
      $userRows=getdata($user_sql);
      if(empty($userRows)){
        $msg="#error#"."NonUser"."#ユーザがありません、再ログインして下さい";
        $nextpage=$pgm;    
        branch($nextpage,$msg);
      }
      $userArr=explode(',',$userRows[0]);
      $authority=$userArr[0];
      print '<tr><td><br></td></tr>';
      ///
      print '<input type="submit" style="display:none">';
      print '<tr><td colspan=2 align=center>&emsp;<input class=button type="button" name="display" onclick="func1(\'viewgraph\')" value="グラフ表示/メール添付"></td>';
    
      if ($authority=='1'){
        print '<input type="submit" style="display:none">';
        print '<td colspan=2 align=center>&emsp;<input class=buttonyell type="button" name="create" onclick="func1(\'graphcreate\')" value="グラフ登録"></td>';
        print '<input type="submit" style="display:none">';
        print '<td colspan=2 align=center>&emsp;<input class=buttondel type="button" name="delete" onclick="func1(\'graphdelete\')" value="グラフ削除"></td>';
      }
      print '</tr>';    
    }else{
      $msg="#error#".$user."#snmp監視対象ホストがありません";
      $nextpage=$pgm;    
      print '<h4><span class=buttonyell>グラフ監視対象ホストがありません</span></h4>';
    }
    print "</table>";
    print "</form>";
    ///
    print '<script language="javascript" type="text/javascript">';
    print 'const func1 = (page) => {';
    print 'document.rform.action = `${page}.php`;';
    print 'document.rform.submit();';
    print '};';
    print '</script>';
    ///
    print '<br>';
  }
  print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell> 監視モニターへ戻る</span></a>";
  print "</body></html>";
  
}

?>

