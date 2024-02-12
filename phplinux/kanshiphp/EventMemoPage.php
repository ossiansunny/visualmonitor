<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

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
$pgm="EventMemoPage.php";
$user="";
$brcode="";
$brmsg="";
$auth="";

if (!isset($_GET['param'])){
  paramGet($pgm);
}else{
  paramSet();
  $selsql="select userid,authority from user where userid='".$user."'";
  $udata=getdata($selsql);
  $sdata=$udata[0];
  $tdata=explode(',',$sdata);
  $auth=$tdata[1];
  print '<html><head><meta>';
  print '<link rel="stylesheet" href="kanshi1_py.css">';
  print '</head><body>';
  ///
  if ($brcode!=""){
    print '<h3 class="'.$brcode.'">"'.$brmsg.'"</h3><hr>';
    //print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  } 
  ///
  print '<h2><img src="header/php.jpg" width="30" height="30">&nbsp;&nbsp;▽　イベントメモ　▽</h2>';
  
  print '<table>';
  print '<tr><th >日付:時刻</th><th>ホスト</th><th>管理者</th><th>障害種類</th><th>障害管理番号</th></tr>';
  print '<tr><th colspan=5>メモメッセージ</th></tr>';

  $sql='select * from eventmemo';
  $rows=getdata($sql);
  print '<form name="rform" method="get" action="vieweventmemo.php">';
  foreach ($rows as $str){
    $sdata=explode(',',$str);
    $strdata=myjoin($sdata);
    $evtime=$sdata[0];
    $host=$sdata[1];
    $skind=$sdata[2];
    $snum=$sdata[3];
    $memo=$sdata[4];
    print "<tr><td><input type='checkbox' name='ckdata[]' value={$strdata} >{$evtime}</td>";
    print "<td width=200> &nbsp;{$host}</td>";
    print "<td> &nbsp;{$user}</td>";
    print "<td> &nbsp;{$skind}</td>";
    print "<td> &nbsp;{$snum}</td></tr>";
    print '<tr><td colspan=5> &nbsp;&nbsp;&nbsp;&nbsp;"'.$memo.'"</td></tr>';
    //print "<tr><td colspan=5> &nbsp;&nbsp;&nbsp;&nbsp;{$memo}</td></tr>";
  } 
  print '</table>';
  if ($auth=='1'){
    print "<input type=hidden name=user value={$user}>";
    print '<br><input class=buttondel type="submit" name="delete" value="削除" >';
  }
  print '</form>';
  print '<br><br>';
  print "<a href='MonitorManager.php?param={$value}'><span class=buttonyell>監視モニターへ戻る</span></a>"; 
  print '</body></html>';
}
?>
