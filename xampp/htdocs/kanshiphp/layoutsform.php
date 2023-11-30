<html>
<head>
<link rel="stylesheet" href="kanshi1.css">
</head>
<body>

<?php
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
  //var_dump($sdata);
  $sdatac=count($sdata);  
  $sw=0;
  for($c=0;$c<$sdatac;$c++){
    $sdataarr=explode(',',$sdata[$c]);
    if($host==$sdataarr[0]){
      $rtn=$sdata[$c];
      //var_dump($rtn);
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
  // find max host number
  $sql='select * from glayout order by gsequence';
  $rows=getdata($sql);
  //var_dump($sarr);
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
  //echo $wide.','.$left;
// $gln max host number
  $hostsw='0';
  $sql='select * from admintb';
  $rows=getdata($sql);
  $adata=explode(',',$rows[0]);
  $kndata=$adata[13]; // start 0 and 13th hosthyoji
  $grpimg=$adata[14]; // get group image
  $setimg='haikeiimg/'.$grpimg;
  //echo $kndata.','.$grpimg.','.$setimg;
  if($kndata=='1'){
    $hostsw='1';
  }
  $hostinfo=array();
  $gc=count($sarr);
  //echo '------------- $gc data is '.strval($gc).'<br>';
  for($gcc=0;$gcc<$gc;$gcc++){
    if ($garr[$gcc][4]=="1"){ // dataflag 1 only
      $dc=count($sarr[$gcc]);
      //echo '------------- $dc data is '.strval($dc).'<br>';
      $grpname=$garr[$gcc][0].' グループ';
      //echo '<img src="'.$setimg.'" width="'.$wide.'">';
      echo '<p style="position: relative;">';
      echo "<img src={$setimg} width={$wide} height=30 alt=Group /><br />";
      echo "<span style='position: absolute; top: 5px; left: {$left}; width: 250px; color: white; font-weight: bold'>{$grpname}</span>";
      echo '</p>';
      //echo '<h3>'.$garr[$gcc][0].' グループ</h3>';
      echo '<table border="0" class="tablelayout">';
      for($dcc=0;$dcc<$dc;$dcc++){
        $hc=count($sarr[$gcc][$dcc]);
        //echo '------------- $hc data is '.strval($hc).'<br>';
      
        // host名表示 管理データのkanrihyoujiが'1'の時表示
        if($hostsw=='1'){
          echo '<tr>';
          for($hcc=0;$hcc<$hc;$hcc++){
            if($sarr[$gcc][$dcc][$hcc][0]==''){
              echo '<td  align="center" class="host">No Assign</td>';
            }else{
              echo "<td  align='center' class='host'>{$sarr[$gcc][$dcc][$hcc][0]}</td>";  //host
              $hostname=$sarr[$gcc][$dcc][$hcc][0];
              $hostdata=gethostinfo($hostname);
              //echo '<br>';
              //var_dump($hostdata);
              //echo '<br>';
              $hostinfo=explode(',',$hostdata);
            }  
          }        
          echo '</tr>';        
        }
        // ---------- 表示名処理--------------
        echo '<tr>';      
        for($hcc=0;$hcc<$hc;$hcc++){
          //echo '<td class="host">'.$sarr[$gcc][$dcc][$hcc][1].'</td>'; //viewname
          $hostname=$sarr[$gcc][$dcc][$hcc][0];
          $hostdata=gethostinfo($hostname);
          $hostinfo=explode(',',$hostdata);
          if($hostinfo[5]==''){ // no viewname
            echo '<td ></td>'; //host dataなし
          }else{
            echo "<td align=center class='viewname' >{$hostinfo[5]}</td>"; //viewname
          }
        }        
        echo '</tr>';
        //----------- 画像処理----------------
        //--- hostのaction==0 画像0 actio!=0 & result 1==画像1 action!==2-9 画像2
        echo '<tr>';      
        for($hcc=0;$hcc<$hc;$hcc++){
          $hostname=$sarr[$gcc][$dcc][$hcc][0];
          $hostdata=gethostinfo($hostname);
          $hostinfo=explode(',',$hostdata);
          $jumpphp="viewhostspec.php?host=".$hostname."&user=".$user;
          if($hostinfo[12]==''){  //image
            echo "<td align=center class=host><a href={$jumpphp}></a></td>";
            //echo '<td align=center class=host><a href="'.$jumpphp.'">未割当/ホスト無</a></td>';
          }else{
            $imgsep=explode('.',$hostinfo[12]);  //imageを.ファイルと拡張子に分解
            // ここで画像を選択 
            if($hostinfo[4]=='0'){ // action=0
              $img='hostimage\\'.$imgsep[0].'0.'.$imgsep[1];
              echo "<td align=center class=unkown ><a href={$jumpphp}><img src={$img} width='70px' height='60px' ></a></td>"; //ima
            }else if($hostinfo[4]!='0' && $hostinfo[3]=='1'){ // action=0以外 result=1 正常
              $img='hostimage\\'.$imgsep[0].'1.'.$imgsep[1];
              echo "<td align=center class=normal ><a href={$jumpphp}><img src={$img} width='70px' height='60px' ></a></td>"; //ima            
            }else if($hostinfo[4]!='0' && $hostinfo[3]=='2'){ // action=0以外 result=2 異常
              $img='hostimage\\'.$imgsep[0].'2.'.$imgsep[1];
              echo "<td align=center class=alarm ><scan class=blink><a href={$jumpphp}><img src={$img} width='70px' height='60px' ></a></scan></td>"; //ima
            }else{ // action=0以外　result=3-9
              $img='hostimage\\'.$imgsep[0].'2.'.$imgsep[1];
              echo "<td align=center class=alarm ><a href={$jumpphp}><img src={$img} width='70px' height='60px' ></a></td>"; //ima
            }             
          }
        }  
        echo '</tr>';
        //-----------SNMP処理--------------------
        echo '<tr>';   // 横一列   
        for($hcc=0;$hcc<$hc;$hcc++){
          $hostname=$sarr[$gcc][$dcc][$hcc][0];
          $hostdata=gethostinfo($hostname); // tcpport[7] process[11]を使う
          $hostinfo=explode(',',$hostdata); 
          $action=$hostinfo[4]; //save action
          $result=$hostinfo[3]; //save result
          $sql="select * from statistics where host='".$hostname."'"; 
          $statdata=getdata($sql); // statistics get
          // host time gtype cpuval ranval agent diskval process port
          // out of target is no statisstics record or action 0,1
          if(empty($statdata[0]) || intval($action) < 2){
            //echo 'No data<br>';
            echo '<td></td>'; // statistics データなし,action=0,1
          }else { 
            $statval=explode(',',$statdata[0]);
            if($result != "1"){ // 障害中 statistics データ表示しない
              echo '<td></td>'; 
            }else if($hostname!='127.0.0.1'){ //agent以外の処理
              if ($statval[2]=='9'){
                $agst='aprob';
                echo "<td class=snmp align=center><table><tr><td class={$agst} align=center>standby</td></tr></table></td>";
              } else {
                $cb1=jdgnwc($statval[3]); //cpu
                $cb2=jdgnwc($statval[4]); //ram
                // statval[5]はagent 'ok'か'ng'
                $cb3=jdgnwc($statval[6]);  //disk
                // 以下、stat="" host="any" なら green表示
                $cb4=jdgnc($statval[7],$hostinfo[11]);  //process
                $cb5=jdgnc($statval[8],$hostinfo[7]);  //port            
                echo "<td class=snmp align=center><table border=0><tr><td class={$cb1}>c</td><td class={$cb2}>r</td><td class={$cb3}>d</td><td class={$cb4}>p</td><td class={$cb5}>t</td></tr></table></td>";
              }
            }else{ // 127.0.0.1  Managerの処理
              //echo "-----> 127.0.0.1 agent is ".$statval[5]."<br>";
              if($statval[5]=='ok'){
                $amsg='No Problem ';
                $agst='snorm';
              }else{
                $amsg='Problem ';
                $agst='aprob';
              }
              echo "<td class=snmp align=center><table><tr><td align=center class={$agst}>{$amsg}</td></tr></table></td>";
            }
          }
        }
        echo '</tr>';
      }
      echo '</table>';
    }
  }
}
?>
</body>
</html>
