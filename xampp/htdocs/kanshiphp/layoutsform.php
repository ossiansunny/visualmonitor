<?php
print '<html><head>';
print '<link rel="stylesheet" href="css/kanshi1.css">';
print '</head><body>';

function jdgnwc($_jdgdata){
  $jdgRtn="";
  if($_jdgdata==""){
    $jdgRtn="sunko";
  }else{
    $jdgArr=explode(':',$_jdgdata);
    if($jdgArr[0]=="n"){
      $jdgRtn="snorm";
    }else if($jdgArr[0]=="w"){
      $jdgRtn="swarn";
    }else if($jdgArr[0]=="c"){
      $jdgRtn="scrit";
    }else{
      $jdgRtn="sunko";
    }
  }
  return $jdgRtn; 
}
function jdgnc($_jdgdata,$_hdata){ // statistics data, host data
  $jdgRtn="";
  if($_jdgdata=="" || $_jdgdata=="allok" || $_jdgdata=="empty"){ // statistics process data が無い=>正常
    if($_hdata!=""){ // host process data はある、is not ""
      $jdgRtn="snorm";
    }else{
      $jdgRtn="sunko";
    }
  }else{
    $jdgRtn="scrit";
  }
  return $jdgRtn;
}

function gethostinfo($_host){
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
}

function layoutsform($_user,$_garr,$_sarr){
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
  $hostEnable=$adminArr[13]; // hosthyoji
  $grpimg=$adminArr[14]; // haikei
  $bgImage='haikeiimg/'.$grpimg;
  if($hostEnable=='1'){
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
        
        // host名表示 管理データのkanrihyoujiが'1'の時表示
        if($hostsw=='1'){
          print '<tr>';
          for($hcc=0;$hcc<$hc;$hcc++){
            if($_sarr[$gcc][$dcc][$hcc][0]=='' or $_sarr[$gcc][$dcc][$hcc][0]=='NoAssign'){
              print '<td  align="center" class="host">No Assign</td>';
            }else{
              print "<td  align='center' class='host'>{$_sarr[$gcc][$dcc][$hcc][0]}</td>";  //host
              //$hostname=$_sarr[$gcc][$dcc][$hcc][0];  /// ホスト名抽出
              //$hostdata=gethostinfo($hostname);      /// ホストデータ取得
              //$hostinfo=explode(',',$hostdata);      /// ホスト情報の配列化
            }  
          }        
          print '</tr>';        
        }
        /// ---------- 表示名処理--------------
        print '<tr>';      
        for($hcc=0;$hcc<$hc;$hcc++){
          $hostname=$_sarr[$gcc][$dcc][$hcc][0];     /// ホスト名抽出
          $hostdata=gethostinfo($hostname);          /// ホストデータ取得
          $hostinfo=explode(',',$hostdata);          /// ホスト情報の配列化
          if($hostinfo[5]==''){ 
            print '<td >&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;</td>'; 
          }else{
            print "<td align=center class='viewname' >{$hostinfo[5]}</td>"; //viewname
          }
        }        
        print '</tr>';
        ///----------- 画像処理----------------
        ///--- hostのaction==0 画像0 actio!=0 & result 1==画像1 action!==2-9 画像2
        print '<tr>';      
        for($hcc=0;$hcc<$hc;$hcc++){
          $hostname=$_sarr[$gcc][$dcc][$hcc][0];
          $hostdata=gethostinfo($hostname);
          $hostinfo=explode(',',$hostdata);
          $jumpphp="viewhostspec.php?host=".$hostname."&user=".$_user;
          if($hostinfo[12]==''){  /// image
            print "<td align=center class=host><a href={$jumpphp}></a></td>";
          }else{
            $imgsep=explode('.',$hostinfo[12]);  //imageを.ファイルと拡張子に分解
            /// ここで画像を選択 
            if($hostinfo[4]=='0'){ /// action=0
              $img='hostimage\\'.$imgsep[0].'0.'.$imgsep[1];
              print "<td align=center class=unkown ><a href={$jumpphp}><img src={$img} width='70px' height='60px' ></a></td>"; //ima
            }else if($hostinfo[4]!='0' && $hostinfo[3]=='1'){ /// action=0以外 result=1 正常
              $img='hostimage\\'.$imgsep[0].'1.'.$imgsep[1];
              print "<td align=center class=normal ><a href={$jumpphp}><img src={$img} width='70px' height='60px' ></a></td>"; //ima            
            }else if($hostinfo[4]!='0' && $hostinfo[3]=='2'){ /// action=0以外 result=2 異常
              $img='hostimage\\'.$imgsep[0].'2.'.$imgsep[1];
              print "<td align=center class=alarm ><scan class=blink><a href={$jumpphp}><img src={$img} width='70px' height='60px' ></a></scan></td>"; //ima
            }else{ /// action=0以外　result=3-9
              $img='hostimage\\'.$imgsep[0].'2.'.$imgsep[1];
              print "<td align=center class=alarm ><a href={$jumpphp}><img src={$img} width='70px' height='60px' ></a></td>"; //ima
            }             
          }
        }  
        print '</tr>';
        ///-----------SNMP処理--------------------
        print '<tr>';   /// 横一列   
        for($hcc=0;$hcc<$hc;$hcc++){
          $hostname=$_sarr[$gcc][$dcc][$hcc][0];
          $hostdata=gethostinfo($hostname); /// tcpport[7] process[11]を使う
          $hostinfo=explode(',',$hostdata); 
          $action=$hostinfo[4]; 
          $result=$hostinfo[3]; 
          $stat_sql="select * from statistics where host='".$hostname."'"; 
          $statRows=getdata($stat_sql); 
          if(empty($statRows[0]) || intval($action) < 2){
            print '<td></td>'; /// statistics データなし,action=0,1
          }else { 
            $statArr=explode(',',$statRows[0]);
            if($result != "1"){ /// 障害中 statistics データ表示しない
              print '<td></td>'; 
            }else if(substr($hostname,0,3)!='127'){ ///agent 127.x.x.x以外の処理
              if ($statArr[2]=='9'){ /// statisticsのgtype欄
                $agst='aprob';
                print "<td class=snmp align=center><table><tr><td class={$agst} align=center>standby</td></tr></table></td>";
                $msg=$hostname.' set snmp standby ';
                writelogd('layousform.php',$msg);
              } else {
                $cb1=jdgnwc($statArr[3]); ///cpu
                $cb2=jdgnwc($statArr[4]); ///ram
                /// statval[5]はagent 'ok'か'ng'
                $cb3=jdgnwc($statArr[6]);  ///disk
                /// 以下、stat="" host="any" なら green表示
                $cb4=jdgnc($statArr[7],$hostinfo[11]);  ///process
                $cb5=jdgnc($statArr[8],$hostinfo[7]);  ///port            
                print "<td class=snmp align=center><table border=0><tr><td class={$cb1}>c</td><td class={$cb2}>r</td><td class={$cb3}>d</td><td class={$cb4}>p</td><td class={$cb5}>t</td></tr></table></td>";
              }
            }else{ /// 127.x.x.x  Managerの処理
              if($statArr[5]=='ok'){ /// statisticsのagent欄
                $amsg='No Problem ';
                $agst='snorm';
              }else{
                $amsg='Problem ';
                $agst='aprob';
              }
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

