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
///
$pgm="UserPageU.php";
$brmsg="";
$user="";
$brcode="";
$alerMsg="";
if (!(isset($_GET['param']) or isset($_GET['update']))){
  paramGet($pgm);
  ///
}elseif(isset($_GET['param'])){
  paramSet();
  ///
}elseif(isset($_GET['update'])){
    $user=$_GET['user'];
    
      /// 更新処理      
      $idx=intval($_GET['select']);
      $userId=$_GET['uid'];
      $password=$_GET['upass'];
      $authority=$_GET['auth'];
      $userName=$_GET['uname'];
      $userCode=$_GET['ucode'];
      $bgColor=$_GET['bgcolor'];
      $timeStamp = $_GET['tstamp'];  
      $user_sql="update user set password='".$password."', authority='".$authority."' , username='".$userName."' , usercode='".$userCode."' , timestamp='".$timeStamp."' , bgcolor='".$bgColor."' where userid='".$userId."'";  
      putdata($user_sql);
      $alerMsg="#notic#".$user."#ユーザー".$uid."が更新されました";
      branch($pgm,$alerMsg);
      //exit();
    //} 
}
  
  $value="";
  $title1='<img src="header/php.jpg" width="30" height="30">';
  $title2=' ▽　ユーザー管理　▽   ';
  $title=$title1 . $title2;
  print '<html><head>';
  print '<link rel="stylesheet" href="css/user.css">';
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
  ///
  ///--------------------View Process --------------------
  ///
  $displaysw='';
  $rows='';
  $user_sql='select * from user where userid="'.$user.'"';
  $userRows=getdata($user_sql);
  if (empty($userRows)){
    print '<h4><font color=red>表示すべきデータがありません</font></h4>';
  }else{
    /// 変更データ表示
    print '<h3>「変更」するものを１つ選択して下さい<br>';
    print '変更可能な項目は、パスワード、ユーザー名、背景色です</h3>';
    print '&nbsp;&nbsp;<form method="get" action="UserPageU.php" onsubmit="return check()">';
    print '<table class="nowrap">';
    print '<tr><th >ユーザーID</th><th>パスワード</th><th>権限</th><th>ユーザー名</th><th>コード</th><th>作成変更日</th><th>背景色</th></tr>';
    $cssColor='';
      $userArr=explode(',',$userRows[0]);
      $authority=$userArr[2];
      if ($authority=='0'){
        $cssColor='cwhite';
      }elseif ($authority=='1'){
        $cssColor='cyellow';
      }
      $selAuth=array('','');
      $authName='';
      switch($authority){
        case "0": $selAuth[0]="selected"; $authName="ユーザー";break;
        case "1": $selAuth[1]="selected"; $authName="管理者";break;
      }
      $userId=$userArr[0];
      $password=$userArr[1];
      $userName=$userArr[3];
      $userCode=$userArr[4];
      $timeStamp=$userArr[5];
      $bgColor=$userArr[6];
      $selBgColorArr=array('','','','','');
      switch($bgColor){
        case "bgdarks": $selBgColorArr[0]="selected"; break;
        case "bgbrown": $selBgColorArr[1]="selected"; break;
        case "bgindig": $selBgColorArr[2]="selected"; break;
        case "bgpurpl": $selBgColorArr[3]="selected"; break;
        case "bgblack": $selBgColorArr[4]="selected"; break;
      }
      print '<tr>';
      print "<td ><input class='cyellow' type=text name=uid value={$userId} size=9 readonly></td>";
      print "<td ><input class={$cssColor} type=text name=upass value={$password} size=9 ></td>";
      print "<td ><input class='cyellow' type=text name=auth value={$authName} size=10 readonly></td>";
      print "<td ><input class={$cssColor} type=text name=uname value={$userName} size=19 ></td>";
      print "<td ><input class='cyellow' type=text name=ucode value={$userCode} size=4 readonly></td>";
      print "<td ><input class='cyellow' type=text name=tstamp value={$timeStamp} size=12 readonly></td>";
      print '<td><select class={$cssColor} name="bgcolor">';
      print "<option value='bgdarks'{$selBgColorArr[0]}>灰色</option>";
      print "<option value='bgbrown'{$selBgColorArr[1]}>茶色</option>";
      print "<option value='bgindig'{$selBgColorArr[2]}>青紫色</option>";
      print "<option value='bgpurpl'{$selBgColorArr[3]}>紫色</option>";
      print "<option value='bgblack'{$selBgColorArr[4]}>黒色</option>";
      print '</select></td>';
      print '</tr>';
      ///end of for
    print '</table>';
    print "<input type=hidden name=user value={$user}>";
    print '<br><input class=button type="submit" name="update" value="更新実行" >';
    print '</form>';
  }
  /// 
  print '<hr>';
  print '<br><br>';
  print "<a href='MonitorManager.php?param={$user}'><span class=buttonyell>監視モニターへ戻る</span></a>"; 
  print '</body></html>';

?>

