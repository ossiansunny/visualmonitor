<?php
require_once 'BaseFunction.php';
require_once 'mysqlkanshi.php';
require_once 'monitorlayout.php';
date_default_timezone_set('Asia/Tokyo');

function popupMsgSet($dataStr){
  $popArr=explode("//",$dataStr);
  $popupData="";
  $firstSw=0;
  foreach($popArr as $popItem){
    if($firstSw==0){
      $popItem="&lt;p&gt;".$popItem."&lt;/p&gt;&lt;p&gt;&lt;font size=2&gt;";
      $firstSw=1;
    }
    $popupData=$popupData.$popItem;    
  }
  $popupData=$popupData."&lt;/font&gt;&lt;/p&gt;";
  return $popupData;
}

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
        if(is_null($hdata[$hdataidx])){ /// layout空データの無視（？）
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
  $admin_sql="select monintval,snmpintval,haikei,laststamp,logout from admintb";
  $adminRows=getdata($admin_sql);
  $adminStr=explode(',',$adminRows[0]);
  ///----------
  $interval = strval($adminStr[0]);
  $coreIntval=$adminStr[1];
  $nameImage=$adminStr[2];
  $lastStamp=$adminStr[3];
  $logout=$adminStr[4];
  $blink="";
  $blinkMsg="同期中・・・";
  $blinkColor="";
  $runMsg="   Core Running";
  $titleImage='haikeiimg/'.$nameImage;
  ///-------------------------------------
  $user_sql='select authority,bgcolor,audio from user where userid="'.$user.'"';
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    branch('logout.php',$msg);
  }
  $userArr=explode(',',$userRows[0]);
  $userAuth=$userArr[0];
  $bgColor=$userArr[1];
  $audio=$userArr[2];
  if($userAuth=='1'){
    $userName='管理者';
  }else{
    $userName='一般監視者';
  }    
  ///------------------------------------
  $header_sql="select title,subtitle from header";
  $headerRows=getdata($header_sql);
  $headerArr=explode(',',$headerRows[0]);
  $headerTitle=$headerArr[0];
  $headerSubTitle=$headerArr[1];
  $title="&ensp;&ensp;&ensp;".$headerTitle."&ensp;&ensp;<font size=4>(監視間隔：".$coreIntval."秒&ensp;表示間隔:".$interval."秒)</font>";
  $subTitle="&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;".$headerSubTitle;
  /// 
  $charColor='white';
  
  ///
  /// html
  ///
  print '<html><head>';
  if($logout=='0'){
    print "<meta http-equiv='Refresh' content={$interval}>";
  }
  print '<link rel="stylesheet" href="css/manager.css">';  
  ///
  print "</head><body class='".$bgColor."'>";
  print '<p style="position: relative;">';
  print "<img src={$titleImage} width='600' height='50' alt='Title' /><br />";
  print "<span style='position: absolute; top: 5px; left: 5px; width: 600px; color: white; font-size: 25px; font-weight: bold'>{$title}</span>";
  print '</p>';
  print "<h2><font color=white>{$subTitle}</font></h2>";
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
  print "<table><tr class=big>";
  print "<td class='white15'>&ensp;&ensp;{$userName}&ensp;</td>";
  print "<td class={$viewColor}>{$viewUser}</td>";
  print "<td class='white15'>&ensp;&ensp;監視終了時刻&ensp;</td>";
  print "<td class=okcolor>{$lastStamp}</td>";
  print "<td class='white15'>&ensp;&ensp;表示開始時刻&ensp;</td>";
  print "<td class=okcolor>{$currTime}</td>";
     
  $popData="ホスト画像および「赤表示」、「黄表示」部分をクリックすると追加情報がポップアップされる";
  "監視対象ホストが少なければ待機中の時間が長い。";
  $popupRtnData=popupMsgSet($popData);  
  
  print "</tr></table>";
  if ($user=='unknown'){
    print "<table><tr><td class={$vcolor}>&ensp;&ensp;ユーザが失われました、ログアウトし、新たなログインを実行して下さい</td></tr></table>";
  }
  print "<br><table><tr class=big>";
  print "<td class='white15'>&ensp;&ensp;{$popData}&ensp;</td>";
  //print '<td><a class="white15" style="text-decoration:none;" href="" onclick="viewPopup(\''.$popupRtnData.'\'); return false;">&ensp;&ensp;待機中とは</a></td>';
  //print "<td class=okcolor>{$coreNew}</td>";
  print "</tr></table>";
  print '</form><br>';  
  ///
  /// -------------------------------------------------------------
  /// グループ配列テーブル作成
  $groupArr = array(); /// group 配列テーブル
  $glayout_sql='select * from glayout order by gsequence';
  $glayoutRows=getdata($glayout_sql);
  groupCreateArray($glayoutRows,$groupArr);
  /// ホスト配列テーブル作成
  $hostArr = array();
  $layout_sql='select * from layout order by gshid';
  $layoutRows=getdata($layout_sql);
  hostCreateArray($layoutRows,$groupArr,$hostArr);
  ///
  /// 監視ホスト配置　　
  monitorlayout($user,$userAuth,$bgColor,$audio,$groupArr,$hostArr);
  ///
  /// 監視が管理者により実行されているタイムスタンプをセット
  /// login.phpでチェック、reset.phpで初期化
  $timeStamp=date('ymdHis');
  if ($userAuth=='1'){
    $proc_sql='update processtb set monstamp='.$timeStamp;
    putdata($proc_sql);
  }
  ///
  /// ---------------------------------------------------------------
  
  
}
?>

