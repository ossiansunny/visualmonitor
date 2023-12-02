<?php
error_reporting(0);
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
/// host_data:$hdata, group_data:$garr
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
  //for numg in range(maxg):
    $seg=intval($garr[$i][3]);   //  1 2 1 1
    $sname=array();
    for ($j=0;$j<$seg;$j++){
      $host=intval($garr[$j][2]);        // num of host
      $hname=array();
      for ($k=0;$k<$host;$k++){
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
//                    group  host   layout  user
function layoutsupform($garr,$sarr,$layout,$user){
  $maxgrp=count($garr);                
  echo '<form name=myform action=layoutsupmapdb.php method=get>';
  for ($i=0;$i<$maxgrp;$i++){     /// グループ数繰り返し
    $maxseg=intval($garr[$i][3]); /// 1グループ内セグメント取得
    $groupno=strval($i+1);        /// グループ番号取得
    $groupname=$garr[$i][0];      /// グループ名取得
    $groupvalue=$groupno.','.$groupname; /// グループ番号＋グループ名
    echo "<br><table><tr><td class=back><b>グループ:</b></td><td><input type=text name='groupname[]' value={$groupname}></td></tr></table>";
    echo "<input type='hidden' name='groupno[]' value={$groupno}>";
    echo '<table border="1" class="tablelayout">';
    for ($j=0;$j<$maxseg;$j++){   /// 1グループ内セグメントループ
      $maxhost=intval($garr[$j][2]);     /// ホスト数取得
      echo '<tr>'; 
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
        echo "<td class=back><input type=text name='data[]' value={$host}></td>";
        echo "<input type=hidden name='key[]' value={$datagsh}>";
        
      }
      echo '</tr>';
    }
    echo '</table>';
  }
  echo "<input type=hidden name=type value={$layout}>";
  echo "<input type=hidden name=user value={$user}>";
  echo '<br><input class=button type=submit name=go value="実行">';
  echo '</form>';
}

$pgm='layoutsupmap.php';
$layout=$_GET['terms'];
$user=$_GET['user'];

echo '<html><head><meta>';
echo '<link rel="stylesheet" href="kanshi1.css">';
echo '</head><body>';
echo "<h2>グループ名、ホスト名変更  ／  レイアウト名：{$layout}</h2>";
echo '<h4>現用レイアウトのグループ名、ホスト名／IPアドレスが変更出来ます、変更したら「実行」をクリックして下さい<br>';
echo '変更しないものは、そのまま反映されます</h4>';
$gtable='g'.$layout;
$table=$layout;
$garr = array(); /// group array table
$sarr = array(); 

/// group 配列テーブル作成
$sql='select * from '.$gtable;
$gdata=getdata($sql);

gcreatearray($gdata,$garr);

// ホストテーブル作成
$sql='select * from '.$table;
$sdata=getdata($sql);
$sarr=screatearray($sdata,$garr);

layoutsupform($garr,$sarr,$layout,$user);

echo "<a href='MonitorManager.php?param={$user}'><span class=button>監視モニターへ戻る</span></a>";
echo '</body></html>';
?>
