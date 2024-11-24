<?php
/// update 2024-2-17 add order by to select statements
error_reporting(0);
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

function gcreatearray($_gdata,&$ghai){
  $gCount=count($_gdata);
  for ($i=0;$i<$gCount;$i++){
    $garr=explode(',',$_gdata[$i]);
    $ghai[$i]=$garr; ///group name
  }
}

function getgroup($_grpno,$_ghai){ /// 配列で返る [0]=グル-プ名　[1]=順序　[2]=ホスト数　[3]=段数　[4]=済
  $rtRrec='';
  foreach ($_ghai as $ghiRec){
    $ghiArr = explode(',',$ghiRec);
    if ($_grpno==$ghiArr[1]){
      $rtnRec=$ghiRec;
      break;
    }
  }
  return $rtnRec; 
}

function screatearray($_hdata,$_garr){
/// $_hdata:ホストデータ配列, $_garr:グループデータ配列
  $hitemArr=array();
  $c=count($_hdata);
  for ($i=0;$i<$c;$i++){ 
    $hitem=explode(',',$_hdata[$i]);
    $hitemArr[$i]=$hitem;
  }
  $num=0;
  $maxg=count($_garr); /// 4
  $gnameArr=array();
  for ($i=0;$i<$maxg;$i++){ 
  /// グループ数分繰り返し
    $segCount=intval($_garr[$i][3]);   ///  セグメント数取得
    $snameArr=array();
    for ($j=0;$j<$segCount;$j++){
      /// セグメント数分繰り返し
      $hostCount=intval($_garr[$j][2]);        /// ホスト数取得
      $hnameArr=array();
      for ($k=0;$k<$hostCount;$k++){
      /// ホスト数分繰り返し
        $hnamex=array();
        $hnameArr[$k]=$hitemArr[$num][1];
        $num++;
      }
      $snameArr[$j]=$hnameArr;
    }
    $gnameArr[$i]=$snameArr;
  }
  return $gnameArr;
}
///                    group  host   layout  user
function layoutsupform($_garr,$_sarr,$_layout,$_user){
  $_maxGrp=count($_garr);                
  print '<form name=myform action=layoutsupmapdb.php method=get>';
  for ($i=0;$i<$_maxGrp;$i++){     /// グループ数繰り返し
    $_maxSeg=intval($_garr[$i][3]); /// 1グループ内セグメント取得
    $groupNum=strval($i+1);        /// グループ番号取得
    $groupName=$_garr[$i][0];      /// グループ名取得
    //$groupValue=$groupNum.','.$groupName; /// グループ番号＋グループ名
    print "<br><table><tr><td class=back><b>グループ:</b></td><td><input type=text name='groupname[]' value={$groupName}></td></tr></table>";
    print "<input type='hidden' name='groupno[]' value={$groupNum}>";
    print '<table border="1" class="tablelayout">';
    for ($j=0;$j<$_maxSeg;$j++){   /// 1グループ内セグメントループ
      $maxHost=intval($_garr[$j][2]);     /// ホスト数取得
      print '<tr>'; 
      for ($k=0;$k<$maxHost;$k++){       /// ホスト数ループ
        $host=$_sarr[$i][$j][$k];  /// ホスト名取得
        if ($host=='' or $host=='No Assign'){
          $host='NoAssign';
        }
        $dataG='g'.strval($i+1);   
        $dataS='s'.strval($j);
        $dataH='h'.strval($k);
        $dataGsh=$dataG.$dataS.$dataH;   /// gsh連結
        //$dataVal=$dataGsh.','.$host;   /// gsh + ホスト名
        //$dataarr='data['.$dataG.$dataS.$dataH.']'; /// 配列へ格納
        print "<td class=back><input type=text name='data[]' value={$host}></td>";
        print "<input type=hidden name='key[]' value={$dataGsh}>";        
      }
      print '</tr>';
    }
    print '</table>';
  }
  print "<input type=hidden name=type value={$_layout}>";
  print "<input type=hidden name=user value={$_user}>";
  print '<br><input class=button type=submit name=go value="実行">';
  print '</form>';
}
///
$pgm='layoutsupmap.php';
$layout=$_GET['terms'];
$user=$_GET['user'];

print '<html><head><meta>';
print '<link rel="stylesheet" href="css/kanshi1.css">';
print '</head><body>';
print "<h2><img src='header/php.jpg' width='30' height='30'>&emsp;&emsp;グループ名、ホスト名変更  ／  レイアウト名：{$layout}</h2>";
print '<h4>現用レイアウトのグループ名、ホスト名／IPアドレスが変更出来ます、変更したら「実行」をクリックして下さい<br>';
print '変更しないものは、そのまま反映されます</h4>';
$grpLayoutName='g'.$layout;
$hostLayoutName=$layout;
$grpArr = array(); /// group array table
$hostArr = array(); /// host array table

/// group 配列テーブル作成
$layout_sql='select * from '.$grpLayoutName.' order by gsequence';
$grpRows=getdata($layout_sql);

gcreatearray($grpRows,$grpArr); /// group layout data から group array table作成
/// ホストテーブル作成
$layout_sql='select * from '.$hostLayoutName. ' order by gshid';
$hostRows=getdata($layout_sql);
///
$hostArr=screatearray($hostRows,$grpArr); /// host layout data から host array table作成
///
layoutsupform($grpArr,$hostArr,$layout,$user);
///
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body></html>';
?>

