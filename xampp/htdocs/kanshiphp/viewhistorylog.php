<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
print '<html><head><meta>';
print '<link rel="stylesheet" href="kanshi1.css">';
print '</head><body>';
$pgm = "viewhistorylog.php";
$user="";
$type="";
///-----------------------------------------------------------
///-----historylog--------------------------------------------
///---- "0:type" "1:datestamp" "2:subject" "3:contents" 
///-----------------------------------------------------------
if (isset($_GET['add'])){
  print '<h2><img src="header/php.jpg" width="30" height="30">▽　履歴データの作成と更新・削除処理　▽</h2>';
  $user=$_GET['user'];
  $logtype=$_GET["logtype"];
  $logdate=$_GET["date"];
  $logsubj=htmlspecialchars($_GET["subj"],ENT_QUOTES);
  $logcont=htmlspecialchars($_GET["mesg"],ENT_QUOTES);
  $insql='insert into historylog values("'.$logtype.'","'.$logdate.'","'.$logsubj.'","'.$logcont.'")';  
  putdata($insql);
  $type="追加";
  $nextpage='HistoryPage.php';
  $msg="#notic#".$user."#".$type."処理が完了しました";
  branch($nextpage,$msg);
}elseif (! isset($_GET['select'])){
  $nextpage='HistoryPage.php';
  $msg="#notic#".$user."#ホストが選択されていません";
  branch($nextpage,$msg);
  
}elseif (isset($_GET['delete'])){
  /// 削除処理
  $user=$_GET['user'];
  $strdata=$_GET['select'];
  $sdata=explode(",",$strdata);
  $logtime=$sdata[1];
  $delsql='delete from historylog where logtime="'.$logtime.'"';
  putdata($delsql);
  $type="削除";
  $nextpage='HistoryPage.php';
  $msg="#notic#".$user."#".$type."処理が完了しました";
  branch($nextpage,$msg);
}else{
  /// 更新処理
  print '<h3>履歴データを修正して、「更新実行」をクリックして下さい</h4>';
  print '<h3><font color=red>英字、数字以外は全角を使用して下さい<br>';
  print '件名は１行のみです</font></h3>';
  $strdata=$_GET['select'];
var_dump($strdata);
  $user=$_GET['user'];
  $sdata=explode(',',$strdata,4);
  $logtype=$sdata[0];
  $logtime=$sdata[1];
  //if (! is_null($sdata[2])){
  //  $subject=$sdata[2];
  //  $websubject=htmlspecialchars($subject,ENT_QUOTES);
  //  $websubject=str_replace("\\","\\\\",$websubject);
  //}else{
  //  $websubject='';
  //} 
  $tsql="select * from historylog where logtime='".$logtime."'";
  $rows=getdata($tsql);
  $tdata=explode(',',$rows[0]);
  if (! is_null($tdata[2])){
    $subject=$tdata[2];
    $websubject=htmlspecialchars($subject,ENT_QUOTES);
    $websubject=str_replace("\\","\\\\",$websubject);
  }else{
    $websubject='';
  } 
  if (! is_null($tdata[3])){
    $contents=$tdata[3];
    $webcontents=htmlspecialchars($contents,ENT_QUOTES);
    $rctr=strval(substr_count($webcontents,"\n")+1); //行数を調べる
  }else{
    $wcstr='';
    $rctr="0";
  }
  $dte = date('ymdHis');
  print '<table class="nowrap">';
  print '<tr><th >ログタイプ</th><th>日付:時刻</th><th>件名</th><th>内容</th></tr>';
  print '<form name=fupdate action="historyupdb.php" method=get>';
  print '<tr><td style="vertical-align: top;">';
  $ot=array('','','','','','','','','','');
  $ot[intval($logtype)]="selected";
  print '<select name=logtype >';
  print '<option value="0"'.$ot[0].'>未設定</option>';
  print '<option value="1"'.$ot[1].'>計画中</option>';
  print '<option value="2"'.$ot[2].'>処理中</option>';
  print '<option value="7"'.$ot[7].'>参考</option>';
  print '<option value="8"'.$ot[8].'>注意</option>';
  print '<option value="9"'.$ot[9].'>履歴保存</option>';
  print '</select></td>';
  print '<td style="vertical-align: top;"><input type="text" name="logtime" value="'.$logtime.'" size="12" readonly></td>';
  print '<td style="vertical-align: top;"><input type="text" name="logsubj" value="'.$websubject.'" size="25" ></td>';
  print '<td><textarea name="logcont" rows="'.$rctr.'" cols="70">'.$webcontents.'</textarea></td>';
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

