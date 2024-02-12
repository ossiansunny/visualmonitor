<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

function getkanrino($authtype){
  $cnt=0;
  $sql='select kanrino from admintb';
  $rows=getdata($sql);
  $kno=$rows[0];
  if ($authtype=='1'){
    $cnt=10000 + intval($kno);
  }else{
    $cnt=20000 + intval($kno);
  }
  $cup=intval($kno)+1;
  $sql='update admintb set kanrino="'.strval($cup).'"';
  putdata($sql);
  return strval($cnt);
}

$pgm="UserPage.php";
$brmsg="";
$user="";
$brcode="";

if (!(isset($_GET['param']) || isset($_GET['update']) || isset($_GET['delete']) || isset($_GET['add']))){
  paramGet($pgm);
  ///
}elseif(isset($_GET['param'])){
  paramSet();
  ///
}elseif(isset($_GET['update'])){
    $user=$_GET['user'];
    if ( ! isset($_GET['select'])){
      $msg="#error#".$user."#ユーザーが選択がされていません";
      writeloge($pgm,$msg);
      branch('UserPage.php',$msg);
    }else{
      /// 更新処理      
      $idx=intval($_GET['select']);
      $auid=$_GET['uid'];
      $aupass=$_GET['upass'];
      $aauth=$_GET['auth'];
      $auname=$_GET['uname'];
      $aucode=$_GET['ucode'];
      $atstamp=$_GET['tstamp'];
      $uid=$auid[$idx];
      $upass=$aupass[$idx]; 
      $auth=$aauth[$idx];
      $uname=$auname[$idx];
      $ucode=$aucode[$idx];
      $tstamp = date('ymdHis');  
      $upsql="update user set password='".$upass."', authority='".$auth."' , username='".$uname."' , usercode='".$ucode."' , timestamp='".$tstamp."' where userid='".$uid."'";  
      putdata($upsql);
      $msg="#notic#".$user."#ユーザー".$uid."が更新されました";
      branch('UserPage.php',$msg);
      exit();
    } 
}elseif(isset($_GET['delete'])){
    $user=$_GET['user'];
    if ( ! isset($_GET['select'])){
      $msg="#error#".$user."#ユーザーが選択がされていません";
      branch('UserPage.php',$msg);
    }else{
      /// 削除処理
      $idx=intval($_GET['select']);
      $auid=$_GET['uid'];
      $uid=$auid[$idx];
      $delsql='delete from user where userid="'.$uid.'"';
      putdata($delsql);
      $msg="#notic#".$user."#ユーザー".$uid."が削除されました";
      branch('UserPage.php',$msg);
    }
}elseif(isset($_GET['add'])){
    /// 追加処理
    $user=$_GET['user'];
    $uid=$_GET['uid'];
    $upass=$_GET['upass']; 
    $auth=$_GET['auth'];
    $uname=$_GET['uname']; 
    $ucode=getkanrino($auth);
    $tstamp = date('ymdHis');
    $insql='insert into user values("'.$uid.'","'.$upass.'","'.$auth.'","'.$uname.'","'.$ucode.'","'.$tstamp.'")';  
    putdata($insql);
    $msg="#notic#".$user."#ユーザー".$uid."が追加されました";
    branch('UserPage.php','');
  
}
  
  $value="";
  $ttl1='<img src="header/php.jpg" width="30" height="30">';
  $ttl2=' ▽　ユーザー管理　▽   ';
  $ttl=$ttl1 . $ttl2;
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
  /// エラー表示
  if ($brcode=="alert" || $brcode=="error" || $brcode=="notic"){
    print '<h3 class="'.$brcode.'">"'.$brmsg.'"</h3><hr>';
    //print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }

  print "<h2>{$ttl}</h2>";
  print "<h4><font color=red>{$errormsg}</font></h4>";
  //
  //--------------------View Process --------------------
  //
  $displaysw='';
  $rows='';
  $sql='select * from user order by userid';
  $rows=getdata($sql);
  if (empty($rows)){
    print '<h4><font color=red>表示すべきデータがありません</font></h4>';
  }else{
    print '<h3>「変更」または「削除」するものを１つ選択して下さい</h3>';
    print '&nbsp;&nbsp;<form method="get" action="UserPage.php" onsubmit="return check()">';
    print '<table class="nowrap">';
    print '<tr><th>選択</th><th >ユーザーID</th><th>パスワード</th><th>権限</th><th>ユーザー名</th><th>コード</th><th>作成変更日</th></tr>';
    $iro='';
    $idx=0;
    foreach ($rows as $rowsrec){
      $sdata=explode(',',$rowsrec);
      $auth=$sdata[2];
      if ($auth=='0'){
        $iro='cwhite';
      }elseif ($auth=='1'){
        $iro='cyellow';
      }
      $uid=$sdata[0];
      $upass=$sdata[1];
      $uname=$sdata[3];
      $ucode=$sdata[4];
      $tstamp=$sdata[5];
      $triro="";
      print '<tr>';
      print '<td class=vatop><input type=radio name=select value="'.strval($idx).'" ></td>';
      print "<td class={$iro}><input type=text name=uid[] value={$uid} size=9 readonly></td>";
      print "<td class={$iro}><input type=text name=upass[] value={$upass} size=9 ></td>";
      //print '<td class="'.$iro.'"><input type=text name=auth[] value='.$auth.' size=9 ></td>';
      $ot=array('','');
      $ot[intval($auth)]="selected";
      print "<td class={$iro}><select name=auth[] >";
      print "<option value='0'{$ot[0]}>ユーザー</option>";
      print "<option value='1'{$ot[1]}>管理者</option>";
      print '</select></td>';  
      print "<td class={$iro}><input type=text name=uname[] value={$uname} size=19 ></td>";
      print "<td class={$iro}><input type=text name=ucode[] value={$ucode} size=4 readonly></td>";
      print "<td class={$iro}><input type=text name=tstamp[] value={$tstamp} size=10 readonly></td>";
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

