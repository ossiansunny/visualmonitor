<?php
require_once "mysqlkanshi.php";

function branch($_page,$_param){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo "<form name='F' action={$_page} method='get'>";
  echo "<input type=hidden name=param value={$_param}>";
  echo '<input type="submit" name="next" value="Waiting...">';
  echo '</form>';
}

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

$brmsg="";
$user="";
$brcode="";

if (!(isset($_GET['param']) || isset($_GET['update']) || isset($_GET['delete']) || isset($_GET['add']))){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="UserPage.php" method="get">';
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
}elseif(isset($_GET['param'])){
  $inform=$_GET['param'];
  if (substr($inform,0,1)=="#"){
    $brarr=explode("#",ltrim($inform,"#"),4);
    $brcode=$brarr[0]; /// code
    $user=$brarr[1];   /// user
    $brmsg=$brarr[2];  /// message
  }else{
    $user=$inform;
  }
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
  echo '<html><head>';
  echo '<link rel="stylesheet" href="kanshi1_py.css">';
  echo '<script language="JavaScript">';
  echo 'function check(){';
  echo '  if (document.rform.onbtn.value == "delete"){';
  echo '    if (window.confirm("削除してよろしいですか？")){';
  echo '      return true;';
  echo '    }else{';
  echo '      window.alert("キャンセルします");';
  echo '      return false;';
  echo '    }';
  echo '  }else{';
  echo '    return true;';
  echo '  }';
  echo '}';
  echo 'function set_val(){';
  echo '  document.rform.onbtn.value = "delete";';
  echo '}';
  echo '</script>';
  echo '</head><body>';
  /// エラー表示
  if ($brcode=="alert" || $brcode=="error" || $brcode=="notic"){
    echo "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  }

  echo "<h2>{$ttl}</h2>";
  echo "<h4><font color=red>{$errormsg}</font></h4>";
  //
  //--------------------View Process --------------------
  //
  $displaysw='';
  $rows='';
  $sql='select * from user order by userid';
  $rows=getdata($sql);
  if (empty($rows)){
    echo '<h4><font color=red>表示すべきデータがありません</font></h4>';
  }else{
    echo '<h3>「変更」または「削除」するものを１つ選択して下さい</h3>';
    echo '&nbsp;&nbsp;<form method="get" action="UserPage.php" onsubmit="return check()">';
    echo '<table class="nowrap">';
    echo '<tr><th>選択</th><th >ユーザーID</th><th>パスワード</th><th>権限</th><th>ユーザー名</th><th>コード</th><th>作成変更日</th></tr>';
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
      echo '<tr>';
      echo '<td class=vatop><input type=radio name=select value="'.strval($idx).'" ></td>';
      echo "<td class={$iro}><input type=text name=uid[] value={$uid} size=9 readonly></td>";
      echo "<td class={$iro}><input type=text name=upass[] value={$upass} size=9 ></td>";
      //echo '<td class="'.$iro.'"><input type=text name=auth[] value='.$auth.' size=9 ></td>';
      $ot=array('','');
      $ot[intval($auth)]="selected";
      echo "<td class={$iro}><select name=auth[] >";
      echo "<option value='0'{$ot[0]}>ユーザー</option>";
      echo "<option value='1'{$ot[1]}>管理者</option>";
      echo '</select></td>';  
      echo "<td class={$iro}><input type=text name=uname[] value={$uname} size=19 ></td>";
      echo "<td class={$iro}><input type=text name=ucode[] value={$ucode} size=4 readonly></td>";
      echo "<td class={$iro}><input type=text name=tstamp[] value={$tstamp} size=10 readonly></td>";
      echo '</tr>';
      $idx++;
    } //end of for
    echo '</table>';
    echo "<input type=hidden name=user value={$user}>";
    echo '<br><input class=button type="submit" name="update" value="変更実行" >';
    echo '&nbsp;&nbsp;<input class=buttondel type="submit" name="delete" value="削除実行" onClick="set_val()">';
    echo '<input type="hidden" name="onbtn">';
    echo '</form>';
  }
  echo '<hr>';
  $tstamp = date('ymdHis');
  echo '<table>';
  echo '<form name="iform" method="get" action="UserPage.php">';
  echo '<h3>追加ユーザーを入力して「作成実行」をクリック</h3>';
  echo '<table class="nowrap">';
  echo '<tr><th>ユーザーID</th><th>パスワード</th><th>権限</th><th>ユーザー名</th><th>コード</th><th>作成変更日</th></tr>';
  echo '<tr>';
  echo '<td> <input type="text" name="uid" size=9 value="" placeholder="MAX半角10桁"></td>';
  echo '<td> <input type="text" name="upass" size=9 value="" placeholder="MAX半角10桁"></td>';
  echo '<td><select name="auth">';
  echo '<option value="0">一般ユーザー</option>';
  echo '<option value="1">管理者</option>';
  echo '</select></td>';
  echo '<td> <input type="text" name="uname" size=19 value="" placeholder="MAX全角10桁"></td>';
  echo '<td> <input type="text" name="ucode" value="" size=4 placeholder="自動採番" readonly></td>';
  echo "<td> <input type='text' name='tstamp' value={$tstamp} size=10 readonly></td>";
  echo '</tr>';
  echo '</table>';
  echo "<input type=hidden name=user value={$user}>";
  echo '<br><input class=button type="submit" name="add" value="作成実行" >';
  echo '</form>';
  echo '<br><br>';
  echo "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>"; 
  echo '</body></html>';

?>
