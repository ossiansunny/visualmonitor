<?php
require_once "BaseFunction.php";
require_once "mysqlkanshi.php";

function myjoin($_data){
  $string = "";
  $index=0;
  foreach ($_data as $rec){
    if (is_null($rec)){
      $string = $string . ' ,';
    }else{
      $string = $string . $data[$index] . ',';
    }
    $index++;
  }
  $okStr=rtrim($string.',');
  return $okStr;
}
///
$pgm="EventMemoPage.php";
$user="";
$brcode="";
$brmsg="";
$auth="";

if (!isset($_GET['param'])){
  paramGet($pgm);
}else{
  paramSet();
  $user_sql="select authority from user where userid='".$user."'";
  $userRows=getdata($user_sql);
  $userArr=explode(',',$userRows[0]);
  $authority=$userArr[0];
  print '<html><head><meta>';
  print '<link rel="stylesheet" href="css/kanshi1_py.css">';
  print '</head><body>';
  ///
  if ($brcode!=""){
    print "<h3 class={$brcode}>{$brmsg}</h3><hr>";
  } 
  ///
  print '<h2><img src="header/php.jpg" width="30" height="30">&nbsp;&nbsp;▽　イベントメモ　▽</h2>';
  
  print '<table>';
  print '<tr><th >日付:時刻</th><th>ホスト</th><th>管理者</th><th>障害種類</th><th>障害管理番号</th></tr>';
  print '<tr><th colspan=5>メモメッセージ</th></tr>';

  $memo_sql='select * from eventmemo';
  $memoRows=getdata($memo_sql);
  print '<form name="rform" method="get" action="vieweventmemo.php">';
  foreach ($memoRows as $memoRowsRec){
    $memoArr=explode(',',$memoRowsRec);
    $memoStr=myjoin($memoArr);
    $eventTime=$memoArr[0];
    $host=$memoArr[1];
    $closeKind=$memoArr[2];
    $closeNum=$memoArr[3];
    $memo=$memoArr[4];
    print "<tr><td><input type='checkbox' name='ckdata[]' value={$memoStr} >{$eventTime}</td>";
    print "<td width=200> &nbsp;{$host}</td>";
    print "<td> &nbsp;{$user}</td>";
    print "<td> &nbsp;{$closeKind}</td>";
    print "<td> &nbsp;{$closeNum}</td></tr>";
    print "<tr><td colspan=5> &nbsp;&nbsp;&nbsp;&nbsp;{$memo}</td></tr>";
  } 
  print '</table>';
  if ($authority=='1'){
    print "<input type=hidden name=user value={$user}>";
    print '<br><input class=buttondel type="submit" name="delete" value="削除" >';
  }
  print '</form>';
  print '<br><br>';
  print "<a href='MonitorManager.php?param={$value}'><span class=buttonyell>監視モニターへ戻る</span></a>"; 
  print '</body></html>';
}
?>
