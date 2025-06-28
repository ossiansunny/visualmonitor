<html>
<head>
<meta http-equiv="Content-Type=" content="text/html;charset=utf-8">
</head>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script type="text/javascript">
<!--
function viewPopup(data) {
  swal.fire({
    title: '',
    width:250,
    height:600,
    html: data,
    showConfirmButton: false,
    confirmButtonText: 'クローズ',
    background: '#dcdcdc',
//background: '#FFFF00',
  });
}
-->
</script>
</html>

<?php
/// global
$bgcolor='';
$audioSrc="";

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

function popupdataget($_host){
  $bgHost='';
  $popupArr=array();
  $popupData='';
  $host_sql="select result,action from host where host='".$_host."'";
  $hostRows=getdata($host_sql);
  if (! empty($hostRows)){
    $hostArr=explode(',',$hostRows[0]);
    $action=$hostArr[1];
    $result=$hostArr[0];
    if ($action=='0'){
      $bgHost='aprob';
    }elseif($result=='1'){
      $bgHost='snorm';
    }else{
      $bgHost='scrit';
    }
    array_push($popupArr,'<p><span class='.$bgHost.'>'.$_host.'</span></p>');
    array_push($popupArr,'<p>Action='.$action.'<br>');
    array_push($popupArr,'Result='.$result.'</p>');
    if($action=='2' or $action=='3'){
      $stat_sql="select host,tstamp,gtype,cpuval,ramval,agent,diskval,process,tcpport from statistics where host='".$_host."'";
      $statRows=getdata($stat_sql);
      if (! empty($statRows)){
        $statArr=explode(',',$statRows[0]);
        /// cpu
        if ($statArr[3]=='empty') {
          $cpu='測定なし';
          $popcpuattr='popupwarn';
        }else{
          if(substr($statArr[3],0,1)=='w'){
            $popcpuattr='popupwarn';
          }elseif(substr($statArr[3],0,1)=='c'){
            $popcpuattr='popupcrit';
          }else{
            $popcpuattr='popupnorm';
          }
          $cpu=$statArr[3].'%';
        }
        /// ram
        if ($statArr[4]=='empty') {
          $ram='測定なし';
          $popramattr='popupwarn';
        }else{
          if(substr($statArr[4],0,1)=='w'){
            $popramattr='popupwarn';
          }elseif(substr($statArr[4],0,1)=='c'){
            $popramattr='popupcrit';
          }else{
            $popramattr='popupnorm';
          }
          $ram=$statArr[4].'%';
        }
        /// disk
        if ($statArr[6]=='empty') {
          $disk='測定なし';
          $popdiskattr='popupwarn';
        }else{
          if(substr($statArr[6],0,1)=='w'){
            $popdiskattr='popupwarn';
          }elseif(substr($statArr[6],0,1)=='c'){
            $popdiskattr='popupcrit';
          }else{
            $popdiskattr='popupnorm';
          }
          $disk=$statArr[6].'%';
        }

        $proc=$statArr[7];
        /// process
        if($proc=='empty' or $proc==''){
          $proc='指定なし';
          $popprocattr='popupnorm';
        }elseif($proc=='allok'){
          $proc='なし';
          $popprocattr='popupnorm';
        }elseif($proc=='unknown'){
          $proc='不明';
          $popprocattr='popupwarn';
        }else{
          $popprocattr='popupcrit';
        }
        $port=$statArr[8];
        /// TcpPort
        if ($port=='empty' or $port==''){
          $port='指定なし';
          $popportattr='popupnorm';
        }elseif($port=='allok'){
          $port='なし';
          $popportattr='popupnorm';
        }elseif($port=='unknown'){
          $port='不明';
          $popportattr='popupwarn';
        }else{
          $popportattr='popupcrit';
        }
        
        array_push($popupArr,'<p>SNMP情報</p>');
        array_push($popupArr,'<table border=1 align=center bgcolor=lemonchiffon>');
        array_push($popupArr,'<tr><td align=center class='.$popcpuattr.'>Cpu Load:</td><td class='.$popcpuattr.'>'.$cpu.'</td></span></tr>');
        array_push($popupArr,'<tr><td align=center class='.$popramattr.'>Ram Load:</td><td class='.$popramattr.'>'.$ram.'</td></tr>');
        array_push($popupArr,'<tr><td align=center class='.$popdiskattr.'>Disk Load:</td><td class='.$popdiskattr.'>'.$disk.'</td></tr>');
        array_push($popupArr,'<tr><td align=center class='.$popprocattr.'>Dead Process:</td><td class='.$popprocattr.'>'.$proc.'</td></tr>');
        array_push($popupArr,'<tr><td align=center class='.$popportattr.'>Close TcpPort:</td><td class='.$popportattr.'>'.$port.'</td></tr>');
        array_push($popupArr,'</table>');
      }else{
        array_push($popupArr,'<p>SNMP情報</p>');
        array_push($popupArr,'<p>No data</p>');
      }
    }elseif($action=='5'){
      $stat_sql="select host,tstamp,gtype,cpuval,ramval,agent,diskval,process,tcpport from statistics where host='".$_host."'";
      $statRows=getdata($stat_sql);
      if (! empty($statRows)){
        $statArr=explode(',',$statRows[0]);
        $port=$statArr[8];
        /// TcpPort
        if ($port=='empty' or $port==''){
          $port='指定なし';
          $popportattr='popupnorm';
        }elseif($port=='allok'){
          $port='なし';
          $popportattr='popupnorm';
        }elseif($port=='unknown'){
          $port='不明';
          $popportattr='popupwarn';
        }else{
          $popportattr='popupcrit';
        }        
        array_push($popupArr,'<p>Ncat Port情報</p>');
        array_push($popupArr,'<table border=1 align=center bgcolor=lemonchiffon>');
        array_push($popupArr,'<tr><td align=center class='.$popportattr.'>Close TcpPort:</td><td class='.$popportattr.'>'.$port.'</td></tr>');
        array_push($popupArr,'</table>');
      }else{
        array_push($popupArr,'<p>Ncat Port情報</p>');
        array_push($popupArr,'<p>No data</p>');
      }
    }
  }else{
    array_push($popupArr,"<p>'".$_host."'</p>");
    array_push($popupArr,"<p>エラー、ホストデータなし</p>");  
  }
  foreach ($popupArr as $popupRec){
    $popupData=$popupData.$popupRec;
  }
  return $popupData;
}

