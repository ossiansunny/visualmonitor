<?php
date_default_timezone_set('Asia/Tokyo');
error_reporting(E_ALL & ~E_NOTICE);
require_once "mysqlkanshi.php";

//-----------------------------------
//---- 各種保存情報取得
//------------------------------------
function getmailstatus(){
  $sql="select * from status";
  $rows=getdata($sql);
  $row=explode(',',$rows[0]);
  $seq=intval($row[0]);
  $key="";
  $val="";
  $rtncd=1;
  $result=array(" "," ");
  $cnt=1;
  while ($cnt<=5){
    $seq++; // 2
    if ($seq>5){
      $seq=1;
    }
    $key=$row[$seq+($seq-1)];
    $val=$row[$seq+($seq-1)+1];
    if (empty($val) || $val==" " || is_null($val)){
      continue;
    }else{
      if ($val=='Mail Server Active'){
        $rtncd=0; // found
        break;
      }
    }
    $cnt++;
  } 
  return $rtncd;
}

//-----------------------------------
//---- get next status message
//------------------------------------
function getstatus(){
  $sql="select * from status";
  $rows=getdata($sql);
  $row=explode(',',$rows[0]);
  $seq=$row[0];
  $key="";
  $val="";
  $nosw=0;
  $result=array(" "," ");
  $cnt=1;
  while ($cnt<=5){
    $seq++;
    if ($seq>5){
      $seq=1;
    }
    $key=$row[$seq+($seq-1)];
    $val=$row[$seq+($seq-1)+1];
    if (empty($val) || $val==" " || is_null($val)){
      $cnt++;
      continue;
    }else{
      $nosw=1; // found
      break;
    }
    
  } 
  if ($nosw!=0){ // not found
    $sql="update status set pointer=".$seq;
    putdata($sql);
    $result[0]=$key;
    $result[1]=$val;
  }
  return $result;
}
  /*
  // 0   1    2    3    4    5    6    7    8    9    10 
  // seq key1 val1 key2 val2 key3 val3 key4 val4 key5 val5
 
  */
/// 各種情報保存
function setstatus($key,$val){
  $nosw=1;
  $sql="select * from status";
  $rows=getdata($sql);
  $row=explode(',',$rows[0]);
  $ptr=$row[0];
  for($cnt=1;$cnt<10;$cnt=$cnt+2){
    $mark=$row[$cnt];
    $msg=$row[$cnt+1];
    if(empty($msg) || is_null($msg) || $msg==" "){
      $sql="update status set mark".strval($cnt)."='".$key."',msg".strval($cnt)."='".$val."'";
      putdata($sql);      
      $nosw=0;
      break;
    }else if($msg==$val){
      $nosw=2;
      break;
    }
  }
  return $nosw; // 保存完了=0 , 保存不可=1, 既保存=2
}

/// 各種情報削除
function delstatus($val){
  $nosw=1;
  $sql="select * from status";
  $rows=getdata($sql);
  $row=explode(',',$rows[0]);
  for($cnt=1;$cnt<6;$cnt++){
    $msg=$row[$cnt+($cnt-1)+1];
    if($msg==$val){
      $sql="update status set mark".strval($cnt)."='',msg".strval($cnt)."=''";
      putdata($sql);      
      $nosw=0;
      break;
    }
  }
  return $nosw; // deleted=0 , not found=1
}
/// デバッグ
/*
delstatus('Mail Server InActive');
delstatus('Mail Server Active');
setstatus("1","Mail Server InActive");
*/
/*
$msg="Logoff Now";
$rc=delstatus($msg);
if($rc==0){
  echo "\n".$msg." deleted";
}else{
  echo "\n".$msg." not found";
}
*/
/*
$flg=getmailstatus();
echo "\n";
var_dump($flg);
if($flg==0){
  echo "\nFound";
}else{
  echo "\nNot found";
}
*/
/*
$rows=getstatus();
echo "\n";
var_dump($rows);
$row=explode(',',$rows[0]);
if(empty($row[0])){
  echo "\nnot found";
}else{
  echo "\n".$row[0]." ".$row[1];
}
 
$rc=setstatus("d","msg4d");
if ($rc==1){
  echo "\nno saved";
}else if($rc==2){
  echo "\nalready saved";
}else{ 
  echo "\nsaved";
}
*/
?>
