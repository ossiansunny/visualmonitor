<?php
print '<html><head>';
print '</head><body>';
/// global
$bgcolor='';

function jdgnwc($_jdgdata){
  global $bgcolor;
  $jdgRtn="";
  if($_jdgdata==""){
    $jdgRtn=$bgcolor;
  }else{
    $jdgArr=explode(':',$_jdgdata);
    if($jdgArr[0]=="n"){
      $jdgRtn="snorm";
    }else if($jdgArr[0]=="w"){
      $jdgRtn="swarn";
    }else if($jdgArr[0]=="c"){
      $jdgRtn="scrit";
    }else{
      $jdgRtn=$bgcolor;
    }
  }
  return $jdgRtn; 
}
function jdgnc($_jdgdata,$_hdata){ /// statistics data, host data
  global $bgcolor;
  $jdgRtn="";
  if($_jdgdata=="" || $_jdgdata=="allok" || $_jdgdata=="empty"){ /// statistics process data が無い=>正常
    if($_hdata!=""){ /// host process data はある、is not ""
      $jdgRtn="snorm"; /// host dataにprocessまたはportあれば正常表示
    }else{
      $jdgRtn=$bgcolor; /// host dataにprocessまたはportなければ表示せず
    }
  }else{
    $jdgRtn="scrit"; /// allok,empty以外ならば異常表示
  }
  return $jdgRtn;
}

