<?php
require_once 'mysqlkanshi.php';
require_once 'layoutsform.php';
require_once 'winhostping.php';
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

if(!isset($_GET['param'])){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="MonitorManager.php" method="get">';
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
  $uid=$_GET['param'];
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

  echo '<html><head>';
  echo "<meta http-equiv='Refresh' content={$interval}>";
  echo '<link rel="stylesheet" href="manager.css">';
  echo '</head><body>';
  echo '<p style="position: relative;">';
  echo "<img src={$mainttl} width='600' height='50' alt='Title' /><br />";
  echo "<span style='position: absolute; top: 5px; left: 5px; width: 500px; color: white; font-size: 25px; font-weight: bold'>{$title}</span>";
  echo '</p>';
  echo "<h2>{$subtitle}</h2>";
  $gis=date('G:i:s');
  echo "<table><tr class=big><td>&ensp;&ensp;ユーザー&ensp;</td><td class=okcolor>{$uid}</td>";
  echo "<td>&ensp;&ensp;モニターコア&ensp;&ensp;</td><td class={$bkcolor}><span class={$blk}>{$runmsg}</span></td><td>&ensp;{$blkmsg}</td></tr></table>";
  echo "<br><table><tr class=big><td>&ensp;&ensp;監視時刻&ensp;</td><td class=okcolor>{$gis}</td>";
  echo "<td>&ensp;&ensp;SNMPカウントダウン　</td><td class=okcolor>{$countdown}</td></tr></table>";
  echo '</form><br>';
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
  layoutsform($uid,$garr,$sarr);
  $dtm=date('ymdHis');
  $usql='select authority from user where userid="'.$uid.'"';
  $rows=getdata($usql);
  $udata=explode(',',$rows[0]);
  $auth=$udata[0];
  if ($auth=='1'){
    $upsql='update processtb set monstamp='.$dtm;
    putdata($upsql);
  }
}
echo '</body></html>';
?>
