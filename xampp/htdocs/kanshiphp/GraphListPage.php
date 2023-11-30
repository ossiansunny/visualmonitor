<?php
require_once "mysqlkanshi.php";

$uid = "";
$ecde="";
$emsg="";

if(!isset($_GET['param'])){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="GraphListPage.php" method="get">';
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
    $cde=$parr[1]; // 通知コード
    $uid=$parr[2]; // ユーザID
    $emsg=$parr[3]; //メッセージ 
  }else{
    $uid=$_GET['param'];
  }
  $ttl1='<img src="header/php.jpg" width="30" height="30">';
  $ttl2='　▽　グラフホスト一覧　▽　';
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
  //
  //---SNMP監視対象一覧を表示---
  //
  echo "<h4>☆ホストを１つ選択して「グラフ表示」「グラフ作成」「グラフ削除」のいずれかをクリックする</h4>";
  $sql="select * from host order by groupname";
  $data = getdata($sql);
  $c = count($data);
  echo '<form name="rform" method="get" action="viewgraph.php">';
  echo '<table><tr><th></th><th width=100>ホスト</th><th>グラフ種類</th><th>表示名</th><th>状態</th></tr>';
  $nsw=0;
  for($i=0;$i<$c;$i++){
    $sdata = explode(',',$data[$i]);
    if($sdata[4]=="2"){ // snmp監視
      if($sdata[3]=="1"){$act="稼働中";}
      if($sdata[3]!="1"){$act="非稼働";}
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
    $usql='select authority from user where userid="'.$uid.'"';
    $rows=getdata($usql);
    $udata=explode(',',$rows[0]);
    $auth=$udata[0];
    echo '<tr><td><br></td></tr>';
    echo '<tr><td colspan=2>&emsp;<input class=button type="submit" name="display" value="グラフ表示" ></td></tr>';
    if ($auth=='1'){
      echo '<tr><td colspan=2>&emsp;<input class=buttonyell type="submit" name="create" value="グラフ作成" ></td></tr>';
      echo '<tr><td colspan=2>&emsp;<input class=buttondel type="submit" name="delete" value="グラフ削除" ></td></tr>';
    }    
  }else{
    echo "<h4><font color=red>snmp監視対象ホストがありません</font></h4>";
  }
  echo "</table>";
  echo "</form>";
  echo '<br>';
  echo "<a href='MonitorManager.php?param={$uid}'><span class=buttonyell> 監視モニターへ戻る</span></a>";
}
echo "</body></html>";
?>
