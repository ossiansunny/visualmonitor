<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
/// 配列データをストリングデータへ変える
function myjoin($_data){
  $string = "";
  $index=0;
  foreach ($_data as $rec){
    if (is_null($rec) or $rec==''){
      $string = $string . ' ,';
    }else{
      $string = $string . $data[$index] . ',';
    }
    $index++;
  }
  $okStr=rtrim($string.',');
  return $okStr;
}
///
$pgm="EventLogPage.php";
$user="";
$brcode="";
$brmsg="";
$authority="";
/// セッション情報のユーザーを取得
if(!isset($_GET['param'])){  
  paramGet($pgm);
}else{
/// ユーザ取得後処理
  paramSet(); 
  ///
  /// read user table
  ///
  $user_sql="select authority,bgcolor from user where userid='".$user."'";
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    branch('logout.php',$msg);
  }
  $userArr=explode(',',$userRows[0]);
  $authority=$userArr[0];
  $bgColor=$userArr[1];
  ///
  /// read admintb table
  /// 
  $admin_sql="select monintval,logout from admintb";
  $adminRows=getdata($admin_sql);
  $adminArr=explode(',',$adminRows[0]);
  $adminMonintval=$adminArr[0];
  $adminLogout=$adminArr[1];
  $interval=$adminMonintval;
  $title1='<img src="header/php.jpg" width="30" height="30">';
  $title2='　▽　イベントログ　▽　';
  $title3=$interval . '　秒間隔更新';
  $title='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$title1 . '&nbsp;&nbsp;'. $title2 . $title3;
  print '<html><head>';
  if($adminLogout=='0'){
    print "<meta http-equiv='Refresh'  content={$interval}>";
  }
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';
  print "</head><body class={$bgColor}>";
  ///
  if ($brcode=="alert" || $brcode=="error" || $brcode=="notic"){
    print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }
  ///
  print "<h2>{$title}<h2>";
  ///
  /// 画面表示処理-
  ///
  print '<h3>ログは、最新のものから出力</h3>';
  print '<h3>☆障害確認する場合は、<span class=trred>赤色背景行「監視異常」</span>又は<span class=trpnk>レンガ色背景行「監視注意」</span>を選択し、<span class=trblk>「選択実行」</span>をクリック<br>';
  print '☆障害クローズをする場合は、<span class=trylw>黄色背景行確認欄「障害確認済」</span>を選択し、<span class=trblk>「選択実行」</span>をクリック<br>';
  print '☆ログの範囲を削除する場合は、範囲yy-mm-ddを入力して<span class=trred>「範囲削除実行」</span>をクリック<br>'; 
  print '☆前日までのログを削除する場合は、サポートメニュー「ログ削除」を使用</h3>';
  print '<table class="nowrap">';
  print '<tr><th >日付:時刻</th><th>ホスト</th><th>イベント種類</th><th>監視種類</th><th>snmp状態</th><th>管理者</th><th>障害種類</th><th>障害管理番号</th><th>確認・クローズ</th><th>処置メール</th></tr>';
  ///
  /// read statistics table
  ///
  $stat_sql="select host,status from statistics where status='1' or status='2'"; 
  $statRows=getdata($stat_sql);
  ///
  /// read eventlog
  ///
  $event_sql='select * from eventlog order by eventtime desc, host';
  $eventRows=getdata($event_sql);
  ///#[0]host [1]time [2]etype      [3]stype [4]svalue [5]管理者 [6]管理# [7]確認終了 [8]メール [9]MSG
  ///#                 1,2,4,5,6,7  2,3,4,5,6 
  print '<form name="rform" method="get" action="vieweventlog.php">';
  /// イベントログを１件づつ表示させる
  foreach ($eventRows as $eventRowsRec){       
    $eventArr=explode(',',$eventRowsRec);
    $event_host=$eventArr[0];
    $event_type=$eventArr[2];
    $event_snmpTyp=$eventArr[3];
    $event_snmpVal=$eventArr[4];
    $event_kanrisha=$eventArr[5];
    $event_kanrimei=$eventArr[6];
    $event_kanrino=$eventArr[7];
    $event_cnfCls=$eventArr[8];  ///
    $event_mailOpt=$eventArr[9]; ///   
    $edt=$eventArr[1];
    $dte = substr($edt,0,2)."-".substr($edt,2,2)."-".substr($edt,4,2)." ".substr($edt,6,2).":".substr($edt,8,2).":".substr($edt,10,2);
    $snmpTypView="";
    $cnfClsView="";
    $mailTypView="";
    $snmpValView="";
    $bgColor="";

    if ($event_type=='1' and $event_snmpVal=='') { 
      /// 監視正常は対象外 
      continue;
    } else {
      if (!(empty($event_snmpVal))){ /// snmpValが null,'',0以外
        $snmpValView=$event_snmpVal;
        if ($snmpValView=='allok'){
          $snmpValView='なし';
        }
      } 
       
      switch($event_type){
        case '1':
          $eventTypView='監視正常';
          $bgColor = "trblk";
          break;
        case '2':
          $eventTypView='監視異常';
          $bgColor = "trred";
          break;
        case '3':
          $eventTypView='監視管理';
          $bgColor = "trylw";
          break;
        case '4':
          $eventTypView='対象削除';
          $bgColor = "trylw";
          break;
        case '5':
          $eventTypView='新規作成';
          $bgColor = "trylw";
          break;
        case '6':
          $eventTypView='内容修正';
          $bgColor = "trylw";
          break;
        case '7':
          $eventTypView='監視開始';
          $bgColor = "trylw";
          break;
        case '0':
          $eventTypView='ログイン';
          $bgColor = "trylw";
          break;
        case '9':
          $eventTypView='ログアウト';
          $bgColor = "trylw";
          break;
        case 'a':
          $eventTypView='DBアクセス';
          $bgColor = "trred";
          break;
        default:
          $eventTypView='不明';
          $bgColor = "trred";
      }    
      
      /// event type 削除　新規　修正
      if ($event_type=="4" or $event_type=="5" or $event_type=="6"){ 
        /// event type=4(削除)　5(新規)　6(修正)
        $snmpTypView="";
        $cnfClsView="";
        $mailTypView="";
      }else{ 
        /// event type=0(ログイン) 1(監視正常) 2(監視異常) 3(監視管理) 7(監視開始) 9(ログアウト) a(DB異常)
        if (!(empty($snmpValView))){
          $nwc=explode(':',$snmpValView);
          if ($event_snmpTyp=='0'){
            $snmpTypView='';
          }elseif ($event_snmpTyp=='2'){ /// snmp cpu監視
            if ($event_type=='7' and $nwc[0]=='n'){ ///監視開始+n
              $snmpTypView='CPU負荷%';
            }elseif($event_type=='1' and $nwc[0]=='n'){ ///監視正常+n
              $snmpTypView='CPU負荷%';
              $eventTypView='監視正常';
            }else{
              $snmpTypView='CPU負荷%';
              $eventTypView='監視注意';
            }  
          }elseif ($event_snmpTyp=='3'){ /// snmp ram監視
            if ($event_type=='7' and $nwc[0]=='n'){ ///監視開始+n
              $snmpTypView='メモリ負荷%';
            }elseif($event_type=='1' and $nwc[0]=='n'){ ///監視正常+n
              $snmpTypView='メモリ負荷%';
              $eventTypView='監視正常';            
            }else{
              $snmpTypView='メモリ負荷%';
              $eventTypView='監視注意';            
            }
          }elseif ($event_snmpTyp=='4'){ /// snmp disk監視
            if ($event_type=='7' and $nwc[0]=='n'){ ///監視開始+n
              $snmpTypView='ディスク負荷%';
            }elseif($event_type=='1' and $nwc[0]=='n'){ ///監視正常+n
              $snmpTypView='ディスク負荷%';
              $eventTypView='監視正常';            
            }else{
              $snmpTypView='ディスク負荷%';
              $eventTypView='監視注意';            
            }
          }elseif ($event_snmpTyp=='5'){ /// process
            $snmpTypView='プロセス未稼働';
          
          }elseif ($event_snmpTyp=='6'){ ///tcpport
            $snmpTypView='ポート閉鎖';
          }
        } 
        if ($event_snmpTyp=='1'){
          $snmpTypView='snmp監視';
          $snmpValView='応答なし';     
        }elseif ($event_snmpTyp=='7'){ 
          $snmpTypView='クローズ待ち';
        }elseif($event_snmpTyp=='P'){
          $snmpTypView='Ping';
        }elseif($event_snmpTyp=='N'){
          $snmpTypView='Ncat';
          $snmpValView='';
        }
        if($event_snmpTyp=='5' and $snmpValView=='empty'){
          $snmpValView='指定なし';
          $eventTypView='監視正常';
          $bgColor = "trblk";
        } 
        if($event_snmpTyp=='6' and $snmpValView=='empty'){
          $snmpValView='指定なし';
          $eventTypView='監視正常';
          $bgColor = "trblk";
        }
        /// 
        if ($event_cnfCls != '0'){ /// イベントログの確認項目が「確認」
          $eventTypViewe='監視管理';
          if ($event_cnfCls=='1'){
            $cnfClsView='障害確認';
            $bgColor = "trblk";
          
          }elseif ($event_cnfCls=='2'){
            $cnfClsView='障害確認済';
            $bgColor="trylw";
          }elseif ($event_cnfCls=='3'){
            $cnfClsView='クローズ'; /// イベントログの確認項目が「クローズ」
            $bgColor = "trblk";
          
          }
        }
        if ($event_mailOpt!='1'){
          $mailTypView='';
        }else{
          $mailTypView='送信済';
          $eventTypViewe='監視管理';
          $bgColor = "trblk";
        }      
      }
      /// 
      if ($eventTypView=='監視注意' and $event_cnfCls!='1'){
        $bgColor= 'trpnk';
      }
      ///
      /// 監視異常に確認、確認済　表示
      ///
      //$cnfClsView='';
      if(!empty($statRows)){
        for($i=0;$i<count($statRows);$i++){
          $statArr=explode(',',$statRows[$i]);
          if($event_host==$statArr[0] and $cnfClsView=='' and $event_type=='2'){
            if($statArr[1]=='1'){
              $cnfClsView='障害確認';
            }elseif($statArr[1]=='2'){
              $cnfClsView='障害確認済';
            }    
          }
        }
      }   
      print "<tr class={$bgColor}><td class={$bgColor}><input type='radio' name='evdata' value={$eventRowsRec} >{$dte}</td>";
      print "<td class={$bgColor} width=200> &nbsp;{$event_host}</td>";
      print "<td class={$bgColor}> &nbsp;{$eventTypView}</td>";
      print "<td class={$bgColor}> &nbsp;{$snmpTypView}</td>";
      print "<td class={$bgColor}> &nbsp;{$snmpValView}</td>";
      print "<td class={$bgColor}> &nbsp;{$event_kanrisha}</td>";
      print "<td class={$bgColor}> &nbsp;{$event_kanrimei}</td>";
      print "<td class={$bgColor}> &nbsp;{$event_kanrino}</td>";
    
      if ($event_cnfCls=='1') {
        print "<td class=trblk> &nbsp;{$cnfClsView}</td>";
      } elseif ($event_cnfCls=='2') {
        print "<td class=trylw> &nbsp;{$cnfClsView}</td>";
      } else {
        print "<td class={$bgColor}> &nbsp;{$cnfClsView}</td>";
      }    
      print "<td class={$bgColor}> &nbsp;{$mailTypView}</td>";
      print '</tr>';
    }
  } ///end of foreach

  print '</table>';
  print "<input type='hidden' name='user' value={$user}>";
  print "<input type='hidden' name='authcd' value={$authority}>";
  print '<br><input class=button type="submit" name="select" value="選択実行" >';
  if ($auth=='1'){
    print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class=buttondel type="submit" name="delete" value="選択削除実行" >';
  }
  print '<br><hr>';
  if ($authority=='1'){
    print '<h3>☆日付を yy-mm-dd としてFrom Toへ入力、「範囲削除実行」をクリック</h3>';
    print '<table><tr>';
    print '<td>範囲入力：</td><td><input type="text" name="fromtime" value="" placeholder="From"></td>';
    print '<td><input type="text" name="totime" value="" placeholder="To"></td>';
    print '</tr></table>';
    print '<br><input class=buttondel type="submit" name="rangedel" value="範囲削除実行" >';
  }
  print '</form>';

  print '<br><br>';
  print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>"; 
  print '</body></html>';
}
?>
