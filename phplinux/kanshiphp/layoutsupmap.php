<?php
error_reporting(0);
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

function gcreatearray($gdata,&$ghai){
  $c=count($gdata);
  for ($i=0;$i<$c;$i++){
    $garr=explode(',',$gdata[$i]);
    $ghai[$i]=$garr; //group name
  }
}

function getgroup($grpno,$ghai){ /// 配列で返る [0]=グル-プ名　[1]=順序　[2]=ホスト数　[3]=段数　[4]=済
  $rtnrec='';
  foreach ($ghai as $ghirec){
    $ghiar = explode(',',$ghirec);
    if ($grpno==$ghiar[1]){
      $rtnrec=$ghirec;
      break;
    }
  }
  return $rtnrec; 
}

function screatearray($hdata,$garr){
/// $hdata:ホストデータ配列, $garr:グループデータ配列
  $hitemarr=array();
  $c=count($hdata);
  for ($i=0;$i<$c;$i++){ 
    $hitem=explode(',',$hdata[$i]);
    $hitemarr[$i]=$hitem;
  }
  $num=0;
  $maxg=count($garr); // 4
  $gname=array();
  for ($i=0;$i<$maxg;$i++){ 
  /// グループ数分繰り返し
    $seg=intval($garr[$i][3]);   ///  セグメント数取得
    $sname=array();
    for ($j=0;$j<$seg;$j++){
      /// セグメント数分繰り返し
      $host=intval($garr[$j][2]);        // ホスト数取得
      $hname=array();
      for ($k=0;$k<$host;$k++){
      /// ホスト数分繰り返し
        $hnamex=array();
        $hname[$k]=$hitemarr[$num][1];
        $num++;
      }
      $sname[$j]=$hname;
    }
    $gname[$i]=$sname;
  }
  return $gname;
}
///                    group  host   layout  user
function layoutsupform($garr,$sarr,$layout,$user){
  $maxgrp=count($garr);                
  print '<form name=myform action=layoutsupmapdb.php method=get>';
  for ($i=0;$i<$maxgrp;$i++){     /// グループ数繰り返し
    $maxseg=intval($garr[$i][3]); /// 1グループ内セグメント取得
    $groupno=strval($i+1);        /// グループ番号取得
    $groupname=$garr[$i][0];      /// グループ名取得
    $groupvalue=$groupno.','.$groupname; /// グループ番号＋グループ名
    print "<br><table><tr><td class=back><b>グループ:</b></td><td><input type=text name='groupname[]' value={$groupname}></td></tr></table>";
    print "<input type='hidden' name='groupno[]' value={$groupno}>";
    print '<table border="1" class="tablelayout">';
    for ($j=0;$j<$maxseg;$j++){   /// 1グループ内セグメントループ
      $maxhost=intval($garr[$j][2]);     /// ホスト数取得
      print '<tr>'; 
      for ($k=0;$k<$maxhost;$k++){       /// ホスト数ループ
        $host=$sarr[$i][$j][$k];  /// ホスト名取得
        if ($host==''){
          $host='NoAssign';
        }
        $datag='g'.strval($i+1);   
        $datas='s'.strval($j);
        $datah='h'.strval($k);
        $datagsh=$datag.$datas.$datah;   /// gsh連結
        $datavalue=$datagsh.','.$host;   /// gsh + ホスト名
        $dataarr='data['.$datag.$datas.$datah.']'; /// 配列へ格納
        print "<td class=back><input type=text name='data[]' value={$host}></td>";
        print "<input type=hidden name='key[]' value={$datagsh}>";
        
      }
      print '</tr>';
    }
    print '</table>';
  }
  print "<input type=hidden name=type value={$layout}>";
  print "<input type=hidden name=user value={$user}>";
  print '<br><input class=button type=submit name=go value="実行">';
  print '</form>';
}

$pgm='layoutsupmap.php';
$layout=$_GET['terms'];
$user=$_GET['user'];

print '<html><head><meta>';
print '<link rel="stylesheet" href="kanshi1.css">';
print '</head><body>';
print "<h2><img src='header/php.jpg' width='30' height='30'>&emsp;&emsp;グループ名、ホスト名変更  ／  レイアウト名：{$layout}</h2>";
print '<h4>現用レイアウトのグループ名、ホスト名／IPアドレスが変更出来ます、変更したら「実行」をクリックして下さい<br>';
print '変更しないものは、そのまま反映されます</h4>';
$gtable='g'.$layout;
$table=$layout;
$garr = array(); /// group array table
$sarr = array(); 

/// group 配列テーブル作成
$sql='select * from '.$gtable;
$gdata=getdata($sql);

gcreatearray($gdata,$garr);

/// ホストテーブル作成
$sql='select * from '.$table;
$sdata=getdata($sql);
///
$sarr=screatearray($sdata,$garr);
///
layoutsupform($garr,$sarr,$layout,$user);
///
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
print '</body></html>';
?>

