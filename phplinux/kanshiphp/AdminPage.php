<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "mailsendany.php";

function nullcheck($_data){
  if (isset($_data)){
    return $_data;
  }else{
    return ' ';
  }
} 

///
$pgm="AdminPage.php";

if (!(isset($_GET['param']) or isset($_GET['update']))){  
  ///　セッション保存のユーザ取得
  paramGet($pgm);
}else{
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
    $admin_sql="update admintb set 
         authority='".$authority."',
         receiver='".$mailToAddr."',
         sender='".$mailFromAddr."',
         subject='".$subject."',
         body='".$body."',
         monintval='".$monIntVal."',
         snmpintval='".$snmpIntVal."',
         debug='".$debug."',
         kanrino='" .$adminNum. "',
         hosthyouji='".$hostViewEnable."',
         haikei='".$bgPicture."'";
    ///
    putdata($admin_sql); 
    $okSqlMsg=str_replace('<','&lt;',$admin_ql);
    $okSqlMsg=str_replace('>','&gt;',$okSqlMsg);
    /// 
    if($uprc == 0){    
      $msg='AdminTB Updated sql: '.$okSqlMsg;
      writelogd($pgm,$msg);
      $event_sql = "insert into eventlog (host,eventtime,eventtype,kanrisha) values('".$user."','".$timeStamp."','3','".$user."')";
      putdata($event_sql);
      $message='Update admintb';
      mailsendany('adminsubject',$mailFromAddr,$mailToAddr,$subject,$message);
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
    print '<html><head><meta>';
    print '<link rel="stylesheet" href="css/kanshi1.css">';
    print '</head><body>';
    print '<h2><img src="header/php.jpg" width="30" height="30">&emsp;&emsp;▽　管理情報　▽</h2>';
    ///
    print '<h4>&emsp;&emsp;&emsp;☆監視間隔：表示間隔(Asec)<br>';
    print '&emsp;&emsp;&emsp;☆SNMP間隔：SNMPポーリング間隔(Asec/2の乗数)<br>';
    print '&emsp;&emsp;&emsp;☆権限：1 管理者操作中　変更不可<br>';        
    print '&emsp;&emsp;&emsp;☆管理番号:管理者 1xxxの下三桁、ユーザー 2xxxの下三桁<br>';
    print '&emsp;&emsp;&emsp;☆ホスト表示：なし=表示名の上部にホスト名表示・IPアドレスを表示しない、あり=表示する<br>';
    print '&emsp;&emsp;&emsp;☆背景図：タイトル、グループ名の背景画像の選択<br>';
    print '&emsp;&emsp;&emsp;☆件名：メールの件名(&lt;title&gt;タイトル、&lt;host&gt;ホスト名、&lt;status&gt;状態を挿入)、未入力は標準の件名<br>';
    print '&emsp;&emsp;&emsp;☆本文：メール本文の最後に付与する、message:固定文（途中改行はn）<br>';
    print '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;img1:<img src=haikeiimg/ki.png width=50px heigh=15px>&emsp;img2:<img src=haikeiimg/umi.png width=50px heigh=15px>';
    print '&emsp;img3:<img src=haikeiimg/aka.png width=50px heigh=15px>&emsp;img4:<img src=haikeiimg/ha.png width=50px heigh=15px>';
    print '</h4>';
    print '<h3><font color=red>&emsp;&emsp;&emsp;注意：入力文字は英字、数字は半角可能、それ以外は全角です。スペースも全角です。</font></h3>';
    ///
    print '<form name="kanriup" type="get" action="AdminPage.php">';
    print '<table border=1>';
    print '<tr><th>監視間隔</th><th>SNMP間隔</th><th>権限</th><th>管理番号</th><th>ホスト表示</th><th>背景図</th><th>追跡ログ</th><th>管理者ID</th></tr>';
    print '<tr>';
    print "<td><input type=text name=monintval size=4 value={$monIntVal}></td>";
    print "<td><input type=text name=snmpintval size=4 value={$snmpIntVal}></td>";
    print "<td><input type=text name=auth size=1 value={$authority} readonly></td>";
    print "<td><input type=text name=kanrino size=4 value={$adminNum} readonly></td>";
    print '<td><select name="hosthyouji">';
    print "<option value='0'{$selHostView0}>なし</option>";
    print "<option value='1'{$selHostView1}>あり</option>";
    print '</select></td>';
    print '<td><select name="haikei">';
    print "<option value='ki.png'{$selBgPictureArr[0]}>img1</option>";
    print "<option value='umi.png'{$selBgPictureArr[1]}>img2</option>";
    print "<option value='aka.png'{$selBgPictureArr[2]}>img3</option>";
    print "<option value='ha.png'{$selBgPictureArr[3]}>img4</option>";
    print '</select></td>';
    $selOptArr=array('','','','','','');
    $selOptArr[intval($debug)]="selected";
    print '<td><select name=debug>';
    print "<option value='0'{$selOptArr[0]}>なし</option>";
    print "<option value='1'{$selOptArr[1]}>全ﾄﾚｰｽ</option>";
    //print "<option value='2'{$selOptArr[2]}>DBﾄﾚｰｽ</option>";
    //print "<option value='3'{$selOptArr[3]}>ﾓﾆﾀｰ</option>";
    //print "<option value='4'{$selOptArr[4]}>SNMP</option>";
    print "<option value='5'{$selOptArr[5]}>PLOT</option>";
    print '</select></td>'; 
    print "<td><input type=text name=kanriname value={$user} readonly></td>";
    print '</tr>';
    print '<tr><th colspan=2>送信先</th><th colspan=2>送信元</th><th colspan=5>件名</th></tr>';
    print '<tr>';
    print "<td colspan=2><input type=text name=recv size=22 value={$mailToAddr}></td>";
    print "<td colspan=2><input type=text name=sender size=22 value={$mailFromAddr}></td>";
    print '<td colspan=6><input type=text name=subj size=39 value="'.$subject.'"></td>';
    print '</tr>';
    print '<tr><th colspan=9>本文</th></tr>';
    print '<tr>';
    print '<td colspan=9><input type=text name=body size=95 value="'.$body.'"></td>';
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
