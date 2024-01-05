<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";
require_once "mailsendany.php";

function nullcheck($data){
  if (isset($data)){
    return $data;
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
  if (isset($_GET['update'])){
    $user=$_GET['user'];
    $auth=$_GET['auth'];
    $recv=$_GET['recv'];
    $sender=$_GET['sender'];
    $subj=$_GET['subj'];
    $body=$_GET['body'];
    $monintval=$_GET['monintval'];
    $snmpintval=$_GET['snmpintval'];
    $debug=$_GET['debug'];
    $kno=$_GET['kanrino'];
    $hosthyouji=$_GET['hosthyouji'];
    $haikei=$_GET['haikei'];
    $upsql="update admintb set 
         authority='".$auth."',
         receiver='".$recv."',
         sender='".$sender."',
         subject='".$subj."',
         body='".$body."',
         monintval='".$monintval."',
         snmpintval='".$snmpintval."',
         debug='".$debug."',
         kanrino='" .$kno. "',
         hosthyouji='".$hosthyouji."',
         haikei='".$haikei."'";
    ///
    $uprc = putdata($upsql); 
    $upsqlmsg=str_replace('<','&lt;',$upsql);
    $upsqlmsg=str_replace('>','&gt;',$upsqlmsg);
    /// 
    if($uprc == 0){    
      $msg='AdminTB Updated sql: '.$upsqlmsg;
      writelogd($pgm,$msg);
      $message='Update admintb';
      mailsendany('adminsubject',$sender,$recv,$subj,$message);
    }else{
      $msg='AdminTB Not updated sql: '.$upsqlmsg; 
      writeloge($pgm,$msg);   
    }
    $nextpage="MonitorManager.php";
    branch($nextpage,$user);
    exit;
  ///　画面表示処理
  }else{
    $user=$_GET['param'];
    $rdsql="select * from admintb";
    $rows=getdata($rdsql);
    $sdata=explode(',',$rows[0]);
    $auth=$sdata[2];
    $recv=$sdata[3];
    $sender=$sdata[4];
    $subj=nullcheck($sdata[5]);
    $body=nullcheck($sdata[6]);
    $monintval=strval($sdata[7]);
    $snmpintval=strval($sdata[8]);
    $debug=$sdata[9];
    $kno=$sdata[10];
    $selh0='';
    $selh1='';
    if ($sdata[13]=='0'){
      $selh0='selected';
      $selh1='';
    }else{
      $selh0='';
      $selh1='selected';
    }
    $haikei=$sdata[14];
    $seld=array('','','','');
    if ($haikei == 'ki.png'){
      $seld[0]='selected';
    }elseif ($haikei == 'umi.png'){
      $seld[1]='selected';
    }elseif ($haikei == 'aka.png'){
      $seld[2]='selected';
    }elseif ($haikei == 'ha.png'){
      $seld[3]='selected';
    }else{
      $seld[0]='selected';
    }
    print '<html><head><meta>';
    print '<link rel="stylesheet" href="kanshi1.css">';
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
    ///
    print '<form name="kanriup" type="get" action="AdminPage.php">';
    print '<table border=1>';
    print '<tr><th>監視間隔</th><th>SNMP間隔</th><th>権限</th><th>管理番号</th><th>ホスト表示</th><th>背景図</th><th>追跡ログ</th><th>管理者ID</th></tr>';
    print '<tr>';
    print "<td><input type=text name=monintval size=4 value={$monintval}></td>";
    print "<td><input type=text name=snmpintval size=4 value={$snmpintval}></td>";
    print "<td><input type=text name=auth size=1 value={$auth} readonly></td>";
    print "<td><input type=text name=kanrino size=4 value={$kno} readonly></td>";
    print '<td><select name="hosthyouji">';
    print "<option value='0'{$selh0}>なし</option>";
    print "<option value='1'{$selh1}>あり</option>";
    print '</select></td>';
    print '<td><select name="haikei">';
    print "<option value='ki.png'{$seld[0]}>img1</option>";
    print "<option value='umi.png'{$seld[1]}>img2</option>";
    print "<option value='aka.png'{$seld[2]}>img3</option>";
    print "<option value='ha.png'{$seld[3]}>img4</option>";
    print '</select></td>';
    $ot=array('','','','','','');
    $ot[intval($debug)]="selected";
    print '<td><select name=debug>';
    print "<option value='0'{$ot[0]}>なし</option>";
    print "<option value='1'{$ot[1]}>全ﾄﾚｰｽ</option>";
    //print "<option value='2'{$ot[2]}>DBﾄﾚｰｽ</option>";
    //print "<option value='3'{$ot[3]}>ﾓﾆﾀｰ</option>";
    //print "<option value='4'{$ot[4]}>SNMP</option>";
    print "<option value='5'{$ot[5]}>PLOT</option>";
    print '</select></td>'; 
    print "<td><input type=text name=kanriname value={$user} readonly></td>";
    print '</tr>';
    print '<tr><th colspan=2>送信先</th><th colspan=2>送信元</th><th colspan=5>件名</th></tr>';
    print '<tr>';
    print "<td colspan=2><input type=text name=recv size=22 value={$recv}></td>";
    print "<td colspan=2><input type=text name=sender size=22 value={$sender}></td>";
    print "<td colspan=6><input type=text name=subj size=39 value={$subj}></td>";
    print '</tr>';
    print '<tr><th colspan=9>本文</th></tr>';
    print '<tr>';
    print "<td colspan=9><input type=text name=body size=95 value={$body}></td>";
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
