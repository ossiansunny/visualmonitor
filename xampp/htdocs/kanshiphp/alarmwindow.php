<?php
date_default_timezone_set('Asia/Tokyo');
error_reporting(E_ALL & ~E_NOTICE);
require_once "mysqlkanshi.php";

//-----------------------------------
//---- �e��ۑ����擾
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
function getstatus($ptr = 6){
  /// $ptr: mark1 mark2 mark3 �́@1 2 3���w��A0 < $ptr > 6
  $result=array();  
  $ssw=0;
  if ($ptr==6){
    $ssw=1;    
  }else{
    if($ptr<1 or $ptr>5){
      array_puch($result,'2','Pointer Invalid');
      return $result;
    }
  } 
  $cptr=1;
  $sql="select * from status";
  $rows=getdata($sql);
  $row=explode(',',$rows[0]);
  $key="";
  $val="";
  if ($ssw==0){
    $cptr=$ptr;
    $markp=($ptr*2)-1;  /// �w��|�C���^�[
  }else{
    $cptr=$row[0];
    $markp=($row[0]*2)-1;     /// ���R�[�h���|�C���^�[
    //print 'markp:'.$markp;
  }
  $cnt=1;
  while ($cnt<=5){
    $keyp=($cnt*2)-1;
    $valp=$cnt*2; 
    $key=$row[$keyp];
    $val=$row[$valp];
    //print 'mark:'.$key.' msg:'.$val."\r\n";
    if ($keyp==$markp){ 
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
  ptrstatus($cptr+1);
  return $result;
}

Function ptrstatus($ptr){
  /// $ptr: mark1 mark2 mark3 �́@1 2 3���w��A0 < $ptr > 
  if($ptr<1 or $ptr>5){
    $ptr=1;
  } 
  $sql="update status set pointer=".$ptr;
  putdata($sql);
  return 0;
}
  /*
  // 0   1    2    3    4    5    6    7    8    9    10 
  // seq key1 val1 key2 val2 key3 val3 key4 val4 key5 val5
 
  */
/// �e����ۑ�
function setstatus($key,$val){
  /// key ..�\���F 
  /// 0: Green  1:Yellow  2:Red
  /// 
  $nosw=1;
  $sql="select * from status";
  $rows=getdata($sql);
  $row=explode(',',$rows[0]);
  //$ptr=$row[0];
  $cnt=1;
  while($cnt<=5){
    $keyp=($cnt*2)-1; // key position
    $valp=$cnt*2;     // msg position
    $mark=$row[$keyp];
    $msg=$row[$valp];
    //print 'cnt:'.strval($cnt).' keyp:'.strval($keyp).' valp:'.strval($valp)."\r\n";
    //print 'mark:'.$mark.' msg:'.$msg."\r\n"; 
    if ($msg==$val){
      $nosw=2;
      break;
    }elseif(empty($msg) or is_null($msg) or $msg==" "){
      /// mark#,msg# �� cnt�Ɠ���
      $sql="update status set mark".strval($cnt)."='".$key."',msg".strval($cnt)."='".$val."'";
      putdata($sql);      
      $nosw=0;
      break;
    }
    $cnt++;    
  }
  return $nosw; // �ۑ�����=0 , �ۑ��s��=1, ���ۑ�=2
}

/// �e����폜
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
?>
