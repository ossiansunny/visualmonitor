<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

function getkanrino($_authtype){
  $rtnVal=0;
  $admin_sql='select kanrino from admintb';
  $adminRows=getdata($admin_sql);
  $adminNum=$adminRows[0];
  if ($_authtype=='1'){
    $rtnVal=10000 + intval($adminNum);
  }else{
    $rtnVal=20000 + intval($adminNum);
  }
  $adminIntVal=intval($adminNum)+1;
  $admin_sql='update admintb set kanrino="'.strval($adminIntVal).'"';
  putdata($admin_sql);
  return strval($rtnVal);
}

$pgm="UserPage.php";
$brmsg="";
$user="";
$brcode="";
//$alerMsg="";
//echo '<br>param:';
//var_dump($_GET['update']);
//echo '<br>param:';
//var_dump($_GET['delete']);
//echo '<br>param:';
//var_dump($_GET['add']);
//echo '<br>user:';
//$user=$_GET['user'];
if (!(isset($_GET['param']) or isset($_GET['update']) or isset($_GET['delete']) or isset($_GET['add']))){
  paramGet($pgm);
  ///

}elseif(isset($_GET['update'])){
  //echo '<br>after update';
    $user=$_GET['user'];
    //echo $user.'<br>';
    if ( ! isset($_GET['select'])){
      $brmsg="ユーザーが選択がされていません";
      $brcode='error';
      //branch('UserPage.php',$alerMsg);
    }else{
      /// 更新処理      
      $idx=intval($_GET['select']);
      $get_uid=$_GET['uid'];
      $get_upass=$_GET['upass'];
      $get_auth=$_GET['auth'];
      $get_uname=$_GET['uname'];
      $get_ucode=$_GET['ucode'];
      $get_bgcolor=$_GET['bgcolor'];
      $get_audio=$_GET['audio'];
      $userId=$get_uid[$idx];
      $password=$get_upass[$idx]; 
      $authority=$get_auth[$idx];
      $userName=$get_uname[$idx];
      $userCode=$get_ucode[$idx];
      $timeStamp = date('ymdHis');  
      $bgColor = $get_bgcolor[$idx];
      $audio = $get_audio[$idx];
      $user_sql="update user set password='".$password."',authority='".$authority."',username='".$userName."',usercode='".$userCode."',timestamp='".$timeStamp."',bgcolor='".$bgColor."',audio='".$audio."' where userid='".$userId."'";  
      putdata($user_sql);
      $brmsg="ユーザー".$userId."が更新されました";
      $brcode='notic';
      //branch('UserPage.php',$alerMsg);
      
    } 
}elseif(isset($_GET['delete'])){
//echo '<br>after delete';
    $user=$_GET['user'];
    if ( ! isset($_GET['select'])){
      $brmsg="ユーザーが選択がされていません";
      $brcode='error';
      //branch('UserPage.php',$alerMsg);
    }else{
      /// 削除処理 delete
      $idx=intval($_GET['select']);
      $get_uid=$_GET['uid'];
      $userId=$get_uid[$idx];
      $user_sql='delete from user where userid="'.$userId.'"';
      putdata($user_sql);
      $brmsg="ユーザー".$userId."が削除されました";
      $brcode='notic';
      //branch('UserPage.php',$alerMsg);
    }
}elseif(isset($_GET['add'])){
    /// 追加処理 add
    $user=$_GET['user'];
    
    $get_uid=$_GET['uid'];
    $get_upass=$_GET['upass']; 
    $get_auth=$_GET['auth'];
    $get_uname=$_GET['uname']; 
    $get_bgcolor=$_GET['bgcolor'];
    $get_audio=$_GET['audio'];
    $userCode=getkanrino($get_auth);
    $timeStamp = date('ymdHis');
    $user_sql='insert into user (userid,password,authority,username,usercode,timestamp,bgcolor,audio) values("'.$get_uid.'","'.$get_upass.'","'.$get_auth.'","'.$get_uname.'","'.$userCode.'","'.$timeStamp.'","'.$get_bgcolor.'","'.$get_audio.'")';  
    putdata($user_sql);
    $brmsg="ユーザー".$get_uid."が追加されました";
    $brcode='notic';
    //branch('UserPage.php','');  
    ///
}elseif(isset($_GET['param'])){

  paramSet();
  
}
  //echo '<br>after other:';
  //echo $user.'<br>';
