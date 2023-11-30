<?php
require_once "mysqlkanshi.php";
echo '<html><head><meta>';
echo '<link rel="stylesheet" href="kanshi1.css">';
echo '</head><body>';
$pgm = "viewhistorylog.php";
$user="";
///-----------------------------------------------------------
///-----historylog--------------------------------------------
///---- "0:type" "1:datestamp" "2:subject" "3:contents" 
///-----------------------------------------------------------
if (isset($_GET['add'])){
  echo '<h2><img src="header/php.jpg" width="30" height="30">▽　履歴データの作成と更新・削除処理　▽</h2>';
  $user=$_GET['user'];
  $logtype=$_GET["logtype"];
  $logdate=$_GET["date"];
  $logsubj=htmlspecialchars($_GET["subj"],ENT_QUOTES);
  $logcont=htmlspecialchars($_GET["mesg"],ENT_QUOTES);
  $insql='insert into historylog values("'.$logtype.'","'.$logdate.'","'.$logsubj.'","'.$logcont.'")';  
  putdata($insql);
}elseif (! isset($_GET['select'])){
  echo '<h4><font color=red>ホストが選択されていません</font></h4>';
  echo '<a href="HistoryPage.php"><span class=buttonyell>履歴管理へ戻る</span></a>';
  
}elseif (isset($_GET['delete'])){
  /// 削除処理
  $user=$_GET['user'];
  $strdata=$_GET['select'];
  $sdata=explode(",",$strdata);
  $logtime=$sdata[1];
  $delsql='delete from historylog where logtime="'.$logtime.'"';
  putdata($delsql);

}else{
  /// 更新処理
  echo '<h4>履歴データを修正して、「更新実行」をクリックして下さい</h4>';
  echo '<h4><font color=red>半角の「\」は「\\\\」、「\\\\」は「\\\\\\\\」と入力します<br>';
  echo '表示できない特殊文字は全角を使って下さい</font></h4>';
  $strdata=$_GET['select'];
  $user=$_GET['user'];
  $sdata=explode(',',$strdata,4);
  $logtype=$sdata[0];
  $logtime=$sdata[1];
  if (! is_null($sdata[2])){
    $subject=$sdata[2];
    $websubject=htmlspecialchars($subject,ENT_QUOTES);
    $websubject=str_replace("\\","\\\\",$websubject);
  }else{
    $websubject='';
  } 
  if (! is_null($sdata[3])){
    $contents=$sdata[3];
    $webcontents=htmlspecialchars($contents,ENT_QUOTES);
    $rctr=strval(substr_count($webcontents,"\n")+1); //行数を調べる
  }else{
    $wcstr='';
    $rctr="0";
  }
  $dte = date('ymdHis');
  echo '<table class="nowrap">';
  echo '<tr><th >ログタイプ</th><th>日付:時刻</th><th>件名</th><th>内容</th></tr>';
  echo '<form name=fupdate action="historyupdb.php" method=get>';
  echo '<tr><td style="vertical-align: top;">';
  $ot=array('','','','','','','','','','');
  $ot[intval($logtype)]="selected";
  echo '<select name=logtype >';
  echo '<option value="0"'.$ot[0].'>未設定</option>';
  echo '<option value="1"'.$ot[1].'>計画中</option>';
  echo '<option value="2"'.$ot[2].'>処理中</option>';
  echo '<option value="7"'.$ot[7].'>参考</option>';
  echo '<option value="8"'.$ot[8].'>注意</option>';
  echo '<option value="9"'.$ot[9].'>履歴保存</option>';
  echo '</select></td>';
  echo '<td style="vertical-align: top;"><input type="text" name="logtime" value="'.$logtime.'" size="12" readonly></td>';
  echo '<td style="vertical-align: top;"><input type="text" name="logsubj" value="'.$websubject.'" size="25" ></td>';
  echo '<td><textarea name="logcont" rows="'.$rctr.'" cols="70">'.$webcontents.'</textarea></td>';
  echo '</tr>';
  echo '</table>';
  echo "<input type=hidden name user=user value={$user}>";
  echo '<br><input class=button type="submit" name="update" value="更新実行" >';
  echo '</form>';
  
}
echo '<br><br>';
echo "<a href='HistoryPage.php?param={$user}'><span class=buttonyell>履歴管理へ戻る</span></a>"; 
echo '</body></html>';
?>