function gethostinfo($_host){
  $host_sql='select host,groupname,ostype,result,action,viewname,mailopt,tcpport,cpulim,ramlim,disklim,process,image,snmpcomm,agenthost,eventlog,standby from host where host="'.$_host.'"';
  $hostRows=getdata($host_sql);
  if(! empty($hostRows)){
    $rtnVal=$hostRows[0];
  }else{
    $rtnVal=',,,,,,,,,,,,,,,';
  }
  return $rtnVal;
}
///
/// 監視ホスト配置
///
function monitorlayout($_user,$_userAuth,$_bgcolor,$_audio,$_garr,$_sarr){
  /// $_garr=group array, $_sarr=host array
  ///　ホスト数の最大値をみつける
  $pgm='monitorlayout';
  $audioSrc='audio/'.$_audio;
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
  $charColor='';
  $bgcolor=$_bgcolor;
  $admin_sql='select hosthyouji,haikei from admintb';
  $adminRows=getdata($admin_sql);
  $adminArr=explode(',',$adminRows[0]);
  $hostEnable=$adminArr[0]; /// hosthyoji
  $grpimg=$adminArr[1]; /// haikei
  $bgImage='haikeiimg/'.$grpimg;
  ///
  /// 管理者でホスト名表示の場合、一般オペレータ画面はホスト名表示なし
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
      ///
      for($dcc=0;$dcc<$dc;$dcc++){ /// sarrのセグメントの繰り返し
        $hc=count($_sarr[$gcc][$dcc]);  /// 
        
        /// host名表示 管理データのkanrihyoujiが'1'の時表示
        if($hostsw=='1'){
          print '<tr>';
          for($hcc=0;$hcc<$hc;$hcc++){
            if($_sarr[$gcc][$dcc][$hcc][0]=='' or $_sarr[$gcc][$dcc][$hcc][0]=='NoAssign'){
              print "<td  align='center' class='host'>No Assign</td>";              
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
            print "<td align=center >&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;</td>"; 
          }else{
            $hostname=$_sarr[$gcc][$dcc][$hcc][0];     /// ホスト名抽出
            $hostdata=gethostinfo($hostname);          /// ホストデータ取得
            $hostinfo=explode(',',$hostdata);          /// ホスト情報の配列化
            $hostViewName=$hostinfo[5]; 
            if($hostViewName==''){ 
              print '<td >&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;</td>'; 
            }else{
              print "<td align=center class='viewname' >{$hostViewName}</td>"; ///viewname
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
            $hostResult=$hostinfo[3];
            $hostAction=$hostinfo[4];
            $hostImage=$hostinfo[12];
            $stat_sql="select host,tstamp,gtype,cpuval,ramval,agent,diskval,process,tcpport from statistics where host='".$hostname."'"; 
            $statRows=getdata($stat_sql);
            if(empty($statRows[0])){
              $statGtype="";
            }else{ 
              $statArr=explode(',',$statRows[0]);                          //////
              $statGtype=$statArr[2];
            }
            /// hopup action and snmp information
            //////////////////////////////////////////////////
            $popupRtnData=popupdataget($hostname);
            ////////////////////////////////////////////////// 
            $jumpphp="viewhostspec.php?host=".$hostname."&user=".$_user;
            if($hostImage==''){  /// image ???????????????????????????????????????????
              print "<td align=center class=host><a href={$jumpphp}></a></td>";
            }else{
              $imgsep=explode('.',$hostImage);  ///imageを.ファイルと拡張子に分解
              $imageName=$imgsep[0];
              $imageExt=$imgsep[1];  
              /// ここで画像を選択 
              if($hostAction=='0' or $statGtype=='9'){ /// action=0 or standby=9
                $img='hostimage\\'.$imageName.'0.'.$imageExt;
                print '<td align=center class=unkown><a href="" onclick="viewPopup(\''.$popupRtnData.'\'); return false;"><img src="'.$img.'" width="70px" height="60px" ></a></td>'; 
                
              }else if($hostAction!='0' and $hostResult=='1'){ /// action=0以外 result=1 正常
                $img='hostimage\\'.$imageName.'1.'.$imageExt;
                
                print '<td align=center class=normal ><a href="" onclick="viewPopup(\''.$popupRtnData.'\'); return false;"><img src="'.$img.'" width="70px" height="60px" ></a></td>'; 
              }else if($hostAction!='0' and $hostResult=='2'){ /// action=0以外 result=2 異常
                $img='hostimage\\'.$imageName.'2.'.$imageExt;
                print '<td align=center class=alarm ><scan class=blink><a href="" onclick="viewPopup(\''.$popupRtnData.'\'); return false;"><img src="'.$img.'" width="70px" height="60px" ></a></scan></td>'; 
                /// alert                
                print "<audio src={$audioSrc} autoplay></audio>";
                writelogd($pgm,'result=2 '.$audioSrc);
                ///
              }else{ /// action=0以外　result=3-9
                $img='hostimage\\'.$imageName.'2.'.$imageExt;
                print '<td align=center class=alarm ><a href="" onclick="viewPopup(\''.$popupRtnData.'\'); return false;"><img src="'.$img.'" width="70px" height="60px" ></a></td>'; 
                print "<audio src={$audioSrc} autoplay></audio>";
                writelogd($pgm,'result!=2 '.$audioSrc);
              }             
            }
          }
        }  
        print '</tr>';
        ///-----------SNMP処理--------------------
        print '<tr>';   /// 横一列   
        for($hcc=0;$hcc<$hc;$hcc++){
          /// ホストなし
          if($_sarr[$gcc][$dcc][$hcc][0]=='' or $_sarr[$gcc][$dcc][$hcc][0]=='NoAssign'){
            print "<td></td>";
          }else{
          /// ホストあり  
            $hostname=$_sarr[$gcc][$dcc][$hcc][0];
            $hostdata=gethostinfo($hostname); /// tcpport[7] process[11]を使う
            $hostinfo=explode(',',$hostdata); 
            $action=$hostinfo[4]; 
            $result=$hostinfo[3]; 
            $standby=$hostinfo[16];           /// host standby列
            
            ///
            /// standbyの場合、standbyの表示をする
            ///
            if($standby=='1'){
              $popData="<font color=sienna></font>監視コア実行待ち//ホスト情報変更により監視実行は監視コアの次のリフレッシュまで待機される";
              $popupRtnData=popupMsgSet($popData);
              $amsg='<a  style="text-decoration:none;" href="" onclick="viewPopup(\''.$popupRtnData.'\'); return false;">Standby</a>';
              print "<td class=snmp align=center><table><tr><td class=aprob align=center>{$amsg}</td></tr></table></td>"; 
            }else{
              /// その他通常データのstatistics
              $stat_sql="select host,tstamp,gtype,cpuval,ramval,agent,diskval,process,tcpport from statistics where host='".$hostname."'"; //////
              $statRows=getdata($stat_sql);                                    //////
              if(! empty($statRows)){                                            //////
                if(empty($statRows[0]) || intval($action) < 2){
                  print '<td></td>'; /// statistics データなし,action=0,1
                }else { 
                  $statArr=explode(',',$statRows[0]);                          //////
                  /// statArr[0] host
                  /// statArr[1] tstamp
                  /// statArr[2] gtype '9:host standby'
                  /// statArr[3] cpuval
                  /// statArr[4] ramval
                  /// statArr[5] agent 'ok' or 'ng' or 'sb:127.0.0.1 standby'
                  /// statArr[6] diskval
                  /// statArr[7] process
                  /// statArr[8] tcpport
                  if($result != "1"){ /// 障害中 statistics データ表示しない
                    print '<td></td>'; 
                  }else if(substr($hostname,0,8)!='127.0.0.'){ 
                    ///agent 127.0.0.x以外の処理 statisticsのgtype=9
                    if ($statArr[2]=='9'){ 
                      /// statisticsのgtype=9で,standbyを表示
                      $popData="<font color=sienna></font>監視コア実行待ち//ホスト情報変更により監視実行は監視コアの次のリフレッシュまで待機される";
                      $popupRtnData=popupMsgSet($popData);
                      $amsg='<a  style="text-decoration:none;" href="" onclick="viewPopup(\''.$popupRtnData.'\'); return false;">Standby</a>';
                      print "<td class=snmp align=center><table><tr><td class=aprob align=center>{$amsg}</td></tr></table></td>";
                      $msg=$hostname.' set snmp standby ';
                      writelogd('layousform.php',$msg);
                    } elseif($action=='5'){
                      $cb5=jdgnc($statArr[8],$hostinfo[7]);
                      if ($cb5=='scrit') {
                        print "<audio src={$audioSrc} autoplay></audio>";
                      }
                      print "<td align=center>";
                        print "<table border=0>";
                          print "<tr><td>Ncat</td><td class={$cb5}>port</td></tr>";
                        print "</table>";
                      print "</td>";      
                    } else {
                      $cb1=jdgnwc($statArr[3]); ///cpu
                      $cb2=jdgnwc($statArr[4]); ///ram
                      $cb3=jdgnwc($statArr[6]);  ///disk
                      /// 以下、stat="" host="any" なら green表示
                      $cb4=jdgnc($statArr[7],$hostinfo[11]);  ///process
                      if ($cb4=='scrit') {
                        print "<audio src={$audioSrc} autoplay></audio>";
                      }
                      $cb5=jdgnc($statArr[8],$hostinfo[7]);  ///port
                      if ($cb5=='scrit') { 
                        print "<audio src={$audioSrc} autoplay></audio>";                      
                      }
                      print "<td align=center>";
                        print "<table border=0>";
                          print "<tr>";
                            print "<td class={$cb1}>c</td>";
                            print "<td class={$cb2}>r</td>";
                            print "<td class={$cb3}>d</td>";
                            print "<td class={$cb4}>p</td>";
                            print "<td class={$cb5}>t</td>";
                          print "</tr>";
                        print "</table>";
                      print "</td>";
                    }
                  }else{ 
                    /// 127.0.0.x  Managerの処理 admintbのstandby=1,2 statisticsのsb
                    if($statArr[5]=='sb'){ /// or $standby=='1' or $standby=='2'){
                      $amsg='Standby ';
                      $agst='aprob';
                    }else if($statArr[5]=='ok'){ 
                      $amsg='No Problem ';
                      $agst='snorm';
                    }else if($statArr[5]=='ng'){
                      $popData="<font color=red>Problem</font>//この監視エージェン内に監視異常の監視対象ホストが存在する";
                      $popupRtnData=popupMsgSet($popData);
                      $amsg='<a class="white15" style="text-decoration:none;" href="" onclick="viewPopup(\''.$popupRtnData.'\'); return false;">Problem</a>';
                      $agst='scrit';
                    }
                    print "<td align=center><table><tr><td align=center class={$agst}>";
                    print "{$amsg}";
                    print "</td></tr></table></td>";
                  }
                }
              }else{
                $msg='mysql error or no data:'.$stat_sql;
                writelogd($pgm,$msg);
                $popData="<font color=sienna>{$hostname}ホストなし</font>//監視対象ホストが存在しない、ホストを新たに作成する必要がある";
                $popupRtnData=popupMsgSet($popData);
                $amsg='<a class="yellow15" style="text-decoration:none;" href="" onclick="viewPopup(\''.$popupRtnData.'\'); return false;">Not found</a>';
                $agst='swarn';
                print "<td class=snmp align=center><table><tr><td align=center class={$agst}>{$amsg}</td></tr></table></td>";
              }
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

