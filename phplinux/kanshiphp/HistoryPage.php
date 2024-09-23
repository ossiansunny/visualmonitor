<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
///
function matching($_data,$_keystr){
  $rtnCde=FALSE;
  $keyarray=explode(' ',$_keystr);
  foreach ($keyarray as $keyone){
    if (strpos($_data,$keyone)){
      $rtnCde=TRUE;
      break;
    }
  }
  return $rtnCde;
}

function myjoin($_data){
  $string = "";
  foreach ($_data as $item){
    if (is_null($item)){
      $string = $string . ',';
    }else{
      $string = $string . $item . ',';
    }
  }
  $okStr=rtrim($string,',');
  return $okStr;
}

$ttl1='<img src="header/php.jpg" width="30" height="30">';
$ttl2=' ▽　覚　え　書　き　管　理　▽   ';
$ttl=$ttl1 . $ttl2;
  /// charset=UTF-8は日本語に必要
print '<html><head>';
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
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
$displayType='';
$rows='';
if(!(isset($_GET['display']) || isset($_GET['select']) || isset($_GET['search']) || isset($_GET['param']))){
  paramGet($pgm);
}elseif(isset($_GET['display'])){
  $user=$_GET['user'];
  if (isset($_GET['all'])){
    $hist_sql='select * from historylog order by type, logtime desc';
  }else{
    if (isset($_GET['plan'])){
      $displayType='1';
    }elseif (isset($_GET['prog'])){
      $displayType='2';
    }elseif (isset($_GET['ideal'])){
      $displayType='7';
    }elseif (isset($_GET['tips'])){
      $displayType='8';
    }elseif (isset($_GET['save'])){
      $displayType='9';
    }
    $hist_sql='select * from historylog where type="'.$displayType.'" order by type, logtime desc';    
  }
  $histRows=getdata($hist_sql);

}elseif(isset($_GET['search']) && $_GET['keys'] != null){ 
  $user=$_GET['user'];
  $keyStr=$_GET['keys'];
  $keyStr=str_replace("　"," ",$keyStr); ///全角スペースを半角に
  $keyStr=preg_replace("/\s+/"," ",trim($keyStr)); ///複数スペースを1つに
  $keyStr=htmlspecialchars($keyStr,ENT_QUOTES); ///html特殊文字を変更
  $keyStr1="";
  $keyStr2="";
  if (strpos($keyStr,'&&')){  
    $keyArr=explode(' && ',$keyStr);
    $keyStr1=$keyArr[0];
    $keyStr2=$keyArr[1];
  }

  $hist_sql='select * from historylog';
  $histRows=getdata($hist_sql);
  $rows=[];
  /// 履歴データ読み 
  foreach ($histRows as $histRowsRec){    
    $histArr=explode(',',$histRowsRec);
    if(strpos($histArr[2],$keyStr) !== false){
      $rows[]=$histRowsRec;
    }else{
      if(strpos($histArr[3],$keyStr) !== false) {
        $rows[]=$histRowsRec;
      }
    }
  }
  $histRows=$rows;  
/// 履歴データ読み　終了

}else{
  paramSet();
///
  if ($brcode=='error' or $brcode=='notic' or $brcode=='alert'){
    print '<h4 class="'.$brcode.'">"'.$brmsg.'"</h4><hr>';
    //print "<h4 class={$ercode}>{$brmsg}</h4><hr>";
  }
///
  $hist_sql='select * from historylog order by type, logtime desc';
  $histRows=getdata($hist_sql);
}  
///
print '<h2>'.$ttl.'</h2>';
if (empty($histRows)){
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
  $bgColor='';
  print '<form name="rform" method="get" action="viewhistorylog.php" onsubmit="return check()">';
  print '<input class=button type="submit" name="update" value="変更実行" >';
  print '&nbsp;&nbsp;<input class=buttondel type="submit" name="delete" value="削除実行" onClick="set_val()">';
  print '<br><br>';
  foreach ($histRows as $histRowsRec){
    $histArr=explode(',',$histRowsRec,4);
    if ($histArr[0]=='1'){
      $logType='計画中';
      $bgColor='cwhite';
    }elseif ($histArr[0]=='2'){
      $logType='処理中';
      $bgColor='cyellow';
    }elseif ($histArr[0]=='7'){
      $logType='参　考';
      $bgColor='corang';
    }elseif ($histArr[0]=='8'){
      $logType='注　意';
      $bgColor='cred';
    }elseif ($histArr[0]=='9'){
      $logType='履歴保存';
      $bgColor='cgray';
    }else{
      $logType='未設定';
    }
    $logtime=$histArr[1];
    if (! is_null($histArr[2])){
      $subject=$histArr[2];
      $webSubject=nl2br($subject);
    }else{
      $webSubject='';
    }
    if (! is_null($histArr[3])){
      $contents=$histArr[3];
      $webContents=nl2br($contents);
    }else{
      $webContents='';
    }
    $histStr = myjoin($histArr); //## for post string to vieweventlog.py
    print '<tr>';
    print "<td class=vatop><input type='radio' name='select' value={$histStr} ></td>";
    print "<td class={$bgColor}><span class=vatop >{$logType}</span></td>";
    print "<td class=vatop>{$logtime}</td>";
    print "<td class=vatop>{$webSubject}</td>";
    print "<td width='60'>{$webContents}</td>";
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
print '<h3><font color=red>’英字、数字以外は全角で入力して下さい<br>';
print '件名内では改行が出来ません、１行のみです</font></h3>';
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

