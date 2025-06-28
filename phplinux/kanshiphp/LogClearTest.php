<?php
require_once "BaseFunction.php";
require_once "varread.php";
///
function popupMsgSet($dataStr){
  $popArr=explode("//",$dataStr);
  $popupData="";
  $firstSw=0;
  foreach($popArr as $popItem){
    if($firstSw==0){
      $popItem="&lt;p&gt;".$popItem."&lt;/p&gt;&lt;p&gt;&lt;font size=2&gt;";
      $firstSw=1;
    }
    $popupData=$popupData.$popItem;    
  }
  $popupData=$popupData."&lt;/font&gt;&lt;/p&gt;";
  return $popupData;
}


$pgm="LogClear.php";
$user="";
$brcode="";
$brmsg="";
///

///
$webLogDir='e:\visualmonitor\xampp\htdocs\httplogs';
///
$now=new DateTime();
$ymd=$now->format("ymd");


///
///--- log存在チェック -----------
/// Webログ
echo '---before---------<br>'.PHP_EOL;
$fileRows=glob($webLogDir.'\error_*.log');
foreach($fileRows as $fileRowsRec){
  $filename=basename($fileRowsRec);
  echo $filename.'<br>'.PHP_EOL;
   /// end of if
}
$fileRows=glob($webLogDir.'\error_*.log');
foreach($fileRows as $fileRowsRec){
  $filename=basename($fileRowsRec);
  if (false === strpos($filename,$ymd)){
    $rtcd=unlink($webLogDir.'\\'.$filename);
    if($rtcd){
      echo 'file:'.$filename.' Delete success<br>'.PHP_EOL;
    }else{
      echo 'file:'.$filename.' Delete failed<br>'.PGP_EOL;
    }
  } /// end of if    
}  /// end of for
echo '---after----------<br>'.PHP_EOL;
$fileRows=glob($webLogDir.'\error_*.log');
foreach($fileRows as $fileRowsRec){
  $filename=basename($fileRowsRec);
  echo $filename.'<br>'.PHP_EOL;
   /// end of if
}
?>
