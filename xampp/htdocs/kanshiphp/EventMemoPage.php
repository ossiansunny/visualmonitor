<?php
require_once "mysqlkanshi.php";

function branch($_page,$_param){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo "form name='F' action={$_page} method=get'>";
  echo "<input type=hidden name=param value={$_param}>";
  echo '<input type="submit" name="next" value="Waiting...">';
  echo '</form>';
}
function myjoin($data){
  $string = "";
  $i=0;
  foreach ($data as $rec){
    if (is_null($rec)){
      $string = $string . ' ,';
    }else{
      $string = $string . $data[$i] . ',';
    }
    $i++;
  }
  $okstr=rtrim($string.',');
  return $okstr;
}
$user="";
$auth="";

if (!isset($_GET['param'])){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="EventMemoPage.php" method="get">';
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
}else{
  $inform=$_GET['param'];
  if (substr($inform,0,1)=="#"){
    $brarr=explode("#",ltrim($inform,"#"),4);
    $brcode=$brarr[0];
    $user=$brarr[1];
    $brmsg=$brarr[2];
  }else{
    $user=$inform;
  }
  $selsql="select userid,authority from user where userid='".$user."'";
  $udata=getdata($selsql);
  $sdata=$udata[0];
  $tdata=explode(',',$sdata);
  $auth=$tdata[1];
  echo '<html><head><meta>';
  echo '<link rel="stylesheet" href="kanshi1_py.css">';
  echo '</head><body>';
  if ($brcode!=""){
    echo "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  } 
  echo '<h2><img src="header/php.jpg" width="30" height="30">&nbsp;&nbsp;▽　イベントメモ　▽</h2>';
  
  echo '<table>';
  echo '<tr><th >日付:時刻</th><th>ホスト</th><th>管理者</th><th>障害種類</th><th>障害管理番号</th></tr>';
  echo '<tr><th colspan=5>メモメッセージ</th></tr>';

  $sql='select * from eventmemo';
  $rows=getdata($sql);
  echo '<form name="rform" method="get" action="vieweventmemo.php">';
  foreach ($rows as $str){
    $sdata=explode(',',$str);
    $strdata=myjoin($sdata);
    $evtime=$sdata[0];
    $host=$sdata[1];
    $skind=$sdata[2];
    $snum=$sdata[3];
    $memo=$sdata[4];
    echo "<tr><td><input type='checkbox' name='ckdata[]' value={$strdata} >{$evtime}</td>";
    echo "<td width=200> &nbsp;{$host}</td>";
    echo "<td> &nbsp;{$user}</td>";
    echo "<td> &nbsp;{$skind}</td>";
    echo "<td> &nbsp;{$snum}</td></tr>";
    echo "<tr><td colspan=5> &nbsp;&nbsp;&nbsp;&nbsp;{$memo}</td></tr>";
  } 
  echo '</table>';
  if ($auth=='1'){
    echo "<input type=hidden name=user value={$user}>";
    echo '<br><input class=buttondel type="submit" name="delete" value="削除" >';
  }
  echo '</form>';
  echo '<br><br>';
  echo "<a href='MonitorManager.php?param={$value}'><span class=button>監視モニターへ戻る</span></a>"; 
  echo '</body></html>';
}
?>
