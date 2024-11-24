<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";
require_once "mailsendany.php";

$dirSep='';
$vpath_kanshiphp="";
///
function plotsvgck($_host){
  global $dirSep, $vpath_kanshiphp;
  $files = glob($vpath_plothome.$dirSep."plot".$dirSep."plotimage".$_host.".svg");
  if (empty($files)){
    return 1; ///グラフなし
  }else{
    return 0; ///グラフあり
  }
}
///
if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
  /// windows xampp
  $dirSep='\\';
}else{
  /// linux
  $dirSep='/';
}

$user = "";
$brcode="";
$brmsg="";
$pgm="GraphListPlotPage.php";
$vpath_plothome="";
$graphStatus="";
$dis="";

if(!isset($_GET['param'])){
  paramGet($pgm);
  ///
}else{
  paramSet();
  ///
  $vpathParam=array("vpath_plothome");
  $rtnPath=pathget($vpathParam);
  $vpath_plothome=$rtnPath[0];
  
  ///
  $title1='<img src="header/php.jpg" width="30" height="30">';
  $title2='　▽　プロットグラフホスト一覧　▽　';
  $title=$title1.$title2;
  print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
  print '<html lang="ja">';
  print '<head>';
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';
  print '<title>Host List</title>';
  print '</head>';
  print '<body>';
  if ($brcode=="error" or $brcode=="alert" or $brcode=="notic"){
    print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }
  print "<h2>{$title}</h2>";
  print "<h4>☆ホストを１つ選択して「グラフ表示／メール添付」をクリックする<br>";
  print "☆グラフのメール添付には、ホストのメール「自動送信」が必要</h4>";
  /// host[0] groupname[1] ostype[2] result[3] action[4] viewname[5] mailopt[6] ...
  $layout_sql="select host from layout";
  $layoutRows=getdata($layout_sql);
  print '<form name="rform" method="get" action="viewgraphplot.php">';
  print '<table><tr><th></th><th width=100>ホスト</th><th>グラフ種類</th><th>表示名</th><th>状態</th></tr>';
  $submitSw=0;
  $active="";
  foreach ($layoutRows as $layoutRowsRec){
    if (!($layoutRowsRec=='' or $layoutRowsRec=='NoAssign')){
      $fileName=$vpath_plothome.$dirSep."plotimage".$dirSep.$layoutRowsRec.".svg";
      if (file_exists($fileName)) {
        $active="グラフ取得可能";
        $dis="";
        $color="colorgreen";
        $host_sql="select * from host where host='".$layoutRowsRec."'";
        $hostRows=getdata($host_sql);
        if (isset($hostRows)){
          $hostArr=explode(',',$hostRows[0]);
          if (!($hostArr[8]=="" or $hostArr[9]=="" or $hostArr[10]=="")) { /// cpu ram disk あり
            $graphType="";
            if($hostArr[8]!=""){$graphType="CPU";}
            if($hostArr[9]!=""){$graphType=$graphType . ";" . "RAM";}
            if($hostArr[10]!=""){$graphType=$graphType . ";" . "Disk";}
            $graphType=trim($graphType,';');
            print "<tr><td><input type=radio name=fradio value={$hostRows[0]} {$dis}></td>";
            print "<td><input type=text name=host value={$hostArr[0]}></td>";
            print "<td><input type=text name=graphtype value={$graphType}></td>";
            print "<td><input type=text name=viewname value={$hostArr[5]}></td>";
            print "<td><input class={$color} type=text name=active value={$active}></td></tr>";
            print "<input type=hidden name=user value={$user}>";
            $submitSw=1;
          }
        } 
      }
    }
  }
  if ($submitSw==1){
    print '<tr><td><br></td></tr>';
    print '<tr><td colspan=2 align=center>&emsp;<input class=button type="submit" name="display" value="グラフ表示/メール添付" ></td></tr>';
  }else{
    $message="snmp監視対象ホストがありません";
    $msg="#error#".$user."#".$message;
    $nextpage=$pgm;    
    print "<h4><span class=buttonyell>{$message}</h4><hr>";
  }  
  print "</table>";
  print "</form>";
}
print '<h4>☆監視対象ホストはリソースグラフで作られる</h4>';    
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell> 監視モニターへ戻る</span></a>";
print '</body></html>';
?>

