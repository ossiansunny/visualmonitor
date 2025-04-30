<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
print '<html><head><meta>';
print '<link rel="stylesheet" href="css/kanshi1_py.css">';
print '</head><body>';
$pgm = "viewhistorylog.php";
$user="";
$actionType="";
$alertMsg="";
///-----------------------------------------------------------
///-----historylog--------------------------------------------
///---- "0:type" "1:datestamp" "2:subject" "3:contents" 
///-----------------------------------------------------------
if (isset($_GET['add'])){
  print '<h2><img src="header/php.jpg" width="30" height="30">▽　履歴データの作成と更新・削除処理　▽</h2>';
  $user=$_GET['user'];
  $logType=$_GET["logtype"];
  $logDate=$_GET["date"];
  $logSubj=htmlspecialchars($_GET["subj"],ENT_QUOTES);
  $logContent=htmlspecialchars($_GET["mesg"],ENT_QUOTES);
  $hist_sql='insert into historylog values("'.$logType.'","'.$logDate.'","'.$logSubj.'","'.$logContent.'")';  
  putdata($hist_sql);
  $actionType="追加";
  $nextpage='HistoryPage.php';
  $alertMsg="#notic#".$user."#".$actionType."処理が完了しました";
  branch($nextpage,$alertMsg);
}elseif (! isset($_GET['select'])){
  $nextpage='HistoryPage.php';
  $alertMsg="#notic#".$user."#ホストが選択されていません";
  branch($nextpage,$alertMsg);
  
}elseif (isset($_GET['delete'])){
  /// 削除処理
  $user=$_GET['user'];
  $histRow=$_GET['select'];
  $histArr=explode(",",$histRow);
  $logTimeStamp=$histArr[1];
  $hist_sql='delete from historylog where logtime="'.$logTimeStamp.'"';
  putdata($hist_sql);
  $actionType="削除";
  $nextpage='HistoryPage.php';
  $alertMsg="#notic#".$user."#".$actionType."処理が完了しました";
  branch($nextpage,$alertMsg);
}else{
  /// 更新処理
  print '<h3>履歴データを修正して、「更新実行」をクリックして下さい</h4>';
  print '<h3><font color=red>英字、数字以外は全角を使用して下さい<br>';
  print '件名は１行のみです</font></h3>';
  $histRow=$_GET['select'];
  $user=$_GET['user'];
  $histArr=explode(',',$histRow,4);
  $logType=$histArr[0];
  $logTimeStamp=$histArr[1];
  $hist_sql="select * from historylog where logtime='".$logTimeStamp."'";
  $histRows=getdata($hist_sql);
  $histArr=explode(',',$histRows[0]);
  if (! is_null($histArr[2])){
    $subject=$histArr[2];
    $webSubject=htmlspecialchars($subject,ENT_QUOTES);
    $webSubject=str_replace("\\","\\\\",$webSubject);
  }else{
    $webSubject='';
  } 
  if (! is_null($histArr[3])){
    $contents=$histArr[3];
    $webContents=htmlspecialchars($contents,ENT_QUOTES);
    $lineCount=strval(substr_count($webContents,"\n")+1); ///行数を調べる
  }else{
    $lineCount="0";
  }
  print '<table class="nowrap">';
  print '<tr><th >ログタイプ</th><th>日付:時刻</th><th>件名</th><th>内容</th></tr>';
  print '<form name=fupdate action="historyupdb.php" method=get>';
  print '<tr><td style="vertical-align: top;">';
  $selOptArr=array('','','','','','','','','','');
  $selOptArr[intval($logType)]="selected";
  print '<select name=logtype >';
  print '<option value="0"'.$selOptArr[0].'>未設定</option>';
  print '<option value="1"'.$selOptArr[1].'>計画中</option>';
  print '<option value="2"'.$selOptArr[2].'>処理中</option>';
  print '<option value="7"'.$selOptArr[7].'>参考</option>';
  print '<option value="8"'.$selOptArr[8].'>注意</option>';
  print '<option value="9"'.$selOptArr[9].'>履歴保存</option>';
  print '</select></td>';
  print '<td style="vertical-align: top;"><input type="text" name="logtime" value="'.$logTimeStamp.'" size="12" readonly></td>';
  print '<td style="vertical-align: top;"><input type="text" name="logSubj" value="'.$webSubject.'" size="25" ></td>';
  print '<td><textarea name="logcont" rows="'.$lineCount.'" cols="70">'.$webContents.'</textarea></td>';
  print '</tr>';
  print '</table>';
  print "<input type=hidden name user=user value={$user}>";
  print '<br><input class=button type="submit" name="update" value="更新実行" >';
  print '</form>';
  
}
print '<br><br>';
print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>"; 
print '</body></html>';
?>

