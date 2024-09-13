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
$alerMsg="";
if (!(isset($_GET['param']) || isset($_GET['update']) || isset($_GET['delete']) || isset($_GET['add']))){
  paramGet($pgm);
  ///
}elseif(isset($_GET['param'])){
  paramSet();
  ///
}elseif(isset($_GET['update'])){
    $user=$_GET['user'];
    if ( ! isset($_GET['select'])){
      $alerMsg="#error#".$user."#ユーザーが選択がされていません";
      writeloge($pgm,$alerMsg);
      branch('UserPage.php',$alerMsg);
    }else{
      /// 更新処理      
      $idx=intval($_GET['select']);
      $get_uid=$_GET['uid'];
      $get_upass=$_GET['upass'];
      $get_auth=$_GET['auth'];
      $get_uname=$_GET['uname'];
      $get_ucode=$_GET['ucode'];
      //$get_tstamp=$_GET['tstamp'];
      $userId=$get_uid[$idx];
      $password=$get_upass[$idx]; 
      $authority=$get_auth[$idx];
      $userName=$get_uname[$idx];
      $userCode=$get_ucode[$idx];
      $timeStamp = date('ymdHis');  
      $user_sql="update user set password='".$password."', authority='".$authority."' , username='".$userName."' , usercode='".$userCode."' , timestamp='".$timeStamp."' where userid='".$userId."'";  
      putdata($user_sql);
      $alerMsg="#notic#".$user."#ユーザー".$uid."が更新されました";
      branch('UserPage.php',$alerMsg);
      exit();
    } 
}elseif(isset($_GET['delete'])){
    $user=$_GET['user'];
    if ( ! isset($_GET['select'])){
      $alerMsg="#error#".$user."#ユーザーが選択がされていません";
      branch('UserPage.php',$alerMsg);
    }else{
      /// 削除処理
      $idx=intval($_GET['select']);
      $get_uid=$_GET['uid'];
      $userId=$get_uid[$idx];
      $user_sql='delete from user where userid="'.$userId.'"';
      putdata($user_sql);
      $alerMsg="#notic#".$user."#ユーザー".$uid."が削除されました";
      branch('UserPage.php',$alerMsg);
    }
}elseif(isset($_GET['add'])){
    /// 追加処理
    $get_user=$_GET['user'];
    $get_uid=$_GET['uid'];
    $get_upass=$_GET['upass']; 
    $get_auth=$_GET['auth'];
    $get_uname=$_GET['uname']; 
    $userCode=getkanrino($get_auth);
    $timeStamp = date('ymdHis');
    $user_sql='insert into user values("'.$get_uid.'","'.$get_upass.'","'.$get_auth.'","'.$get_uname.'","'.$userCode.'","'.$timeStamp.'")';  
    putdata($user_sql);
    $alerMsg="#notic#".$user."#ユーザー".$uid."が追加されました";
    branch('UserPage.php','');
  
}
  
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
  print '</head><body>';
  /// エラー表示
  if ($brcode=="alert" || $brcode=="error" || $brcode=="notic"){
    print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }

  print "<h2>{$title}</h2>";
  print "<h4><font color=red>{$errormsg}</font></h4>";
  //
  //--------------------View Process --------------------
  //
  $displaysw='';
  $rows='';
  $user_sql='select * from user order by userid';
  $userRows=getdata($user_sql);
  if (empty($userRows)){
    print '<h4><font color=red>表示すべきデータがありません</font></h4>';
  }else{
    print '<h3>「変更」または「削除」するものを１つ選択して下さい</h3>';
    print '&nbsp;&nbsp;<form method="get" action="UserPage.php" onsubmit="return check()">';
    print '<table class="nowrap">';
    print '<tr><th>選択</th><th >ユーザーID</th><th>パスワード</th><th>権限</th><th>ユーザー名</th><th>コード</th><th>作成変更日</th></tr>';
    $cssColor='';
    $idx=0;
    foreach ($userRows as $userRowsRec){
      $userArr=explode(',',$userRowsRec);
      $authority=$userArr[2];
      if ($authority=='0'){
        $cssColor='cwhite';
      }elseif ($authority=='1'){
        $cssColor='cyellow';
      }
      $userId=$userArr[0];
      $password=$userArr[1];
      $userName=$userArr[3];
      $userCode=$userArr[4];
      $timeStamp=$userArr[5];
      //$trcssColor="";
      print '<tr>';
      print '<td class=vatop><input type=radio name=select value="'.strval($idx).'" ></td>';
      print "<td class={$cssColor}><input type=text name=uid[] value={$userId} size=9 readonly></td>";
      print "<td class={$cssColor}><input type=text name=upass[] value={$password} size=9 ></td>";
      //print '<td class="'.$cssColor.'"><input type=text name=auth[] value='.$auth.' size=9 ></td>';
      $selOptArr=array('','');
      $selOptArr[intval($authority)]="selected";
      print "<td class={$cssColor}><select name=auth[] >";
      print "<option value='0'{$selOptArr[0]}>ユーザー</option>";
      print "<option value='1'{$selOptArr[1]}>管理者</option>";
      print '</select></td>';  
      print "<td class={$cssColor}><input type=text name=uname[] value={$userName} size=19 ></td>";
      print "<td class={$cssColor}><input type=text name=ucode[] value={$userCode} size=4 readonly></td>";
      print "<td class={$cssColor}><input type=text name=tstamp[] value={$timeStamp} size=10 readonly></td>";
      print '</tr>';
      $idx++;
    } //end of for
    print '</table>';
    print "<input type=hidden name=user value={$user}>";
    print '<br><input class=button type="submit" name="update" value="変更実行" >';
    print '&nbsp;&nbsp;<input class=buttondel type="submit" name="delete" value="削除実行" onClick="set_val()">';
    print '<input type="hidden" name="onbtn">';
    print '</form>';
  }
  print '<hr>';
  $tstamp = date('ymdHis');
  print '<table>';
  print '<form name="iform" method="get" action="UserPage.php">';
  print '<h3>追加ユーザーを入力して「作成実行」をクリック</h3>';
  print '<table class="nowrap">';
  print '<tr><th>ユーザーID</th><th>パスワード</th><th>権限</th><th>ユーザー名</th><th>コード</th><th>作成変更日</th></tr>';
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
  print '</tr>';
  print '</table>';
  print "<input type=hidden name=user value={$user}>";
  print '<br><input class=button type="submit" name="add" value="作成実行" >';
  print '</form>';
  print '<br><br>';
  print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>"; 
  print '</body></html>';

?>

