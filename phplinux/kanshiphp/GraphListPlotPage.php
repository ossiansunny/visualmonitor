<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";
require_once "mailsendany.php";

$dirSep='';
$vpath_kanshiphp="";
///
function mrtgcfgck($_host){
  global $dirSep, $vpath_kanshiphp;
  $files = glob($vpath_kanshiphp.$dirSep."mrtgcfg".$dirSep.$_host.".*");
  if (empty($files)){
    return 1; //グラフ未作成
  }else{
    return 0; //グラフ作成中
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
$vpath_kanshiphp="";
$graphStatus="";
$dis="";

if(!isset($_GET['param'])){
  paramGet($pgm);
  ///
}else{
  paramSet();
  ///
  $vpathParam=array("vpath_kanshiphp");
  $rtnPath=pathget($vpathParam);
  $vpath_kanshiphp=$rtnPath[0];
  
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
  $host_sql="select * from host order by groupname";
  $hostRows = getdata($host_sql);
  $recCount = count($hostRows);
  $submitSw=0;
  $active="";
  print '<form name="rform" method="get" action="viewgraphplot.php">';
  print '<table><tr><th></th><th width=100>ホスト</th><th>グラフ種類</th><th>表示名</th><th>状態</th></tr>';
  for($i=0;$i<$recCount;$i++){
    $hostArr = explode(',',$hostRows[$i]);
    if($hostArr[4]=="2" or $hostArr[4]=="3" ){ // snmp監視対象ホストチェック action="2" snmp
      $color="colorred"; 
      if($hostArr[3]=="1"){$active="グラフ作成中";$dis="";$color="colorgreen";} /// result == 1 active 
      if($hostArr[3]!="1"){$active="非稼働";$dis="disabled";}                   /// result != 1 not active
      $graphStatus=mrtgcfgck($hostArr[0]);                     /// mrtg登録あり=0 なし=1
      if ($graphStatus==1){$active='グラフ未登録';$dis='disabled';}             /// 
      if($hostArr[8]!="" || $hostArr[9]!="" || $hostArr[10]!="") {              /// cpu ram disk あり
        $graphType="";
        if($hostArr[8]!=""){$graphType="CPU";}
        if($hostArr[9]!=""){$graphType=$graphType . ";" . "RAM";}
        if($hostArr[10]!=""){$graphType=$graphType . ";" . "Disk";}
        $graphType=trim($graphType,';');
        print "<tr><td><input type=radio name=fradio value={$hostRows[$i]} {$dis}></td>";
        print "<td><input type=text name=host value={$hostArr[0]}></td>";
        print "<td><input type=text name=graphtype value={$graphType}></td>";
        print "<td><input type=text name=viewname value={$hostArr[5]}></td>";
        print "<td><input type=text name=active value={$active}></td></tr>";
        print "<input type=hidden name=user value={$user}>";
        $submitSw=1; 
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

