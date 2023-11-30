<?php
$path_kanshiphp=__DIR__;
$path_kanshiphpini=$path_kanshiphp."\\vmsetup\\kanshiphp.ini";
function pathget($path){
  global $path_kanshiphpini;
  $rtnarr=array();
  foreach($path as $arg){
    $fp = fopen($path_kanshiphpini,"r");
    while ($line = fgets($fp)) {
      $item=explode("=",$line);
      $key=trim($item[0]);
      $value=trim($item[1]);
      $value=str_replace('"','',$value);
      if($arg==$key){
        array_push($rtnarr,$value);
        break;
      }
    }
    fclose($fp);
  }  
  return $rtnarr;
}
/*
/// Debug
/// 配列にキー文字列を指定、その順にパスが配列で返る
/// 取得の判定は、配列の要素数で行い、通常は要求した要素数でチェックする
$patharr=array("vpath_kanshiphp","vpath_xampp");
$pathfile=pathget($patharr);
if(count($pathfile)==2){
  echo '<br>\r\nreturn ok:'.$pathfile[0]." ".$pathfile[1].PHP_EOL;
}else{
  var_dump($pathfile);
}
*/
?>
