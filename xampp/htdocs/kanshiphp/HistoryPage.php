<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
///
function matching($data,$keystr){
  $rtncd=FALSE;
  $keyarray=explode(' ',$keystr);
  foreach ($keyarray as $keyone){
    if (strpos($data,$keyone)){
      $rtncd=TRUE;
      break;
    }
  }
  return $rtncd;
}

function myjoin($data){
  $string = "";
  foreach ($data as $item){
    if (is_null($item)){
      $string = $string . ',';
    }else{
      $string = $string . $item . ',';
    }
  }
  $okstr=rtrim($string,',');
  return $okstr;
}

$ttl1='<img src="header/php.jpg" width="30" height="30">';
$ttl2=' ▽　覚　え　書　き　管　理　▽   ';
$ttl=$ttl1 . $ttl2;
  /// charset=UTF-8は日本語に必要
print '<html><head>';
print '<link rel="stylesheet" href="kanshi1_py.css">';
print '<script language="JavaScript">';
print 'function check(){';
print '  if (document.rform.onbtn.value == "delete"){';
print '    if (window.confirm("削除してよろしいですか？")){';
print '      return true;';
print '    }else{';
print '      window.alert("キャンセルします");';
print '      return false;';
print '    }';
print '  }else{';
print '    return true;';
print '  }';
print '}';
print 'function set_val(){';
print '  document.rform.onbtn.value = "delete";';
print '}';
print '</script>';
print '</head><body>';
///
/// 画面表示処理-------------------
///
$pgm="HistoryPage.php";
$user="";
$brcode="";
$brmsg="";
$displaysw='';
$rows='';
if(!(isset($_GET['display']) || isset($_GET['select']) || isset($_GET['search']) || isset($_GET['param']))){
  paramGet($pgm);
}elseif(isset($_GET['display'])){
  $user=$_GET['user'];
  if (isset($_GET['all'])){
    $sql='select * from historylog order by type, logtime desc';
  }else{
    if (isset($_GET['plan'])){
      $displaysw='1';
    }elseif (isset($_GET['prog'])){
      $displaysw='2';
    }elseif (isset($_GET['ideal'])){
      $displaysw='7';
    }elseif (isset($_GET['tips'])){
      $displaysw='8';
    }elseif (isset($_GET['save'])){
      $displaysw='9';
    }
    $sql='select * from historylog where type="'.$displaysw.'" order by type, logtime desc';    
  }
  $rows=getdata($sql);

}elseif(isset($_GET['search']) && $_GET['keys'] != null){ 
  $user=$_GET['user'];
  $keystr=$_GET['keys'];
  $keystr=str_replace("　"," ",$keystr); ///全角スペースを半角に
  $keystr=preg_replace("/\s+/"," ",trim($keystr)); ///複数スペースを1つに
  $keystr=htmlspecialchars($keystr,ENT_QUOTES); ///html特殊文字を変更
  $keystr1="";
  $keystr2="";
  $ampsw=0;
  if (strpos($keystr,'&&')){  
    $keyarrays=explode(' && ',$keystr);
    $keystr1=$keyarrays[0];
    $keystr2=$keyarrays[1];
    $ampsw=1;  // and ari    
  }else{
    $ampsw=0;  // and nashij
  }

  $sql='select * from historylog';
  $data=getdata($sql);
  $rows=[];
  /// 履歴データ読み 
  foreach ($data as $rowsrec){    
    $sdata=explode(',',$rowsrec);
    if(strpos($sdata[2],$keystr) !== false){
      $rows[]=$rowsrec;
    }else{
      if(strpos($sdata[3],$keystr) !== false) {
        $rows[]=$rowsrec;
      }
    }
  }  
/// 履歴データ読み　終了

}else{
  paramSet();
///
  if ($brcode=='error' or $brcode=='notic' or $brcode=='alert'){
    print "<h4 class={$ercode}>{$brmsg}</h4><hr>";
  }
///
  $sql='select * from historylog order by type, logtime desc';
  $rows=getdata($sql);
}  
///
  print "<h2>{$ttl}</h2>";
  if (empty($rows)){
    print '<h4><font color=red>ログがありません</font></h4>';
  }else{
    print '<h3>最新順に表示、「変更」または「削除」するものを１つ選択して下さい<br>';
    print '各種タイプのログを選択表示できます<br>';
    print '</h3>';
    print '&nbsp;&nbsp;<form method="get" action="HistoryPage.php">';
    print '&nbsp;&nbsp;件名または内容を検索→';
    print '&nbsp;&nbsp;<input type="text" name="keys" value="" size="40">';
    print "<input type=hidden name=user value={$user}>";
    
    print '&nbsp;&nbsp;<input class="buttonyell" type="submit" name="search" value="検索開始"></form>';
    print '<hr><table class="nowrap">';
    print '<tr><th>選択</th><th >ログタイプ</th><th>日付:時刻</th><th>件名</th><th>内容</th></tr>';
    /// historylog
    ///[0]type [1]logtime [2]subject  [3]contents
    ///  
    $iro='';
    print '<form name="rform" method="get" action="viewhistorylog.php" onsubmit="return check()">';
    print '<input class=button type="submit" name="update" value="変更実行" >';
    print '&nbsp;&nbsp;<input class=buttondel type="submit" name="delete" value="削除実行" onClick="set_val()">';
    print '<br><br>';
    foreach ($rows as $rowsrec){
      $sdata=explode(',',$rowsrec,4);
      if ($sdata[0]=='1'){
        $logtype='計画中';
        $iro='cwhite';
      }elseif ($sdata[0]=='2'){
        $logtype='処理中';
        $iro='cyellow';
      }elseif ($sdata[0]=='7'){
        $logtype='参　考';
        $iro='corang';
      }elseif ($sdata[0]=='8'){
        $logtype='注　意';
        $iro='cred';
      }elseif ($sdata[0]=='9'){
        $logtype='履歴保存';
        $iro='cgray';
      }else{
        $logtype='未設定';
      }
      $logtime=$sdata[1];
      if (! is_null($sdata[2])){
        $subject=$sdata[2];
        $websubject=nl2br($subject);
      }else{
        $websubj='';
      }
      if (! is_null($sdata[3])){
        $contents=$sdata[3];
        $webcontents=nl2br($contents);
      }else{
        $webcontents='';
      }
      $strdata = myjoin($sdata); //## for post string to vieweventlog.py
      $triro="";
      print '<tr>';
      print "<td class=vatop><input type='radio' name='select' value={$strdata} ></td>";
      print "<td class={$iro}><span class=vatop >{$logtype}</span></td>";
      print "<td class=vatop>{$logtime}</td>";
      print "<td class=vatop>{$websubject}</td>";
      print "<td width='60'>{$webcontents}</td>";
      print '</tr>';
    } //end of for
    print '</table>';
    print '<br><input class=button type="submit" name="update" value="変更実行" >';
    print '&nbsp;&nbsp;<input class=buttondel type="submit" name="delete" value="削除実行" onClick="set_val()">';
    print '<input type="hidden" name="onbtn">';
    print '</form>';
  }

  print '<hr>';
  print '<a id="selectlog"/>';
  print '<form name="dform" method="get" action="HistoryPage.php">';
  print '<h3>ログタイプを選択表示出来ます</h3>';
  print '<input type="hidden" name="display" value="option" >';
  print "<input type=hidden name=user value={$user}>";
  print '<table border="1" class=dsptb><tr>';
  print '<td class=dsptd><input class=buttonwhite type="submit" name="plan" value="計画中表示" ></td>';
  print '<td class=dsptd><input class=buttonyell type="submit" name="prog" value="処理中表示" ></td>';
  print '<td class=dsptd><input class=buttonorang type="submit" name="idea" value="参考表示" ></td>';
  print '<td class=dsptd><input class=buttondel type="submit" name="tips" value="注意表示" ></td>';
  print '<td class=dsptd><input class=buttongray type="submit" name="save" value="履歴保存表示" ></td>';
  print '<td class=dsptd><input class=button type="submit" name="all" value="全て表示" ></td>'; 
  print '</tr></table>';
  print '</form>';
  
  print '<hr>';
  $dte=date('ymdHis');
  print '<table>';
  print '<form name="iform" method="get" action="viewhistorylog.php">';
  print '<h3>追加データを入力して「作成実行」をクリック</h3>';
  print '<h4><font color=red>’（&#039;）、”（&quot;）、〈（&lt;）、〉（&gt;）はDBレコード上()内文字にて書かれます<br>';
  print '「\\」は「\\\\」、「\\\\」は「\\\\\\\\」として入力して下さい</font></h4>';
  print '<table class="nowrap">';
  print '<tr><th >ログタイプ</th><th>日付:時刻</th><th>件名</th><th>内容</th></tr>';
  print '<tr>';
  print '<td><select name="logtype">';
  print '<option value="1">計画中</option>';
  print '<option value="2">処理中</option>';
  print '<option value="7">参考</option>';
  print '<option value="8">注意</option>';
  print '<option value="9">履歴保存</option>';
  print '</select></td>';
  print "<td> <input type='text' name='date' size=9 value={$dte} readonly></td>";
  print '<td> <input type="text" name="subj" value="" size=25></td>';
  print '<td><textarea name="mesg" cols="70">ここに詳細を書く、改行で複数行可能</textarea></td>';
  print '</tr>';
  print '</table>';
  print '<br><input class=button type="submit" name="add" value="作成実行" >';
  print '</form>';
  print '<br><br>';
  print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>"; 
  print '</body></html>';
?>