function gethostinfo($_host){
/*
  $host_sql='select * from host order by groupname';
  $hostRows=getdata($host_sql);
  $hostCount=count($hostRows);  
  $breakSw=0;
  $rtnVal="";
  for($c=0;$c<$hostCount;$c++){
    $hostArr=explode(',',$hostRows[$c]);
    if($_host==$hostArr[0]){
      $rtnVal=$hostRows[$c];
      $breakSw=1;
      break;
    }
  }
  if($breakSw==0){
    $rtnVal=',,,,,,,,,,,,';
  }
  return $rtnVal;
*/
  $host_sql='select * from host where host="'.$_host.'"';
  $hostRows=getdata($host_sql);
  if(isset($hostRows)){
    $rtnVal=$hostRows[0];
  }else{
    $rtnVal=',,,,,,,,,,,,';
  }
  return $rtnVal;
}
///
/// 監視ホスト配置
///
function layoutsform($_user,$_userAuth,$_garr,$_sarr){
  /// $_garr=group array, $_sarr=host array
  ///　ホスト数の最大値をみつける
  $layout_sql='select * from glayout order by gsequence';
  $layoutRows=getdata($layout_sql);
  $grpLayoutCount=count($layoutRows);
  $old=0;
  for($nc=0;$nc<$grpLayoutCount;$nc++){
    $layoutArr=explode(',',$layoutRows[$nc]);
    $grpLayoutNumInt=intval($layoutArr[2]);
    if($grpLayoutNumInt>$old){
      $old=$grpLayoutNumInt;
    }
  }
  $wide=strval($old*100);
  $left='45px';
  $hostsw='0';
  $admin_sql='select * from admintb';
  $adminRows=getdata($admin_sql);
  $adminArr=explode(',',$adminRows[0]);
  $hostEnable=$adminArr[13]; /// hosthyoji
  $grpimg=$adminArr[14]; /// haikei
  $bgcolor=$adminArr[17]; /// background color
  $bgImage='haikeiimg/'.$grpimg;
  /// 管理者でホスト名表示の場合のみ
  if($hostEnable=='1' and $_userAuth=='1'){
    $hostsw='1';
  }
  $hostinfo=array();
  $groupCount=count($_garr);  /// garrでもsarrでもグループ数は同じ
  for($gcc=0;$gcc<$groupCount;$gcc++){
    if ($_garr[$gcc][4]=="1"){  /// garrのdataflagが"1"で入力済チェック
      $dc=count($_sarr[$gcc]);  /// sarrのセグメント配列数を得る
      $grpname=$_garr[$gcc][0].' グループ'; /// garrのグループ名をセット
      print '<p style="position: relative;">';
      print "<img src={$bgImage} width={$wide} height=30 alt=Group /><br />";
      print "<span style='position: absolute; top: 5px; left: {$left}; width: 250px; color: white; font-weight: bold'>{$grpname}</span>";
      print '</p>';
      print '<table border="0" class="tablelayout">';
      for($dcc=0;$dcc<$dc;$dcc++){ /// sarrのセグメントの繰り返し
        $hc=count($_sarr[$gcc][$dcc]);  /// 
        
        /// host名表示 管理データのkanrihyoujiが'1'の時表示
        if($hostsw=='1'){
          print '<tr>';
          for($hcc=0;$hcc<$hc;$hcc++){
            if($_sarr[$gcc][$dcc][$hcc][0]=='' or $_sarr[$gcc][$dcc][$hcc][0]=='NoAssign'){
              print '<td  align="center" class="host">No Assign</td>';
            }else{
              print "<td  align='center' class='host'>{$_sarr[$gcc][$dcc][$hcc][0]}</td>";  ///host
            }  
          }        
          print '</tr>';        
        }
        /// ---------- 表示名処理--------------
        print '<tr>';      
        for($hcc=0;$hcc<$hc;$hcc++){
          if($_sarr[$gcc][$dcc][$hcc][0]=='' or $_sarr[$gcc][$dcc][$hcc][0]=='NoAssign'){
            print "<td align=center></td>"; 
          }else{
            $hostname=$_sarr[$gcc][$dcc][$hcc][0];     /// ホスト名抽出
            $hostdata=gethostinfo($hostname);          /// ホストデータ取得
            $hostinfo=explode(',',$hostdata);          /// ホスト情報の配列化
            if($hostinfo[5]==''){ 
              print '<td >&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;</td>'; 
            }else{
              print "<td align=center class='viewname' >{$hostinfo[5]}</td>"; ///viewname
            }
          }
        }        
        print '</tr>';
        ///----------- 画像処理----------------
        ///--- hostのaction==0 画像0 actio!=0 & result 1==画像1 action!==2-9 画像2
        print '<tr>';      
        for($hcc=0;$hcc<$hc;$hcc++){
          if($_sarr[$gcc][$dcc][$hcc][0]=='' or $_sarr[$gcc][$dcc][$hcc][0]=='NoAssign'){
            print "<td align=center class=host></a></td>";
          }else{
            $hostname=$_sarr[$gcc][$dcc][$hcc][0];
            $hostdata=gethostinfo($hostname);
            $hostinfo=explode(',',$hostdata);
            $jumpphp="viewhostspec.php?host=".$hostname."&user=".$_user;
            if($hostinfo[12]==''){  /// image
              print "<td align=center class=host><a href={$jumpphp}></a></td>";
            }else{
              $imgsep=explode('.',$hostinfo[12]);  ///imageを.ファイルと拡張子に分解
              /// ここで画像を選択 
              if($hostinfo[4]=='0'){ /// action=0
                $img='hostimage\\'.$imgsep[0].'0.'.$imgsep[1];
                print "<td align=center class=unkown ><a href={$jumpphp}><img src={$img} width='70px' height='60px' ></a></td>"; 
              }else if($hostinfo[4]!='0' && $hostinfo[3]=='1'){ /// action=0以外 result=1 正常
                $img='hostimage\\'.$imgsep[0].'1.'.$imgsep[1];
                print "<td align=center class=normal ><a href={$jumpphp}><img src={$img} width='70px' height='60px' ></a></td>"; 
              }else if($hostinfo[4]!='0' && $hostinfo[3]=='2'){ /// action=0以外 result=2 異常
                $img='hostimage\\'.$imgsep[0].'2.'.$imgsep[1];
                print "<td align=center class=alarm ><scan class=blink><a href={$jumpphp}><img src={$img} width='70px' height='60px' ></a></scan></td>"; 
              }else{ /// action=0以外　result=3-9
                $img='hostimage\\'.$imgsep[0].'2.'.$imgsep[1];
                print "<td align=center class=alarm ><a href={$jumpphp}><img src={$img} width='70px' height='60px' ></a></td>"; 
              }
            }             
          }
        }  
        print '</tr>';
        ///-----------SNMP処理--------------------
        print '<tr>';   /// 横一列   
        for($hcc=0;$hcc<$hc;$hcc++){
          if($_sarr[$gcc][$dcc][$hcc][0]=='' or $_sarr[$gcc][$dcc][$hcc][0]=='NoAssign'){
            print "<td></td>";
          }else{
            $hostname=$_sarr[$gcc][$dcc][$hcc][0];
            $hostdata=gethostinfo($hostname); /// tcpport[7] process[11]を使う
            $hostinfo=explode(',',$hostdata); 
            $action=$hostinfo[4]; 
            $result=$hostinfo[3]; 
            $stat_sql="select * from statistics where host='".$hostname."'"; 
            $statRows=getdata($stat_sql);
            if(isset($statRows)){
              if(empty($statRows[0]) || intval($action) < 2){
                print '<td></td>'; /// statistics データなし,action=0,1
              }else { 
                $statArr=explode(',',$statRows[0]);
                /// statArr[0] host
                /// statArr[1] tstamp
                /// statArr[2] gtype
                /// statArr[3] cpuval
                /// statArr[4] ramval
                /// statArr[5] agent 'ok' or 'ng'
                /// statArr[6] diskval
                /// statArr[7] process
                /// statArr[8] tcpport
                if($result != "1"){ /// 障害中 statistics データ表示しない
                  print '<td></td>'; 
                }else if(substr($hostname,0,8)!='127.0.0.'){ ///agent 127.0.0.x以外の処理
                  if ($statArr[2]=='9'){ /// statisticsのgtype欄
                    $agst='aprob';
                    print "<td align=center><table><tr><td class={$agst} align=center>standby</td></tr></table></td>";
                    $msg=$hostname.' set snmp standby ';
                    writelogd('layousform.php',$msg);
                  } else {
                    $cb1=jdgnwc($statArr[3]); ///cpu
                    $cb2=jdgnwc($statArr[4]); ///ram
                    $cb3=jdgnwc($statArr[6]);  ///disk
                    $cb4=jdgnc($statArr[7],$hostinfo[11]);  ///$hostinfo[11]はprocess情報
                    $cb5=jdgnc($statArr[8],$hostinfo[7]);  ///$hostinfo[7]はport情報        
                    print "<td align=center><table border=0><tr>";
                    print "<td class={$cb1}>c</td>";
                    print "<td class={$cb2}>r</td>";
                    print "<td class={$cb3}>d</td>";
                    print "<td class={$cb4}>p</td>";
                    print "<td class={$cb5}>t</td>";
                    print "</tr></table></td>";
                  }
                }else{ /// 127.0.0.x  Managerの処理
                  if($statArr[5]=='ok'){ /// statisticsのagent欄
                    $amsg='No Problem ';
                    $agst='snorm';
                  }else if($statArr[5]=='ng') {
                    $amsg='Problem ';
                    $agst='scrit';
                  }else{
                    $amsg='Standby ';
                    $agst='swarn';
                  }
                  print "<td align=center><table><tr><td align=center class={$agst}>{$amsg}</td></tr></table></td>";
                }
              }            
            }else{
              $msg='mysql error or no data:'.$stat_sql;
              writeloge($pgm,$msg);
              $amsg='No statis ';
              $agst='scrit';
              print "<td class=snmp align=center><table><tr><td align=center class={$agst}>{$amsg}</td></tr></table></td>";
            }
          }
        }
        print '</tr>';
      }
      print '</table>';
    }
  }
}
print '</body></html>';
?>

