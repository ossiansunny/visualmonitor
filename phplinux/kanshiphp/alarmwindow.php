<?php
date_default_timezone_set('Asia/Tokyo');
error_reporting(E_ALL & ~E_NOTICE);
require_once "mysqlkanshi.php";

///-----------------------------------
///---- 各種保存情報取得
///------------------------------------
function getmailstatus(){
  $stat_sql="select * from status";
  $statRows=getdata($stat_sql);
  $statArr=explode(',',$statRows[0]);
  $pointer=intval($statArr[0]);
  $key="";
  $val="";
  $rtncd=1;
  $result=array(" "," ");
  $cnt=1;
  while ($cnt<=5){
    $pointer++; /// 2
    if ($pointer>5){
      $pointer=1;
    }
    $key=$statArr[$pointer+($pointer-1)];
    $val=$statArr[$pointer+($pointer-1)+1];
    if (empty($val) or $val==" " or is_null($val)){
      $cnt++;
      continue;
    }else{
      if ($val=='Mail Server Active'){
        $rtncd=0; /// found
        break;
      }
    }
    $cnt++;
  } 
  return $rtncd;
}

///-----------------------------------
///---- get next status message
///------------------------------------
function getstatus($_poniter = 6){
  /// $_poniter: mark1 mark2 mark3 の　1 2 3を指定、0 < $_poniter > 6
  $result=array();  
  if($_poniter<1 or $_poniter>6){
    array_puch($result,'2','Pointer Invalid');
    return $result;
  }
  $currentPtr=1;
  $stat_sql="select * from status";
  $statRows=getdata($stat_sql);
  $statArr=explode(',',$statRows[0]);
  $key="";
  $val="";
  if ($_poniter==6) {
    $currentPtr=$statArr[0];
    $markPtr=($statArr[0]*2)-1;     /// レコード内ポインター
  }else{
    $currentPtr=$_poniter;
    $markPtr=($_poniter*2)-1;  /// 指定ポインター
  }
  $cnt=1;
  while ($cnt<=5){
    $keyPtr=($cnt*2)-1;
    $valPtr=$cnt*2; 
    $key=$statArr[$keyPtr];
    $val=$statArr[$valPtr];
    if ($keyPtr==$markPtr){ 
      if (empty($val) || $val==" " || is_null($val)){
        array_push($result,"","");
        break;
      }else{
        array_push($result,$key,$val);
        break;
      }
    }
    $cnt++;
  } 
  ptrstatus($currentPtr+1);
  return $result;
}

Function ptrstatus($_poniter){
  /// $_poniter: mark1 mark2 mark3 の　1 2 3を指定、0 < $_poniter > 
  if($_poniter<1 or $_poniter>5){
    $_poniter=1;
  } 
  $stat_sql="update status set pointer=".$_poniter;
  putdata($stat_sql);
  return 0;
}
  
  /// 0       1    2    3    4    5    6    7    8    9    10 
  /// pointer key1 val1 key2 val2 key3 val3 key4 val4 key5 val5
 
  
/// 各種情報保存
function setstatus($_key,$_val){
  /// key ..表示色 
  /// 0: Green  1:Yellow  2:Red
  /// 
  $rtnCde=1;
  $stat_sql="select * from status";
  $statRows=getdata($stat_sql);
  $statArr=explode(',',$statRows[0]);
  $cnt=1;
  while($cnt<=5){
    $_keyPtr=($cnt*2)-1; /// key position
    $_valPtr=$cnt*2;     /// msg position
    $mark=$statArr[$_keyPtr];
    $msg=$statArr[$_valPtr];
    if ($msg==$_val){
      $rtnCde=2;
      break;
    }elseif(empty($msg) or is_null($msg) or $msg==" "){
      /// mark#,msg# は cntと同じ
      $stat_sql="update status set mark".strval($cnt)."='".$_key."',msg".strval($cnt)."='".$_val."'";
      putdata($stat_sql);      
      //echo $stat_sql.'<br>'.PHP_EOL;
      $rtnCde=0;
      break;
    }
    $cnt++;    
  }
  return $rtnCde; /// 保存完了=0 , 保存不可=1, 既保存=2
}

/// 各種情報削除
function delstatus($_val){
  $rtnCde=1;
  $stat_sql="select * from status";
  $statRows=getdata($stat_sql);
  $statArr=explode(',',$statRows[0]);
  for($cnt=1;$cnt<6;$cnt++){
    $msg=$statArr[$cnt+($cnt-1)+1];
    if($msg==$_val){
      $stat_sql="update status set mark".strval($cnt)."='',msg".strval($cnt)."=''";
      putdata($stat_sql);      
      $rtnCde=0;
      break;
    }
  }
  return $rtnCde; /// deleted=0 , not found=1
}
/*
$rtncde=getmailstatus();
var_dump($rtncde);
*/
?>
