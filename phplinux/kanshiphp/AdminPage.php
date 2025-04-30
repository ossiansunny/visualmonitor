<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "mailsend.php";

function nullcheck($_data){
  if (isset($_data)){
    return $_data;
  }else{
    return ' ';
  }
} 

///
$pgm="AdminPage.php";
$user='';
$brcode="";
$brmsg="";

if ((!isset($_GET['param'])) and (!isset($_GET['update']))){  
  ///　セッション保存のユーザ取得
  paramGet($pgm);
}else{
  paramSet();
  /// admintbの更新
  $now=date('ymdHis');
  $timeStamp = $now;
  if (isset($_GET['update'])){
    $user=$_GET['user'];
    $authority=$_GET['auth'];
    $mailToAddr=$_GET['recv'];
    $mailFromAddr=$_GET['sender'];
    $subject=$_GET['subj'];
    $body=$_GET['body'];
    $monIntVal=$_GET['monintval'];
    $snmpIntVal=$_GET['snmpintval'];
    $debug=$_GET['debug'];
    $adminNum=$_GET['kanrino'];
    $hostViewEnable=$_GET['hosthyouji'];
    $bgPicture=$_GET['haikei'];
    $bgColor=$_GET['bgcolor'];
    $admin_sql="update admintb set 
         authority='".$authority."',
         receiver='".$mailToAddr."',
         sender='".$mailFromAddr."',
         subject='".$subject."',
         body='".$body."',
         monintval='".$monIntVal."',
         snmpintval='".$snmpIntVal."',
         debug='".$debug."',
         kanrino='".$adminNum."',
         hosthyouji='".$hostViewEnable."',
         haikei='".$bgPicture."',
         bgcolor='".$bgColor."'";
    ///
    $uprc=putdata($admin_sql); 
    $okSqlMsg=str_replace('<','&lt;',$admin_sql);
    $okSqlMsg=str_replace('>','&gt;',$okSqlMsg);
    /// update montime,coretime of processtb
    $proc_sql="update processtb set montime={$monIntVal}, coretime={$snmpIntVal}";  
    putdata($proc_sql);
    /// 
    if($uprc == 0){    
      $msg='AdminTB Updated sql: '.$okSqlMsg;
      writelogd($pgm,$msg);
      $event_sql = "insert into eventlog (host,eventtime,eventtype,kanrisha) values('".$user."','".$timeStamp."','3','".$user."')";
      putdata($event_sql);
      $message='Update admintb';
      mailsend('',$user,'0','管理情報変更','','','');
      
    }else{
      $msg='AdminTB Not updated sql: '.$upsqlmsg; 
      writeloge($pgm,$msg);   
    }
    $nextpage="MonitorManager.php";
    branch($nextpage,$user);
    
  ///　画面表示処理
  }else{
    $user=$_GET['param'];
    $admin_sql="select * from admintb";
    $adminRows=getdata($admin_sql);
    $adminArr=explode(',',$adminRows[0]);
    $authority=$adminArr[2];
    $mailToAddr=$adminArr[3];
    $mailFromAddr=$adminArr[4];
    $subject=nullcheck($adminArr[5]);
    $body=nullcheck($adminArr[6]);
    $monIntVal=strval($adminArr[7]);
    $snmpIntVal=strval($adminArr[8]);
    $debug=$adminArr[9];
    $adminNum=$adminArr[10];
    $selHostView0='';
    $selHostView1='';
    if ($adminArr[13]=='0'){
      $selHostView0='selected';
      $selHostView1='';
    }else{
      $selHostView0='';
      $selHostView1='selected';
    }
    $bgPicture=$adminArr[14];
    $selBgPictureArr=array('','','','');
    if ($bgPicture == 'ki.png'){
      $selBgPictureArr[0]='selected';
    }elseif ($bgPicture == 'umi.png'){
      $selBgPictureArr[1]='selected';
    }elseif ($bgPicture == 'aka.png'){
      $selBgPictureArr[2]='selected';
    }elseif ($bgPicture == 'ha.png'){
      $selBgPictureArr[3]='selected';
    }else{
      $selBgPictureArr[0]='selected';
    }
    $selBgColorArr=array('','','','','');
    switch($adminArr[17]){
      case "bgdarks": $selBgColorArr[0]="selected"; break;
      case "bgbrown": $selBgColorArr[1]="selected"; break;
      case "bgindig": $selBgColorArr[2]="selected"; break;
      case "bgpurpl": $selBgColorArr[3]="selected"; break;
      case "bgblack": $selBgColorArr[4]="selected"; break;
    }
    $user_sql="select authority,bgcolor,usercode from user where userid='".$user."'";
    $userRows=getdata($user_sql);
    if(empty($userRows)){
      $msg="#error#unkown#ユーザを見失いました";
      branch('logout.php',$msg);
    }
    $userArr=explode(',',$userRows[0]);
    $authority=$userArr[0];
    $bgColor=$userArr[1];
    $adminNum=$userArr[2];
    print '<html><head><meta>';
    print '<link rel="stylesheet" href="css/kanshi1_py.css">';
    print "</head><body class={$bgColor}>";
    print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　管理情報　▽</h2>';
    ///
    print '<h3>&emsp;&emsp;必要な欄を修正して、<span class=trblk>「更新実行」</span>をクリック</h3>';
    print '<h4>&emsp;&emsp;&emsp;☆監視モニタ間隔：監視モニターの起動間隔<br>';
    print '&emsp;&emsp;&emsp;☆監視コア間隔：監視コアの起動間隔<br>';
    print '&emsp;&emsp;&emsp;☆権限：1 管理者操作中　変更不可<br>';        
    print '&emsp;&emsp;&emsp;☆管理番号:管理者（変更不可、「ユーザ管理」で変更）<br>';
    print '&emsp;&emsp;&emsp;☆ホスト表示：なし=表示名の上部にホスト名またはIPアドレスを表示しない、あり=表示する<br>';
    print '&emsp;&emsp;&emsp;☆背景図：タイトル名の背景画像の下のサンプルから選択<br>';
    print '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;img1:<img src=haikeiimg/ki.png width=50px height=15px>';
    print '&emsp;img2:<img src=haikeiimg/umi.png width=50px height=15px>';
    print '&emsp;img3:<img src=haikeiimg/aka.png width=50px height=15px>';
    print '&emsp;img4:<img src=haikeiimg/ha.png width=50px height=15px><br>';
    print '&emsp;&emsp;&emsp;☆メール送信先、メール送信元、件名、本文は、「メール設定メニュー」から変更される<br>';
    print '</h3>';
    print '<h3><font color=red>&emsp;&emsp;&emsp;注意：入力文字は英字、数字は半角可能、それ以外はスペースも含め全角。</font></h4>';
    ///
    print '<form name="kanriup" type="get" action="AdminPage.php">';
    print '<table border=1>';
    print '<tr><th>モニタ間隔</th><th>コア間隔</th><th>権限</th><th>管理番号</th><th>ホスト表示</th><th>背景図</th><th>未使用</th><th>追跡ログ</th><th>管理者ID</th></tr>';
    print '<tr>';
    print "<td><input type=text name=monintval size=4 value={$monIntVal}></td>";
    print "<td><input type=text name=snmpintval size=4 value={$snmpIntVal}></td>";
    print "<td><input type=text name=auth size=1 value={$authority} readonly></td>";
    print "<td><input type=text name=kanrino size=4 value={$adminNum} readonly></td>";
    $selOptArr=array('','');
    $selOptArr[intval($adminArr[13])]="selected";
    print '<td><select name="hosthyouji">';
    print "<option value='0'{$selOptArr[0]}>なし</option>";
    print "<option value='1'{$selOptArr[1]}>あり</option>";
    print '</select></td>';
    print '<td><select name="haikei">';
    print "<option value='ki.png'{$selBgPictureArr[0]}>img1</option>";
    print "<option value='umi.png'{$selBgPictureArr[1]}>img2</option>";
    print "<option value='aka.png'{$selBgPictureArr[2]}>img3</option>";
    print "<option value='ha.png'{$selBgPictureArr[3]}>img4</option>";
    print '</select></td>';
    print '<td><select name="bgcolor">';
    print "<option value='' selected>未使用</option>";
    //print "<option value='bgdarks'{$selBgColorArr[0]}>灰色</option>";
    //print "<option value='bgbrown'{$selBgColorArr[1]}>茶色</option>";
    //print "<option value='bgindig'{$selBgColorArr[2]}>青紫色</option>";
    //print "<option value='bgpurpl'{$selBgColorArr[3]}>紫色</option>";
    //print "<option value='bgblack'{$selBgColorArr[3]}>黒色</option>";
    print '</select></td>';
    $selOptArr=array('','','','','','','','');
    $selOptArr[intval($debug)]="selected";
    print '<td><select name=debug>';
    print "<option value='0'{$selOptArr[0]}>なし</option>";
    print "<option value='1'{$selOptArr[1]}>監視ログ</option>";
    print "<option value='5'{$selOptArr[5]}>プロットログ</option>";
    print "<option value='7'{$selOptArr[7]}>処理時間測定</option>";
    print '</select></td>'; 
    print "<td><input type=text name=kanriname value={$user} readonly></td>";
    print '</tr>';
    print '<tr><th colspan=3>送信先</th><th colspan=3>送信元</th><th colspan=3>件名</th></tr>';
    print '<tr>';
    print "<td colspan=3><input type=text name=recv size=22 value={$mailToAddr} readonly></td>";
    print "<td colspan=3><input type=text name=sender size=22 value={$mailFromAddr} readonly></td>";
    print '<td colspan=3><input type=text name=subj size=47 value="'.$subject.'" readonly></td>';
    print '</tr>';
    print '<tr><th colspan=9>本文</th></tr>';
    print '<tr>';
    print '<td colspan=9><input type=text name=body size=103 value="'.$body.'" readonly></td>';
    print '</tr>';
    print '</table>';
    print "<input type=hidden name=user value={$user}>";
    print '<br>&emsp;<input class=button type="submit" name="update" value="更新実行">';
    print '</form>';  
    print "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>";
    print '</body></html>';
  }
}
?>
