<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";
require_once "mailsendany.php";

function mrtgcfgck($host,$path){
  $files = glob($path."/mrtgcfg/".$host.".*");
  //echo $path.'/mrtgcfg/'.$host.'.*';
  if (! empty($files)){
    return "0"; //グラフ作成中
  }else{
    return "1"; //グラフ未作成
  }
}

$user = "";
$brcode="";
$brmsg="";
$pgm="GraphListPlotPage.php";
$vpath_kanshiphp="";
$flag="";
$dis="";

if(!isset($_GET['param'])){
  paramGet($pgm);
  ///
}else{
  paramSet();
  ///
  $ttl1='<img src="header/php.jpg" width="30" height="30">';
  $ttl2='　▽　プロットグラフホスト一覧　▽　';
  $ttl=$ttl1.$ttl2;
  print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
  print '<html lang="ja">';
  print '<head>';
  print '<link rel="stylesheet" href="kanshi1_py.css">';
  print '<title>Host List</title>';
  print '</head>';
  print '<body>';
  if ($brcode=="error" or $brcode=="alert" or $brcode=="notic"){
    print '<h3 class="'.$brcode.'">"'.$brmsg.'"</h3><hr>';
    //print "<h3 class={$brcde}>{$brmsg}</h3><hr>";
  }
  print '<h2>'.$ttl.'</h2>';
  print "<h4>☆ホストを１つ選択して「グラフ表示／メール添付」をクリックする<br>";
  print "☆グラフのメール添付には、ホストのメール「自動送信」が必要です</h4>";
  $vpatharr=array("vpath_kanshiphp");
  $rtnv=pathget($vpatharr);
  $vpath_kanshiphp=$rtnv[0]; 
  //echo $vpath_kanshiphp; 
  $sql="select * from host order by groupname";
  $data = getdata($sql);
  $c = count($data);
  $nsw=0;
  $act="";
  print '<form name="rform" method="get" action="viewgraphplot.php">';
  print '<table><tr><th></th><th width=100>ホスト</th><th>グラフ種類</th><th>表示名</th><th>状態</th></tr>';
  for($i=0;$i<$c;$i++){
    $sdata = explode(',',$data[$i]);
    //var_dump($sdata);
    if($sdata[4]=="2"){ // snmp監視対象ホストチェック
      $flag=mrtgcfgck($sdata[0],$vpath_kanshiphp);
      if ($flag=='0'){
        $act='グラフ作成中';
        $dis='';
      }else{
        $act='グラフ未作成';
        $dis='disabled';
      }
      if($sdata[8]!="" || $sdata[9]!="" || $sdata[10]!="") {
        $gtype="";
        if($sdata[8]!=""){$gtype="CPU";}
        if($sdata[9]!=""){$gtype=$gtype . ";" . "RAM";}
        if($sdata[10]!=""){$gtype=$gtype . ";" . "Disk";}
        $gtype=trim($gtype,';');
        print "<tr><td><input type=radio name=fradio value={$data[$i]} {$dis}></td>";
        print "<td><input type=text name=host value={$sdata[0]}></td>";
        print "<td><input type=text name=graphtype value={$gtype}></td>";
        print "<td><input type=text name=viewname value={$sdata[5]}></td>";
        print "<td><input type=text name=active value={$act}></td></tr>";
        print "<input type=hidden name=user value={$user}>";
        $nsw=1; 
      }
    }
  }
  if ($nsw==1){
    print '<tr><td><br></td></tr>';
    print '<tr><td colspan=2 align=center>&emsp;<input class=button type="submit" name="display" value="グラフ表示/メール添付" ></td></tr>';
  }else{
    $msg="#error#".$user."#snmp監視対象ホストがありません";
    $nextpage=$pgm;    
    branch($nextpage,$msg);
    print '<h4><span class=buttonyell>グラフ監視対象ホストがありません</span></h4>';    
  }  
  print "</table>";
  print "</form>";
}
print '<h4>☆監視対象ホストはリソースグラフで作られます</h4>';    
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell> 監視モニターへ戻る</span></a>";
print '</body></html>';
?>