if (! $user==""){
  $user_sql="select authority,bgcolor from user where userid='".$user."'";
  $userRows=getdata($user_sql);
  if(empty($userRows)){
    $msg="#error#unkown#ユーザを見失いました";
    branch('logout.php',$msg);
  }
  $userArr=explode(',',$userRows[0]);
  $authority=$userArr[0];
  $bgColor=$userArr[1];
  
  $value="";
  $title1='<img src="header/php.jpg" width="30" height="30">';
  $title2=' ▽　ユーザー管理　▽   ';
  $title=$title1 . $title2;
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
  print "</head><body class={$bgColor}>";
  /// エラー表示
  if ($brcode=="alert" or $brcode=="error" or $brcode=="notic"){
    print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }
  print "<h2>{$title}</h2>";
  /// 
  //print "<h4><font color=red>{$errormsg}</font></h4>";
  ///
  ///--------------------表示処理 --------------------
  ///
  $displaysw='';
  $rows='';
  $user_sql='select userid,password,authority,username,usercode,timestamp,bgcolor,audio from user order by userid';
  #$user_sql='select * from user order by userid';
  $userRows=getdata($user_sql);
  if (empty($userRows)){
    print '<h4><font color=red>表示すべきデータがありません</font></h4>';
  }else{
    /// 変更削除データ表示
    print '<h3>☆警告音が試聴出来ます、再生ボタンをクリックして下さい<br>';
    print '<font color=yellow>注意：ブラウザの音声自動再生が許可状態に限ります</font></h3>';
    print '<table boder=1>';
    print '<tr><th>無音</th><th>警告音１</th><th>警告音２</th><th>警告音３</th><th>警告音４</th></tr>';
    print '<tr>';
    print '<td><audio  controls src="audio/nonalert.mp3"></audio></td>';
    print '<td><audio  controls src="audio/alert1.mp3"></audio></td>';
    print '<td><audio  controls src="audio/alert2.mp3"></audio></td>';
    print '<td><audio  controls src="audio/alert3.mp3"></audio></td>';
    print '<td><audio  controls src="audio/alert4.mp3"></audio></td>';
    print '</tr>';
    print '</table>';
    print '<hr>';
    print '<h3>☆背景色・画像一覧<br>背景色欄を選択して下さい</h3>';
    //print '<table boder=1>';
    //print '<tr><th>無音</th><th>警告音１</th><th>警告音２</th><th>警告音３</th><th>警告音４</th></tr>';
    //print '<tr>';
    print '<table>';
    print '<tr><th>標準</th><th>bgimage1</th><th>bgimage2</th><th>bgimage3</th><th>bgimage4</th><th>bgimage5</th></tr>';
    print '<tr><td><img src=header/bgstand.jpg width=120px height=100px></td>';
    print '<td><img src=header/bgimage1.jpg width=120px height=100px></td>';
    print '<td><img src=header/bgimage2.jpg width=120px height=100px></td>';
    print '<td><img src=header/bgimage3.jpg width=120px height=100px></td>';
    print '<td><img src=header/bgimage4.jpg width=120px height=100px></td>';
    print '<td><img src=header/bgimage5.jpg width=120px height=100px></td></tr>';
    print '</table>';
    print '<hr>';
    print '&nbsp;&nbsp;<form method="get" action="UserPage.php" onsubmit="return check()">';
    print '<h3>☆「変更」または「削除」するものを１つ選択して下さい</h3>';
    print '<table class="nowrap">';
    print '<tr><th>選択</th><th >ユーザーID</th><th>パスワード</th><th>権限</th><th>ユーザー名</th><th>コード</th><th>作成変更日</th><th>背景色</th><th>警告音</th></tr>';
    $cssColor='';
    $idx=0;
    foreach ($userRows as $userRowsRec){
      $userArr=explode(',',$userRowsRec);
      $rd_authority=$userArr[2];
      if ($rd_authority=='0'){
        $cssColor='cwhite';
      }elseif ($rd_authority=='1'){
        $cssColor='cyellow';
      }
      $rd_userId=$userArr[0];
      $rd_password=$userArr[1];
      $rd_userName=$userArr[3];
      $rd_userCode=$userArr[4];
      $rd_timeStamp=$userArr[5];
      $rd_bgColor=$userArr[6];
      $selBgColorArr=array('','','','','','','','','','');
      switch($rd_bgColor){
        case "bgstand": $selBgColorArr[0]="selected"; break;
        case "bgimage1": $selBgColorArr[1]="selected"; break;
        case "bgimage2": $selBgColorArr[2]="selected"; break;
        case "bgimage3": $selBgColorArr[3]="selected"; break;
        case "bgimage4": $selBgColorArr[4]="selected"; break;
        case "bgimage5": $selBgColorArr[5]="selected"; break;
      }  
      $rd_audio=$userArr[7];
      $selAudioArr=array('','','','','');
      switch($rd_audio){
        case "nonalert.mp3": $selAudioArr[0]="selected"; break;
        case "alert1.mp3": $selAudioArr[1]="selected"; break;
        case "alert2.mp3": $selAudioArr[2]="selected"; break;
        case "alert3.mp3": $selAudioArr[3]="selected"; break;
        case "alert4.mp3": $selAudioArr[4]="selected"; break;
      }
      ///  該当ユーザー($user)で一般ユーザ権限(0)および管理者権限のみ以下実行
      if ($authority=='1' or ($rd_authority=='0' and $rd_userId==$user)) {
        print '<tr>';
        print '<td ><input class={$cssColor} type=radio name=select value="'.strval($idx).'" ></td>';
        print "<td ><input class={$cssColor} type=text name=uid[] value={$rd_userId} size=9 readonly></td>";
        print "<td ><input class={$cssColor} type=text name=upass[] value={$rd_password} size=9 ></td>";
        $selOptArr=array('','');
        $selOptArr[intval($rd_authority)]="selected";
        print "<td ><select class={$cssColor} name=auth[] >";
        print "<option value='0'{$selOptArr[0]}>一般ユーザー</option>";
        print "<option value='1'{$selOptArr[1]}>管理者</option>";
        print '</select></td>';
        print "<td ><input class={$cssColor} type=text name=uname[] value={$rd_userName} size=19 ></td>";
        print "<td ><input class={$cssColor} type=text name=ucode[] value={$rd_userCode} size=4 readonly></td>";
        print "<td ><input class={$cssColor} type=text name=tstamp[] value={$rd_timeStamp} size=10 readonly></td>";
        print "<td><select class={$cssColor} name=bgcolor[]>";
        print "<option value='bgstand' {$selBgColorArr[0]}>標準</option>";
        print "<option value='bgimage1' {$selBgColorArr[1]}>bgimage1</option>";
        print "<option value='bgimage2' {$selBgColorArr[2]}>bgimage2</option>";
        print "<option value='bgimage3' {$selBgColorArr[3]}>bgimage3</option>";
        print "<option value='bgimage4' {$selBgColorArr[4]}>bgimage4</option>";
        print "<option value='bgimage5' {$selBgColorArr[5]}>bgimage5</option>";
        print '</select></td>';
        print "<td><select class={$cssColor} name=audio[]>";
        print "<option value='nonalert.mp3' {$selAudioArr[0]}>無音</option>";
        print "<option value='alert1.mp3' {$selAudioArr[1]}>警告音１</option>";
        print "<option value='alert2.mp3' {$selAudioArr[2]}>警告音２</option>";
        print "<option value='alert3.mp3' {$selAudioArr[3]}>警告音３</option>";
        print "<option value='alert4.mp3' {$selAudioArr[4]}>警告音４</option>";
        print "</select></td>"; 
        print '</tr>';
      }
      $idx++;
      ///
    } ///end of for
    print '</table>';
    print "<input type=hidden name=user value={$user}>";
    print "<br><input class='button' type='submit' name='update' value='変更実行' >";
    print '&nbsp;&nbsp;<input class="buttondel" type="submit" name="delete" value="削除実行" onClick="set_val()">';
    print '<input type="hidden" name="onbtn">';
    print '</form>';
  }
  if ($authority=='1') {
    /// 新規入力データ
    print '<hr>';
    $tstamp = date('ymdHis');
    print '<form name="iform" method="get" action="UserPage.php">';
    print '<h3>追加ユーザーを入力して「作成実行」をクリック</h3>';
    print '<table class="nowrap">';
    print '<tr><th>ユーザーID</th><th>パスワード</th><th>権限</th><th>ユーザー名</th><th>コード</th><th>作成変更日</th><th>背景色</th><th>警告音</th></tr>';
    print '<tr>';
    print '<td> <input type="text" name="uid" size=9 value="" placeholder="MAX半角10桁"></td>';
    print '<td> <input type="text" name="upass" size=9 value="" placeholder="MAX半角10桁"></td>';
    print '<td><select name="auth">';
    print '<option value="0">一般ユーザー</option>';
    print '<option value="1">管理者</option>';
    print '</select></td>';
    print '<td> <input type="text" name="uname" size=19 value="" placeholder="MAX全角10桁"></td>';
    print '<td> <input type="text" name="ucode" value="" size=4 placeholder="自動採番" readonly></td>';
    print "<td> <input type='text' name='tstamp' value={$tstamp} size=10 readonly></td>";
    print '<td><select name="bgcolor">';
    print "<option value='bgstand' selected>標準</option>";
    print "<option value='bgimage1'>bgimage1</option>";
    print "<option value='bgimage2'>bgimage2</option>";
    print "<option value='bgimage3'>bgimage3</option>";
    print "<option value='bgimage4'>bgimage4</option>";
    print "<option value='bgimage5'>bgimage5</option>";
    print '</select></td>';
    print "<td><select name='audio'>";
    print "<option value='nonalert.mp3' selected>無音</option>";
    print "<option value='alert1.mp3'>警告音１</option>";
    print "<option value='alert2.mp3'>警告音２</option>";
    print "<option value='alert3.mp3'>警告音３</option>";
    print "<option value='alert4.mp3'>警告音４</option>";
    print "</select></td>"; 
    print '</tr>';
    print '</table>';
    print "<input type=hidden name=user value='".$user."'>";
    print '<br><input class=button type="submit" name="add" value="作成実行" >';
    print '</form>';
    print '<br><br>';
  }
  print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>"; 
  print '</body></html>';
}
?>

