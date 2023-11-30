<?php

require_once "mysqlkanshi.php";
require_once "varread.php";
require_once "mailsendany.php";

function branch($page,$param){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo "<form name='F' action={$page} method='get'>";
  echo "<input type=hidden name=param value={$param}>";
  echo '<input type="submit" name="next" value="Waiting...">';
  echo '</form>';
}
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
$uid = "";
$ecde="";
$emsg="";
if(!isset($_GET['param'])){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="GraphListPlotPage.php" method="get">';
  echo '<input type="hidden" name="param" value="">';
  echo '<input type="submit" name="next" style="display:none;" />';
  echo '</form></body></html>';
  echo '<script type="text/javascript">';
  echo 'var keyvalue = sessionStorage.getItem("user");';
  echo 'if (!keyvalue) {';
  echo '  keyvalue = "unknown";';
  echo '}';
  echo 'document.forms["F"].elements["param"].value = keyvalue;';
  echo '</script>';
}else{
  $param=$_GET['param'];
  if(substr($param,0,1)=="#"){
    $parr=explode("#",$param);
    $cde=$parr[1]; 
    $uid=$parr[2];
    $emsg=$parr[3];    
  }else{
    $uid=$_GET['param'];
  }
  $ttl1='<img src="header/php.jpg" width="30" height="30">';
  $ttl2='　▽　プロットグラフホスト一覧　▽　';
  $ttl=$ttl1.$ttl2;
  echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
  echo '<html lang="ja">';
  echo '<head>';
  echo '<link rel="stylesheet" href="kanshi1.css">';
  echo '<title>Host List</title>';
  echo '</head>';
  echo '<body>';
  if ($cde == "error"){
    echo "<h3 class={$cde}>{$emsg}</h3><hr>";
  }
  echo "<h2>{$ttl}</h2>";
  echo "<h4>☆ホストを１つ選択して「グラフ表示」をクリックする</h4>";
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
    echo "<h3><font color=red>変数vpath_mrtgbase取得不可、管理者に通知</font></h3>";         
    echo "<a href='MonitorManager.php?param={$uid}'><span class=buttonyell> 監視モニターへ戻る</span></a>";
    echo "</body>";
    echo "</html>";
  }
  $mrtgcfg = mrtgcfgread($vpath_mrtg);
  $sql="select * from host order by groupname";
  $data = getdata($sql);
  $c = count($data);
  $nsw=0;
  echo '<form name="rform" method="get" action="viewgraphplot.php">';
  echo '<table><tr><th></th><th width=100>ホスト</th><th>グラフ種類</th><th>表示名</th><th>状態</th></tr>';
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
        echo "<tr><td><input type=radio name=fradio value={$data[$i]}></td>";
        echo "<td><input type=text name=host value={$sdata[0]}></td>";
        echo "<td><input type=text name=graphtype value={$gtype}></td>";
        echo "<td><input type=text name=viewname value={$sdata[5]}></td>";
        echo "<td><input type=text name=active value={$act}></td></tr>";
        echo "<input type=hidden name=user value={$uid}>";
        $nsw=1; 
      }
    }
  }
  if ($nsw==1){
    echo '<tr><td><br></td></tr>';
    echo '<tr><td colspan=2>&emsp;<input class=button type="submit" name="display" value="グラフ表示" ></td></tr>';
  }else{
    echo "<h4><font color=red>snmp監視対象ホストがありません</font></h4>";
  }  
  echo "</table>";
  echo "</form>";
}
    
echo '<br>';
echo "<a href='MonitorManager.php?param={$uid}'><span class=buttonyell> 監視モニターへ戻る</span></a>";
echo '</body></html>';
?>
