<?php
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

$value="";
$auth="";
/// セッション情報のユーザーを取得
if(!isset($_GET['param'])){  
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="EventLogPage.php" method="get">';
  echo '<input type="hidden" name="param" value="">';
  echo '<input type="submit" name="next" style="display:none;" />';
  echo '</form></body></html>';
  echo '<script type="text/javascript">';
  echo 'var keyvalue = sessionStorage.getItem("user");';
  echo 'if (!keyvalue) {';
  echo '  keyvalue = "unknown";';
  echo '}';  
  echo 'document.forms["F"].elements["param"].value = keyvalue;';
  echo '</script>';
}else{                       
/// ユーザ取得後処理
  $value=$_GET['param'];
  $selsql="select userid,authority from user where userid='".$value."'";
  $udata=getdata($selsql);
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
  echo '<html><head>';
  echo "<meta http-equiv='Refresh'  content={$interval}>";
  echo '<link rel="stylesheet" href="kanshi1_py.css">';
  echo '</head><body>';
  echo "<h2>{$ttl}</h2>";
  ///
  /// 画面表示処理-
  ///
  echo '<h3>☆最新順に出力、データを選択して「選択実行」をクリック</h3>';
  echo '<h3>☆障害確認する場合は、背景赤色の「監視異常」を選択して下さい<br>';
  echo '☆クローズをする場合は、背景黄色の「障害確認済」を選択して下さい</h3>';
  echo '<table class="nowrap">';
  echo '<tr><th >日付:時刻</th><th>ホスト</th><th>イベント種類</th><th>snmp監視</th><th>snmp状態</th><th>管理者</th><th>障害管理番号</th><th>確認</th><th>メール送信</th></tr>';

  $sql='select * from eventlog order by eventtime desc, host';
  $rows=getdata($sql);
  ///#[0]host [1]time [2]etype      [3]stype [4]svalue [5]管理者 [6]管理# [7]確認終了 [8]メール [9]MSG
  ///#                 1,2,4,5,6,7  2,3,4,5,6 
  echo '<form name="rform" method="get" action="vieweventlog.php">';
  /// イベントログを１件づつ表示させる
  foreach ($rows as $rowsrec){      // 
    $sdata=explode(',',$rowsrec);
    $evlhost=$sdata[0];
    $evleventtyp=$sdata[2];
    $evlsnmptyp=$sdata[3];
    $evlsnmpval=$sdata[4];
    $evlcnfcls=$sdata[7];
    $evlmailopt=$sdata[8];    
    $strdata = myjoin($sdata); ///## vieweventlog.phpへ渡すデータ
    $edt=$sdata[1];
    $dte = substr($edt,0,2)."-".substr($edt,2,2)."-".substr($edt,4,2)." ".substr($edt,6,2).":".substr($edt,8,2).":".substr($edt,10,2);
    $styp="";
    $ctyp="";
    $mtyp="";
    $snmpval="";
    $kanrisha="";
    $kanrino="";
    $triro="";

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
      $triro = "trblk";
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
      $etyp='Login';
      $triro = "trblk";
    }elseif ($evleventtyp=='9'){
      $etyp='Logout';
      $triro = "trblk";
    }elseif ($evleventtyp=='a'){
      $etyp='DBアクセス';
      $triro = "trred";
    }else{
      $etyp='不明';
      $triro = "trred";
    } // endif

    if ($sdata[2]=="4" || $sdata[2]=="5" || $sdata[2]=="6"){ // event type=削除　新規　修正
      $styp="";
      $ctyp="";
      $mtyp="";
    }else{
      if (! is_null($snmpval)){
        $nwc=explode(':',$snmpval);
        if ($evlsnmptyp=='0'){
          $styp='';
        }elseif ($evlsnmptyp=='1'){
          //$styp='無応答';
          $styp="";
        }elseif ($evlsnmptyp=='2'){ //##cpu
          if ($evleventtyp=='7' && $nwc[0]=='n'){ // 監視開始&n
            $styp='CPU負荷%';
          }else{
            $styp='CPU負荷%';
          }
        }elseif ($evlsnmptyp=='3'){
          if ($evleventtyp=='7' && $nwc[0]=='n'){
            $styp='メモリ負荷%';
          }else{
            $styp='メモリ負荷%';
          }
        }elseif ($evlsnmptyp=='4'){
          if ($evleventtyp=='7' && $nwc[0]=='n'){
            $styp='ディスク負荷%';
          }else{
            $styp='ディスク負荷%';
          }
        }elseif ($evlsnmptyp=='5'){
          $styp='プロセス未稼働'; 
        }elseif ($evlsnmptyp=='6'){
          $styp='ポート閉鎖'; 
        }else{
          $styp='-';
        }
      } // endif A
      if ($snmpval==''){
        if ($evlsnmptyp=='2' || $evlsnmptyp=='3' || $evlsnmptyp=='4' || $evlsnmptyp=='5' || $evlsnmptyp=='6'){
          $snmpval='データロスト';          
        }
      }
      if ($evlcnfcls=='1'){         // イベントログの確認項目が「確認」
        $ctyp='障害確認';
      }elseif ($evlcnfcls=='2'){
        $ctyp='障害確認済';
        $triro="trylw";
      }elseif ($evlcnfcls=='3'){
        $ctyp='クローズ';           // イベントログの確認項目が「クローズ」
        $triro = "trblk";
      }else{
        $ctyp='未確認';
      }
      if ($evlmailopt=='0'){
        $mtyp='未送信';
      }else{
        $mtyp='送信済';
      }
   
    } // endif 
    echo "<tr class={$triro}><td class={$triro}><input type='checkbox' name='ckdata[]' value={$strdata} >{$dte}</td>";
    echo "<td class={$triro} width=200> &nbsp;{$sdata[0]}</td>";
    echo "<td class={$triro}> &nbsp;{$etyp}</td>";
    echo "<td class={$triro}> &nbsp;{$styp}</td>";
    echo "<td class={$triro}> &nbsp;{$snmpval}</td>";
    echo "<td class={$triro}> &nbsp;{$kanrisha}</td>";
    echo "<td class={$triro}> &nbsp;{$kanrino}</td>";
    
    if ($evlcnfcls=='1') {
      echo "<td class=trblk> &nbsp;{$ctyp}</td>";
    } elseif ($evlcnfcls=='2') {
      echo "<td class=trylw> &nbsp;{$ctyp}</td>";
    } else {
      echo "<td class={$triro}> &nbsp;{$ctyp}</td>";
    }    
    echo "<td class={$triro}> &nbsp;{$mtyp}</td>";
    echo '</tr>';

  } //end of foreach

  echo '</table>';
  echo "<input type='hidden' name='userid' value={$value}>";
  echo "<input type='hidden' name='authcd' value={$auth}>";
  echo '<br><input class=button type="submit" name="select" value="選択実行" >';
  echo '<br>';
  if ($auth=='1'){
    echo '<h3>☆日付を yy-mm-dd としてFrom Toへ入力、「削除実行」をクリック</h3>';
    echo '<table><tr>';
    echo '<td>範囲入力：</td><td><input type="text" name="fromtime" value="" placeholder="From"></td>';
    echo '<td><input type="text" name="totime" value="" placeholder="To"></td>';
    echo '</tr></table>';
    echo '<br><input class=buttondel type="submit" name="delete" value="削除実行" >';
  }
  echo '</form>';

  echo '<br><br>';
  echo "<a href='MonitorManager.php?param={$value}'><span class=buttonyell>監視モニターへ戻る</span></a>"; 
  echo '</body></html>';
}
?>
