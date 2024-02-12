<?php
require_once 'BaseFunction.php';
require_once 'mysqlkanshi.php';
require_once 'layoutsform.php';
require_once 'hostping.php';
date_default_timezone_set('Asia/Tokyo');

function gcreatearray($gdata,&$ghai){
  $gsc=count($gdata);
  for($gcc=0;$gcc<$gsc;$gcc++){
    $garr=explode(',',$gdata[$gcc]);
    $ghai[$gcc][0]=$garr[0]; ///group name
    $ghai[$gcc][1]=$garr[1]; ///group seq
    $ghai[$gcc][2]=$garr[2]; ///no. of host
    $ghai[$gcc][3]=$garr[3]; ///no. of seg
    $ghai[$gcc][4]=$garr[4]; ///sumi 
  }
}

function screatearray($hdata,$garr,&$hhai){ ///$hdata is host layout record
  $hdatac=count($hdata);
  $hdataidx=0;
  $gc=count($garr); /// グループ数取得
  for($gcc=0;$gcc<$gc;$gcc++){ /// グループのループ
    $dc=intval($garr[$gcc][3]); /// 段数取得
    for($dcc=0;$dcc<$dc;$dcc++){ /// 段数のループ
      $hc=intval($garr[$gcc][2]); /// ホスト数取得
      for($hcc=0;$hcc<$hc;$hcc++){ // ホストのループ
        $hdarr=explode(',',$hdata[$hdataidx]); ///layout ホストデータを配列
        $hhai[$gcc][$dcc][$hcc][0]=$hdarr[1]; ///ホスト名をコピー
        $hhai[$gcc][$dcc][$hcc][1]='view';  ///場所を確保
        $hhai[$gcc][$dcc][$hcc][2]='image'; ///場所を確保
        $hhai[$gcc][$dcc][$hcc][3]='snmp'; ///場所を確保
        $hdataidx++;
      }
    }
  }
}

$pgm="MonitorManager.php";
$user="";
$brcode="";
$brmsg="";
///
if(!isset($_GET['param'])){
  paramGet($pgm);
  ///
}else{
  paramSet();
  ///
  $sql="select * from admintb";
  $rows=getdata($sql);
  $adata=explode(',',$rows[0]);
  //----------
  $countdown = strval($adata[12]); 
  $interval = strval($adata[7]);
  $blk="";
  $blkmsg="";
  $blcolor="";
  $runmsg="";
  $coreold=strval($adata[11]);
  $corenew=strval($adata[12]);
  if($coreold==$corenew){
    $bkcolor="okcolor";
    $runmsg="   Core Running";
    $blk="blink";
    $blkmsg="同期中・・・";
  }else{
    $bkcolor="okcolor";
    $runmsg="   Core Running";
    $blk="";
    $blkmsg="";
  }
  //------------------------------------
  $sql="select * from header";
  $rows=getdata($sql);
  $sdata=explode(',',$rows[0]);
  $title="&ensp;&ensp;&ensp;&ensp;".$sdata[0]."(".$interval."秒間隔更新)";
  $subtitle="&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;".$sdata[1];
  $sql="select haikei from admintb";
  $rows=getdata($sql);
  $ttlimg=$rows[0];
  $mainttl='haikeiimg/'.$ttlimg;

  print '<html><head>';
  print "<meta http-equiv='Refresh' content={$interval}>";
  print '<link rel="stylesheet" href="manager.css">';
  print '</head><body>';
  print '<p style="position: relative;">';
  print "<img src={$mainttl} width='600' height='50' alt='Title' /><br />";
  print "<span style='position: absolute; top: 5px; left: 5px; width: 500px; color: white; font-size: 25px; font-weight: bold'>{$title}</span>";
  print '</p>';
  print "<h2>{$subtitle}</h2>";
  $gis=date('G:i:s');
  if ($user=='unknown'){
    $vuser='Lost User';
    $vcolor='ngcolor';
  }else{
    $vuser=$user;
    $vcolor='okcolor';
  }
  print "<table><tr class=big><td>&ensp;&ensp;ユーザー&ensp;</td><td class={$vcolor}>{$vuser}</td>";
  print "<td>&ensp;&ensp;モニターコア&ensp;&ensp;</td><td class={$bkcolor}><span class={$blk}>{$runmsg}</span></td><td>&ensp;{$blkmsg}</td></tr></table>";
  if ($user=='unknown'){
    print "<table><tr><td class={$vcolor}>&ensp;&ensp;ユーザが失われました、ログアウトし、新たなログインを実行して下さい</td></tr></table>";
  }
  //print "<td>&ensp;&ensp;モニターコア&ensp;&ensp;</td><td class={$bkcolor}><span class={$blk}>{$runmsg}</span></td><td>&ensp;{$blkmsg}</td></tr></table>";
  print "<br><table><tr class=big><td>&ensp;&ensp;監視時刻&ensp;</td><td class=okcolor>{$gis}</td>";
  print "<td>&ensp;&ensp;SNMPカウントダウン　</td><td class=okcolor>{$countdown}</td></tr></table>";
  print '</form><br>';
  $garr = array(); // group 配列テーブル
  $grponedata=array();
  /// group 配列テーブル作成
  $sql='select * from glayout order by gsequence';
  $rows=getdata($sql);
  gcreatearray($rows,$garr);
  /// ホストテーブル作成
  $sarr = array();
  $sql='select * from layout order by gshid';
  $rows=getdata($sql);
  screatearray($rows,$garr,$sarr);
  layoutsform($user,$garr,$sarr);
  $dtm=date('ymdHis');
  $usql='select authority from user where userid="'.$user.'"';
  $rows=getdata($usql);
  $udata=explode(',',$rows[0]);
  $auth=$udata[0];
  if ($auth=='1'){
    $upsql='update processtb set monstamp='.$dtm;
    putdata($upsql);
  }
}
print '</body></html>';
?>

