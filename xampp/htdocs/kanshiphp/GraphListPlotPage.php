<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";
require_once "mailsendany.php";

function mrtgcfgread($path){
  $array = array();
  $fp = fopen($path."\\newmrtg.cfg","r");  
  $rtable = array();
  $oldkey = '';
  $c=0;
  if($fp){
    while ($line = fgets($fp)) {
      if (strpos($line,'Target[') !== false){
        $array = explode(' ',$line);
        if ($oldkey !== $array[2]) { 
          $rtable[$c] = $array[2];
          $oldkey = $array[2];
          $c++;
        }
      }
    }
  }
  fclose($fp);
  return $rtable;
}
$user = "";
$brcode="";
$brmsg="";
$pgm="GraphListPlotPage.php";
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
    print "<h3 class={$brcde}>{$brmsg}</h3><hr>";
  }
  print "<h2>{$ttl}</h2>";
  print "<h4>☆ホストを１つ選択して「グラフ表示」をクリックする</h4>";
  $vpath_mrtg="";
  $vpatharr=array("vpath_mrtgbase");
  $rtnv=pathget($vpatharr);
  if(count($rtnv)==1){
    $vpath_mrtg=$rtnv[0];  
  }else{
    writeloge($pgm,"variable vpath_mrtgbase could not get path");
    $rdsql="select * from admintb";
    $rows=getdata($rdsql);
    $sdata=explode(',',$rows[0]);
    $recv=$sdata[3];
    $sender=$sdata[4];
    $subj="Path変数不正";
    $body=$pgm."パス変数 vpath_mrtgbase 取得不可";
    mailsendany('other',$sender,$recv,$subj,$body);
    $msg="#error#".$user."#変数vpath_mrtgbase取得不可、管理者に通知";
    $nextpage=$pgm;    
    branch($nextpage,$msg);
  }
  $mrtgcfg = mrtgcfgread($vpath_mrtg);
  $sql="select * from host order by groupname";
  $data = getdata($sql);
  $c = count($data);
  $nsw=0;
  print '<form name="rform" method="get" action="viewgraphplot.php">';
  print '<table><tr><th></th><th width=100>ホスト</th><th>グラフ種類</th><th>表示名</th><th>状態</th></tr>';
  for($i=0;$i<$c;$i++){
    $ssw = "0";
    $sdata = explode(',',$data[$i]);
    if($sdata[4]=="2"){ // snmp監視対象ホストチェック
      foreach ($mrtgcfg as $item){
        if ($sdata[0] == $item){
          $ssw="1";
          break;
        } 
      }
      if($ssw=="1" && $sdata[3]=="1"){
        $act="グラフ取得中";
      }else if($ssw=="0" && $sdata[3]=="1"){
        $act="稼働中";
      }else if($sdata[3]!="1"){
        $act="非稼働";
      }    
      if($sdata[8]!="" || $sdata[9]!="" || $sdata[10]!="") {
        $gtype="";
        if($sdata[8]!=""){$gtype="CPU";}
        if($sdata[9]!=""){$gtype=$gtype . ";" . "RAM";}
        if($sdata[10]!=""){$gtype=$gtype . ";" . "Disk";}
        $gtype=trim($gtype,';');
        print "<tr><td><input type=radio name=fradio value={$data[$i]}></td>";
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
    //branch($nextpage,$msg);
    print '<h4><span class=buttonyell>グラフ監視対象ホストがありません</span></h4>';    
  }  
  print "</table>";
  print "</form>";
}
    
print '<br>';
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell> 監視モニターへ戻る</span></a>";
print '</body></html>';
?>

