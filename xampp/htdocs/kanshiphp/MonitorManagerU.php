<?php
require_once 'BaseFunction.php';
require_once 'mysqlkanshi.php';
require_once 'layoutsformU.php';  /// layoutsformU.php 試験中
date_default_timezone_set('Asia/Tokyo');

function groupCreateArray($gdata,&$ghai){
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

function hostCreateArray($hdata,$garr,&$hhai){ ///$hdata is host layout record
  $hdatac=count($hdata);
  $hdataidx=0;
  $gc=count($garr); /// グループ数取得
  for($gcc=0;$gcc<$gc;$gcc++){ /// グループのループ
    $dc=intval($garr[$gcc][3]); /// 段数取得
    for($dcc=0;$dcc<$dc;$dcc++){ /// 段数のループ
      $hc=intval($garr[$gcc][2]); /// ホスト数取得
      for($hcc=0;$hcc<$hc;$hcc++){ /// ホストのループ
        if(is_null($hdata[$hdataidx])){
          break;
        }
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

$pgm="MonitorManagerU.php";
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
  $admin_sql="select * from admintb";
  $adminRows=getdata($admin_sql);
  $adminStr=explode(',',$adminRows[0]);
  ///----------
  $countdown = strval($adminStr[12]); 
  $interval = strval($adminStr[7]);
  $blink="";
  $blinkMsg="";
  $blinkColor="";
  $runMsg="";
  $coreOld=strval($adminStr[11]);
  $coreNew=strval($adminStr[12]);
  if($coreOld==$coreNew){
    $blinkColor="okcolor";
    $runMsg="   Core Running";
    $blink="blink";
    $blinkMsg="同期中・・・";
  }else{
    $blinkColor="okcolor";
    $runMsg="   Core Running";
    $blink="";
    $blinkMmsg="";
  }
  $nameImage=$adminStr[14];
  $titleImage='haikeiimg/'.$nameImage;
  $bgcolor=$adminStr[17];
  ///------------------------------------
  $header_sql="select * from header";
  $headerArr=getdata($header_sql);
  $headerStr=explode(',',$headerArr[0]);
  $title="&ensp;&ensp;&ensp;".$headerStr[0]."(".$interval."秒間隔更新)";
  $subTitle="&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;".$headerStr[1];
  print '<html><head>';
  print "<meta http-equiv='Refresh' content={$interval}>";
  print '<link rel="stylesheet" href="css/managerU.css">'; 
  print "</head><body class='{$bgcolor}'>";
  print '<p style="position: relative;">';
  print "<img src={$titleImage} width='600' height='50' alt='Title' /><br />";
  print "<span style='position: absolute; top: 5px; left: 5px; width: 600px; color: white; font-size: 25px; font-weight: bold'>{$title}</span>";
  print '</p>';
  print "<h2>{$subTitle}</h2>";
  $currTime=date('G:i:s');
  $viewUser="";
  $viewColor="";
  if ($user=='unknown'){
    $viewUser='Lost User';
    $viewColor='ngcolor';
  }else{
    $viewUser=$user;
    $viewColor='okcolor';
  }
  print "<table><tr class=big><td>&ensp;&ensp;ユーザー&ensp;</td><td class={$viewColor}>{$viewUser}</td>";
  print "<td>&ensp;&ensp;モニターコア&ensp;&ensp;</td><td class={$blinkColor}><span class={$blink}>{$runMsg}</span></td><td>&ensp;{$blinkMsg}</td></tr></table>";
  if ($user=='unknown'){
    print "<table><tr><td class={$vcolor}>&ensp;&ensp;ユーザが失われました、ログアウトし、新たなログインを実行して下さい</td></tr></table>";
  }
  print "<br><table><tr class=big><td>&ensp;&ensp;監視時刻&ensp;</td><td class=okcolor>{$currTime}</td>";
  print "<td>&ensp;&ensp;SNMPカウントダウン　</td><td class=okcolor>{$countdown}</td></tr></table>";
  print '</form><br>';
  $groupArr = array(); /// group 配列テーブル
  
  /// グループ配列テーブル作成
  $glayout_sql='select * from glayout order by gsequence';
  $glayoutRows=getdata($glayout_sql);
  groupCreateArray($glayoutRows,$groupArr);
  /// ホスト配列テーブル作成
  $hostArr = array();
  $layout_sql='select * from layout order by gshid';
  $layoutRows=getdata($layout_sql);
  hostCreateArray($layoutRows,$groupArr,$hostArr);
  ///　　
  $user_sql='select authority from user where userid="'.$user.'"';
  $userRows=getdata($user_sql);
  $userArr=explode(',',$userRows[0]);
  $userAuth=$userArr[0];
  ///　　
  /// 監視ホスト配置　　
  layoutsform($user,$userAuth,$groupArr,$hostArr);
  ///
  $timeStamp=date('ymdHis');
  if ($userAuth=='1'){
    $proc_sql='update processtb set monstamp='.$timeStamp;
    putdata($proc_sql);
  }
}
print '</body></html>';
?>

