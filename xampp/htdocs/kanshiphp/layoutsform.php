<?php
print '<html><head>';
print '<link rel="stylesheet" href="kanshi1.css">';
print '</head><body>';

function jdgnwc($jdgdata){
  $jdgrtn="";
  if($jdgdata==""){
    $jdgrtn="sunko";
  }else{
    $jdgarr=explode(':',$jdgdata);
    if($jdgarr[0]=="n"){
      $jdgrtn="snorm";
    }else if($jdgarr[0]=="w"){
      $jdgrtn="swarn";
    }else if($jdgarr[0]=="c"){
      $jdgrtn="scrit";
    }else{
      $jdgrtn="sunko";
    }
  }
  return $jdgrtn; 
}
function jdgnc($jdgdata,$hdata){ // statistics data, host data
  $jdgrtn="";
  if($jdgdata=="" || $jdgdata=="allok" || $jdgdata=="empty"){ // statistics process data が無い=>正常
    if($hdata!=""){ // host process data はある、is not ""
      $jdgrtn="snorm";
    }else{
      $jdgrtn="sunko";
    }
  }else{
    $jdgrtn="scrit";
  }
  return $jdgrtn;
}

function gethostinfo($host){
  $sql='select * from host order by groupname';
  $sdata=getdata($sql);
  $sdatac=count($sdata);  
  $sw=0;
  for($c=0;$c<$sdatac;$c++){
    $sdataarr=explode(',',$sdata[$c]);
    if($host==$sdataarr[0]){
      $rtn=$sdata[$c];
      $sw=1;
      break;
    }
  }
  if($sw==0){
    return ',,,,,,,,,,,,';
  }else{
    return $rtn;
  }
}

function layoutsform($user,$garr,$sarr){
  ///　ホスト数の最大値をみつける
  $sql='select * from glayout order by gsequence';
  $rows=getdata($sql);
  $glc=count($rows);
  $old=0;
  for($nc=0;$nc<$glc;$nc++){
    $glone=explode(',',$rows[$nc]);
    $gln=intval($glone[2]);
    if($gln>$old){
      $old=$gln;
    }
  }
  $wide=strval($old*100);
  $left='45px';
  $hostsw='0';
  $sql='select * from admintb';
  $rows=getdata($sql);
  $adata=explode(',',$rows[0]);
  $kndata=$adata[13]; 
  $grpimg=$adata[14]; 
  $setimg='haikeiimg/'.$grpimg;
  if($kndata=='1'){
    $hostsw='1';
  }
  $hostinfo=array();
  $gc=count($sarr);
  for($gcc=0;$gcc<$gc;$gcc++){
    if ($garr[$gcc][4]=="1"){ 
      $dc=count($sarr[$gcc]);
      $grpname=$garr[$gcc][0].' グループ';
      print '<p style="position: relative;">';
      print "<img src={$setimg} width={$wide} height=30 alt=Group /><br />";
      print "<span style='position: absolute; top: 5px; left: {$left}; width: 250px; color: white; font-weight: bold'>{$grpname}</span>";
      print '</p>';
      print '<table border="0" class="tablelayout">';
      for($dcc=0;$dcc<$dc;$dcc++){
        $hc=count($sarr[$gcc][$dcc]);
        
        // host名表示 管理データのkanrihyoujiが'1'の時表示
        if($hostsw=='1'){
          print '<tr>';
          for($hcc=0;$hcc<$hc;$hcc++){
            if($sarr[$gcc][$dcc][$hcc][0]==''){
              print '<td  align="center" class="host">No Assign</td>';
            }else{
              print "<td  align='center' class='host'>{$sarr[$gcc][$dcc][$hcc][0]}</td>";  //host
              $hostname=$sarr[$gcc][$dcc][$hcc][0];
              $hostdata=gethostinfo($hostname);
              $hostinfo=explode(',',$hostdata);
            }  
          }        
          print '</tr>';        
        }
        // ---------- 表示名処理--------------
        print '<tr>';      
        for($hcc=0;$hcc<$hc;$hcc++){
          $hostname=$sarr[$gcc][$dcc][$hcc][0];
          $hostdata=gethostinfo($hostname);
          $hostinfo=explode(',',$hostdata);
          if($hostinfo[5]==''){ 
            print '<td ></td>'; 
          }else{
            print "<td align=center class='viewname' >{$hostinfo[5]}</td>"; //viewname
          }
        }        
        print '</tr>';
        ///----------- 画像処理----------------
        ///--- hostのaction==0 画像0 actio!=0 & result 1==画像1 action!==2-9 画像2
        print '<tr>';      
        for($hcc=0;$hcc<$hc;$hcc++){
          $hostname=$sarr[$gcc][$dcc][$hcc][0];
          $hostdata=gethostinfo($hostname);
          $hostinfo=explode(',',$hostdata);
          $jumpphp="viewhostspec.php?host=".$hostname."&user=".$user;
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
          $hostname=$sarr[$gcc][$dcc][$hcc][0];
          $hostdata=gethostinfo($hostname); /// tcpport[7] process[11]を使う
          $hostinfo=explode(',',$hostdata); 
          $action=$hostinfo[4]; 
          $result=$hostinfo[3]; 
          $sql="select * from statistics where host='".$hostname."'"; 
          $statdata=getdata($sql); 
          if(empty($statdata[0]) || intval($action) < 2){
            print '<td></td>'; /// statistics データなし,action=0,1
          }else { 
            $statval=explode(',',$statdata[0]);
            if($result != "1"){ /// 障害中 statistics データ表示しない
              print '<td></td>'; 
            }else if(substr($hostname,0,3)!='127'){ ///agent 127.x.x.x以外の処理
              if ($statval[2]=='9'){ /// statisticsのgtype欄
                $agst='aprob';
                print "<td class=snmp align=center><table><tr><td class={$agst} align=center>standby</td></tr></table></td>";
              } else {
                $cb1=jdgnwc($statval[3]); ///cpu
                $cb2=jdgnwc($statval[4]); ///ram
                /// statval[5]はagent 'ok'か'ng'
                $cb3=jdgnwc($statval[6]);  ///disk
                /// 以下、stat="" host="any" なら green表示
                $cb4=jdgnc($statval[7],$hostinfo[11]);  ///process
                $cb5=jdgnc($statval[8],$hostinfo[7]);  ///port            
                print "<td class=snmp align=center><table border=0><tr><td class={$cb1}>c</td><td class={$cb2}>r</td><td class={$cb3}>d</td><td class={$cb4}>p</td><td class={$cb5}>t</td></tr></table></td>";
              }
            }else{ /// 127.x.x.x  Managerの処理
              if($statval[5]=='ok'){ /// statisticsのagent欄
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

