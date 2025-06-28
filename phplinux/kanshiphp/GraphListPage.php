<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "varread.php";
$brcode=""; /// 通知コード
$user=""; /// ユーザID
$brmsg=""; ///メッセージ $user = "";
$pgm="GraphListPage.php";
$vpath_kanshiphp="";
$exists='0';
///
$vpathParam=array("vpath_kanshiphp","vpath_mrtg");
$rtnPath=pathget($vpathParam);
$vpath_kanshiphp=$rtnPath[0];
$vpath_mrtg=$rtnPath[1];
///

///
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
  ///
  $user_sql='select authority,bgcolor from user where userid="'.$user.'"';
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    branch('logout.php',$msg);
  }
  $userArr=explode(',',$userRows[0]);
  $authority=$userArr[0];
  $bgColor=$userArr[1];
    
  if(! file_exists($vpath_mrtg)){
    print "<html><body class={$bgColor}>";
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
  print "<body class={$bgColor}>";
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
    print "<h3>☆ホストを１つ選択して<span class=trblk>「グラフ表示/メール添付」</span>&nbsp;&nbsp;<span class=trylw>「グラフ登録」</span>&nbsp;&nbsp;<span class=trred>「グラフ削除」</span>のいずれかをクリックする<br>";
    print "☆グラフのメール添付には、ホストのメール「自動送信」が必要です</h3>";
    ///
    $layout_sql="select host from layout";
    $layoutRows=getdata($layout_sql);
    
    print '<table><tr><th></th><th width=100>ホスト</th><th>グラフ種類</th><th>表示名</th><th>状態</th></tr>';
    $exeSw=0;
    $active="";
    $disable="";
    /// 対象ホスト存在チェック
    print '<form name="rform" method="get" action="viewgraph.php">';
    foreach ($layoutRows as $layoutRowsRec){
      if (!($layoutRowsRec=='' or $layoutRowsRec=='NoAssign')){
        $host_sql="select host,groupname,ostype,result,action,viewname,mailopt,tcpport,cpulim,ramlim,disklim,process,image,snmpcomm,agenthost,eventlog,standby from host where host='".$layoutRowsRec."'";
        $hostRows=getdata($host_sql);
        if (!(empty($hostRows))){
          $hostArr=explode(',',$hostRows[0]);
          $host=$hostArr[0];
          $result=$hostArr[3];
          $action=$hostArr[4];
          $viewname=$hostArr[5];
          $cpulim=$hostArr[8];
          $ramlim=$hostArr[9];
          $disklim=$hostArr[10];
          if($action=="2" or $action=="3"){ /// snmp監視 snmp監視通知なし
            $color="colorred"; 
            if($result=="1"){
              $active="グラフ登録済";
              $disable="";
              $color="colorgreen";
            }
            if($result!="1"){
              $active="非稼働";
              $disable="";
            }
            $cfgrtn=mrtgcfgck($host);
            if($cfgrtn==1){
              $active="グラフ未登録";
              $disable="";$color="colorred";
            }
            if($cpulim!="" || $ramlim!="" || $disklim!="") {
              $graphType="";
              if($cpulim!=""){
                $graphType="CPU";
              }
              if($ramlim!=""){
                $graphType=$graphType . ";" . "RAM";
              }
              if($disklim!=""){
                $graphType=$graphType . ";" . "Disk";
              }
              $graphType=trim($graphType,';');
              
              print "<tr><td><input type=radio name=fradio value={$hostRows[0]} {$disable}></td>";
              print "<td><input type=text name=host value={$host}></td>";
              print "<td><input type=text name=graphtype value={$graphType}></td>";
              print "<td><input type=text name=viewname value={$viewname}></td>";
              print "<td><input class={$color} type=text name=active value={$active}></font></td></tr>";
              
              $exeSw=1;
            }
          }
        }
      }
    }
    print "</table>";
    print "<br>";
    print "<input type=hidden name=user value={$user}>";
    print '&emsp;<input type="submit" name="display" class="button" value="グラフ表示/メール添付">';
    if ($authority=='1'){
      print '&emsp;<input type="submit" name="create" class="buttonyell" value="グラフ登録">';
      print '&emsp;<input type="submit" name="delete" class="buttondel" value="グラフ削除">';
    }
    print "</form>";   
    if ($exeSw==0){    
      $msg="#error#".$user."#snmp監視対象ホストがありません";
      $nextpage=$pgm;    
      print '<h4><span class=buttonyell>グラフ監視対象ホストがありません</span></h4>';
    }
    ///
    print '<br><br>';
  }
  print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell> 監視モニターへ戻る</span></a>";
  print "</body></html>";
  
}

?>

