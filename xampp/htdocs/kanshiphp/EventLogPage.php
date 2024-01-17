<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
/// 配列データをストリングデータへ変える
function myjoin($data){
  $string = "";
  $i=0;
  foreach ($data as $rec){
    if (is_null($rec)){
      $string = $string . ' ,';
    }else{
      $string = $string . $data[$i] . ',';
    }
    $i++;
  }
  $okstr=rtrim($string.',');
  return $okstr;
}
$pgm="EventLogPage.php";
$user="";
$brcode="";
$brmsg="";
$auth="";
/// セッション情報のユーザーを取得
if(!isset($_GET['param'])){  
  paramGet($pgm);
}else{                       
/// ユーザ取得後処理
  paramSet(); 
  ///
  $selsql="select userid,authority from user where userid='".$user."'";
  $udata=getdata($selsql);
  if(empty($udata)){
    $msg="#error#admin#ユーザが存在しません、再ログインして下さい";
    branch($pgm,$msg);
  }
  $sdata=$udata[0];
  $tdata=explode(',',$sdata);
  $auth=$tdata[1];
  $sql="select * from admintb";
  $rows=getdata($sql);
  $adata=explode(',',$rows[0]);
  $interval=strval(intval(intval($adata[7])/2));
  $ttl1='<img src="header/php.jpg" width="30" height="30">';
  $ttl2='　▽　イベントログ　▽　';
  $ttl3=$interval . '　秒間隔更新';
  $ttl=$ttl1 . $ttl2 . $ttl3;
  print '<html><head>';
  print "<meta http-equiv='Refresh'  content={$interval}>";
  print '<link rel="stylesheet" href="kanshi1_py.css">';
  print '</head><body>';
  ///
  if ($brcode=="alert" || $brcode=="error" || $brcode=="notic"){
    print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }
  ///
  print "<h2>{$ttl}</h2>";
  ///
  /// 画面表示処理-
  ///
  print '<h3>ログは、最新のものから出力されます</h3>';
  print '<h3>☆障害確認する場合は、<span class=trred>赤色背景行「監視異常」</span>又は<span class=trpnk>レンガ色背景行「監視注意」</span>を選択し、<span class=trblk>「選択実行」</span>をクリックします<br>';
  print '☆障害クローズをする場合は、<span class=trylw>黄色背景行確認欄「障害確認済」</span>を選択し、<span class=trblk>「選択実行」</span>をクリックします<br>';
  print '☆単一ログを削除する場合は、〇を選択し<span class=trred>「選択削除実行」</span>をクリックします<br>';
  print '☆ログの範囲を削除する場合は、範囲yy-mm-ddを入力して<span class=trred>「範囲削除実行」</span>をクリックします</h4>'; 
  print '<table class="nowrap">';
  print '<tr><th >日付:時刻</th><th>ホスト</th><th>イベント種類</th><th>snmp監視</th><th>snmp状態</th><th>管理者</th><th>障害管理番号</th><th>確認</th><th>処置メール</th></tr>';

  $sql='select * from eventlog order by eventtime desc, host';
  $rows=getdata($sql);
  ///#[0]host [1]time [2]etype      [3]stype [4]svalue [5]管理者 [6]管理# [7]確認終了 [8]メール [9]MSG
  ///#                 1,2,4,5,6,7  2,3,4,5,6 
  print '<form name="rform" method="get" action="vieweventlog.php">';
  /// イベントログを１件づつ表示させる
  foreach ($rows as $rowsrec){      // 
    $sdata=explode(',',$rowsrec);
    $evlhost=$sdata[0];
    $evleventtyp=$sdata[2];
    $evlsnmptyp=$sdata[3];
    $evlsnmpval=$sdata[4];
    $evlcnfcls=$sdata[7];  ///
    $evlmailopt=$sdata[8]; ///   
    //$strdata = myjoin($sdata); ///## vieweventlog.phpへ渡すデータ
    $edt=$sdata[1];
    $dte = substr($edt,0,2)."-".substr($edt,2,2)."-".substr($edt,4,2)." ".substr($edt,6,2).":".substr($edt,8,2).":".substr($edt,10,2);
    $styp="";
    $ctyp="";
    $mtyp="";
    $snmpval="";
    $kanrisha="";
    $kanrino="";
    $triro="";
    //if ($evlsnmptyp=='7'){
    //  var_dump($strdata);
    //}
    if (! is_null($evlsnmpval)){ //##snmpvale
      $snmpval=$evlsnmpval;
      if ($snmpval=='allok'){
        $snmpval='なし';
      }
    } // endif
    if (! is_null($sdata[5])){ //##管理者
      $kanrisha=$sdata[5];
    } // endiff
    if (! is_null($sdata[6])){ //##管理#
      $kanrino=$sdata[6];
    } 
    if ($evleventtyp=='1'){ //##eventtype
      $etyp='監視正常';
      $triro = "trblk";
    }elseif ($evleventtyp=='2'){ // ##
      $etyp='監視異常';
      $triro = "trred";
    }elseif ($evleventtyp=='3'){ // ##
      $etyp='監視管理';
      $triro = "trylw";
    }elseif ($evleventtyp=='4'){
      $etyp='対象削除';
      $triro = "trylw";
    }elseif ($evleventtyp=='5'){
      $etyp='新規作成';
      $triro = "trylw";
    }elseif ($evleventtyp=='6'){
      $etyp='内容修正';
      $triro = "trylw";
    }elseif ($evleventtyp=='7'){
      $etyp='監視開始';
      $triro = "trylw";
    }elseif ($evleventtyp=='0'){
      $etyp='ログイン';
      $triro = "trylw";
    }elseif ($evleventtyp=='9'){
      $etyp='ログアウト';
      $triro = "trylw";
    }elseif ($evleventtyp=='a'){
      $etyp='DBアクセス';
      $triro = "trred";
    }else{
      $etyp='不明';
      $triro = "trred";
    } // endif
    /// event type 削除　新規　修正
    if ($evleventtyp=="4" or $evleventtyp=="5" or $evleventtyp=="6"){ // event type=削除　新規　修正
      $styp="";
      $ctyp="";
      $mtyp="";
    }else{ /// eventtype 0 1 2 3 7 8 9
      if (! is_null($snmpval)){
        $nwc=explode(':',$snmpval);
        if ($evlsnmptyp=='0'){
          $styp='';
        }elseif ($evlsnmptyp=='1'){
          //$styp='無応答';
          $styp="";
        }elseif ($evlsnmptyp=='2'){ /// cpu
          if ($evleventtyp=='7' and $nwc[0]=='n'){ ///監視開始+n
            $styp='CPU負荷%';
          }elseif($evleventtyp=='1' and $nwc[0]=='n'){ ///監視正常+n
            $styp='CPU負荷%';
            $etyp='監視正常';
          }else{
            $styp='CPU負荷%';
            $etyp='監視注意';
          }  
        }elseif ($evlsnmptyp=='3'){ /// ram
          if ($evleventtyp=='7' and $nwc[0]=='n'){ ///監視開始+n
            $styp='メモリ負荷%';
          }elseif($evleventtyp=='1' and $nwc[0]=='n'){ ///監視正常+n
            $styp='メモリ負荷%';
            $etyp='監視正常';            
          }else{
            $styp='メモリ負荷%';
            $etyp='監視注意';            
          }
        }elseif ($evlsnmptyp=='4'){ /// disk
          if ($evleventtyp=='7' and $nwc[0]=='n'){ ///監視開始+n
            $styp='ディスク負荷%';
          }elseif($evleventtyp=='1' and $nwc[0]=='n'){ ///監視正常+n
            $styp='ディスク負荷%';
            $etyp='監視正常';            
          }else{
            $styp='ディスク負荷%';
            $etyp='監視注意';            
          }
        }elseif ($evlsnmptyp=='5'){ /// process
          $styp='プロセス未稼働';
          //$etyp='監視注意';           
        }elseif ($evlsnmptyp=='6'){ //##tcpport
          $styp='ポート閉鎖';
          //$etyp='監視注意';           
        }elseif ($evlsnmptyp=='7'){ 
          $styp='クローズ待ち';
        }else{
          $styp='';
        }
      } // endif A
      if ($snmpval=='' or !isset($snmpval)){
        if ($evlsnmptyp=='2' or $evlsnmptyp=='3' or $evlsnmptyp=='4' or $evlsnmptyp=='5' or $evlsnmptyp=='6'){
          $snmpval='データロスト';
        }
      }
      if($snmpval=='7'){
        $snmpval='クローズ待ち';
      }
      if($evlsnmptyp=='5' and $snmpval=='empty'){
        $snmpval='指定なし';
        $etyp='監視正常';
        $triro = "trblk";
      } 
      if ($evlcnfcls != '0'){ /// イベントログの確認項目が「確認」
        $etype='監視管理';
        if ($evlcnfcls=='1'){
          $ctyp='障害確認';
          $styp="";
          $snmpval="";
          $triro = "trblk";
          //var_dump($sdata);
        }elseif ($evlcnfcls=='2'){
          $ctyp='障害確認済';
          $triro="trylw";
        }elseif ($evlcnfcls=='3'){
          $ctyp='クローズ';           // イベントログの確認項目が「クローズ」
          $triro = "trblk";
        }else{
          $ctyp='未確認';
        }
      }
      if ($evlmailopt!='1'){
        $mtyp='';
      }else{
        $mtyp='送信済';
        $etype='監視管理';
        $triro = "trblk";
      }
      
    }
    /// 
    if ($etyp=='監視注意' and $evlcnfcls!='1'){
      $triro= 'trpnk';
    }
 // endif 
    if($evlcnfcls=='1'){
      var_dump($triro);
    }
    print "<tr class={$triro}><td class={$triro}><input type='radio' name='evdata' value={$rowsrec} >{$dte}</td>";
    print "<td class={$triro} width=200> &nbsp;{$sdata[0]}</td>";
    print "<td class={$triro}> &nbsp;{$etyp}</td>";
    print "<td class={$triro}> &nbsp;{$styp}</td>";
    print "<td class={$triro}> &nbsp;{$snmpval}</td>";
    print "<td class={$triro}> &nbsp;{$kanrisha}</td>";
    print "<td class={$triro}> &nbsp;{$kanrino}</td>";
    
    if ($evlcnfcls=='1') {
      print "<td class=trblk> &nbsp;{$ctyp}</td>";
    } elseif ($evlcnfcls=='2') {
      print "<td class=trylw> &nbsp;{$ctyp}</td>";
    } else {
      print "<td class={$triro}> &nbsp;{$ctyp}</td>";
    }    
    print "<td class={$triro}> &nbsp;{$mtyp}</td>";
    print '</tr>';

  } //end of foreach

  print '</table>';
  print "<input type='hidden' name='user' value={$user}>";
  print "<input type='hidden' name='authcd' value={$auth}>";
  print '<br><input class=button type="submit" name="select" value="選択実行" >';
  if ($auth=='1'){
    print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class=buttondel type="submit" name="delete" value="選択削除実行" >';
  }
  print '<br><hr>';
  if ($auth=='1'){
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
