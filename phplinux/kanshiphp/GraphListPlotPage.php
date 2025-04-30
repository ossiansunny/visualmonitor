<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";

$exists='0';
$user = "";
$brcode="";
$brmsg="";
$pgm="GraphListPlotPage.php";
$vpath_kanshiphp="";
$vpath_plothome="";
$graphStatus="";
$dis="";
///
function plotsvgck($_host){
  global $vpath_plothome;
  $files = glob($vpath_plothome."/plot/plotimage".$_host.".svg");
  if (empty($files)){
    return 1; ///グラフなし
  }else{
    return 0; ///グラフあり
  }
}
function mrtgcfgck($_host){
  global $vpath_kanshiphp;
  $files = glob($vpath_kanshiphp."/mrtgcfg/".$_host.".*");
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
  $vpathParam=array("vpath_kanshiphp","vpath_plothome");
  $rtnPath=pathget($vpathParam);
  $vpath_kanshiphp=$rtnPath[0];
  $vpath_plothome=$rtnPath[1];
  ///
  $gnuplot='';
  $vpathParam=array("vpath_gnuplot");
  $rtnPath=pathget($vpathParam);
  if(!count($rtnPath)==1){
    $exists='1';
  }else{
    $gnuplot=$rtnPath[0];      
    if(! file_exists($gnuplot)){      
      print '<html><body>';
      print '<h4><br>GnuPlotMrtgが見つかりません、インストール済か初期設定をチェックして下さい</h4>';
      $exists='1';
    }
  }
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
  print "<body class={$bgColor}>";
  if ($brcode=="error" or $brcode=="alert" or $brcode=="notic"){
    print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }
  ///
  if ($exists=='0'){
    print "<h2>{$title}</h2>";
    print "<h3>☆ホストを１つ選択して<span class=trblk>「グラフ表示／メール添付」</span>をクリックする<br>";
    print "☆グラフのメール添付には、ホストのメール「自動送信」が必要</h3>";
    /// host[0] groupname[1] ostype[2] result[3] action[4] viewname[5] mailopt[6] ...
    $layout_sql="select host from layout";
    $layoutRows=getdata($layout_sql);
    print '<form name="rform" method="get" action="viewgraphplot.php">';
    print '<table><tr><th></th><th width=100>ホスト</th><th>グラフ種類</th><th>表示名</th><th>状態</th></tr>';
    $submitSw=0;
    $active="";
    foreach ($layoutRows as $layoutRowsRec){
      if (!($layoutRowsRec=='' or $layoutRowsRec=='NoAssign')){
        $fileName=$vpath_plothome."/plotimage/".$layoutRowsRec.".svg";
        //echo $fileName.'<br>';
        $cfgRtn=mrtgcfgck($layoutRowsRec);
        if (file_exists($fileName) and $cfgRtn==0) {
          $active="グラフ取得可能";
          $dis="";
          $color="colorgreen";
          $host_sql="select host,groupname,ostype,result,action,viewname,mailopt,tcpport,cpulim,ramlim,disklim,process,image,snmpcomm,agenthost,eventlog,standby from host where host='".$layoutRowsRec."'";
          $hostRows=getdata($host_sql);
          if (!(empty($hostRows))){
            $hostArr=explode(',',$hostRows[0]);
            $hostName=$hostArr[0];
            $hostView=$hostArr[5];
            $hostCpu=$hostArr[8];
            $hostRam=$hostArr[9];
            $hostDisk=$hostArr[10];
            if (!($hostCpu=="" or $hostRam=="" or $hostDisk=="")) { /// cpu ram disk あり
              /// グラフタイトル作成
              $graphType="";
              if($hostCpu!=""){
                $graphType="CPU";
              }
              if($hostRam!=""){
                $graphType=$graphType . ";" . "RAM";
              }
              if($hostDisk!=""){
                $graphType=$graphType . ";" . "Disk";
              }
              $graphType=trim($graphType,';');
              ///
              print "<tr><td><input type=radio name=fradio value={$hostRows[0]} {$dis}></td>";
              print "<td><input type=text name=host value={$hostName}></td>";
              print "<td><input type=text name=graphtype value={$graphType}></td>";
              print "<td><input type=text name=viewname value={$hostView}></td>";
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
    print '<h3>☆監視対象ホストはリソースグラフで作られる</h3>'; 
    print "</table>";
    print "</form>";
  }     
  print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell> 監視モニターへ戻る</span></a>";
  print '</body></html>';
}
?>

