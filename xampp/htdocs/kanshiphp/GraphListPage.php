<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";
$brcode=""; // 通知コード
$user=""; // ユーザID
$brmsg=""; //メッセージ $user = "";
$pgm="GraphListPage.php";
$vpath_kanshiphp="";

function mrtgcfgck($host,$stat){
  global $vpath_kanshiphp;
  //echo $vpath_kanshiphp."\\mrtgcfg\\".$host.".*";
  $files = glob($vpath_kanshiphp."\\mrtgcfg\\".$host.".*");
  if (empty($files)){
    return "グラフ未作成";
  }else{
    return $stat;
  }
}

if(!isset($_GET['param'])){
  paramGet($pgm);
  ///
}else{
  paramSet();
  ///
  $vpatharr=array("vpath_kanshiphp");
  $rtnv=pathget($vpatharr);
  $vpath_kanshiphp=$rtnv[0];
///
  $ttl1='<img src="header/php.jpg" width="30" height="30">';
  $ttl2='　▽　グラフホスト一覧　▽　';
  $ttl=$ttl1.$ttl2;
  print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
  print '<html lang="ja">';
  print '<head>';
  print '<link rel="stylesheet" href="kanshi1_py.css">';
  print '<title>Host List</title>';
  print '</head>';
  print '<body>';
  ///
  if ($brcode=="error" or $brcode=="alert" or $brcode=="notic"){
    print "<h3 class={$brcde}>{$brmsg}</h3><hr>";
  }
  ///
  print "<h2>{$ttl}</h2>";
  ///
  ///---SNMP監視対象一覧を表示---
  ///
  print "<h4>☆ホストを１つ選択して「グラフ表示/メール添付」「グラフ作成」「グラフ削除」のいずれかをクリックする<br>";
  print "☆グラフのメール添付には、ホストのメール「自動送信」が必要です</h4>";
  $sql="select * from host order by groupname";
  $data = getdata($sql);
  $c = count($data);
  print '<form name="rform" method="get" action="viewgraph.php">';
  print '<table><tr><th></th><th width=100>ホスト</th><th>グラフ種類</th><th>表示名</th><th>状態</th></tr>';
  $nsw=0;
  $act="";
  for($i=0;$i<$c;$i++){
    $sdata = explode(',',$data[$i]);
    if($sdata[4]=="2"){ // snmp監視
      if($sdata[3]=="1"){$act="稼働中";}
      if($sdata[3]!="1"){$act="非稼働";}
      $act=mrtgcfgck($sdata[0],$act);
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
    $usql='select authority from user where userid="'.$user.'"';
    $rows=getdata($usql);
    $udata=explode(',',$rows[0]);
    $auth=$udata[0];
    print '<tr><td><br></td></tr>';
    ///
    print '<input type="submit" style="display:none">';
    print '<tr><td colspan=2 align=center>&emsp;<input class=button type="button" name="display" onclick="func1(\'viewgraph\')" value="グラフ表示/メール添付"></td></tr>';
    if ($auth=='1'){
      print '<input type="submit" style="display:none">';
      print '<tr><td colspan=2 align=center>&emsp;<input class=buttonyell type="button" name="create" onclick="func1(\'graphcreate\')" value="グラフ作成"></td></tr>';
      print '<input type="submit" style="display:none">';
      print '<tr><td colspan=2 align=center>&emsp;<input class=buttondel type="button" name="delete" onclick="func1(\'graphdelete\')" value="グラフ削除"></td></tr>';
    }    
  }else{
    $msg="#error#".$user."#snmp監視対象ホストがありません";
    $nextpage=$pgm;    
    //branch($nextpage,$msg);
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
  print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell> 監視モニターへ戻る</span></a>";
  print "</body></html>";
}

?>

