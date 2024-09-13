<?php
$path_kanshiphp=__DIR__;
$path_kanshiphpini=$path_kanshiphp."\\vmsetup\\kanshiphp.ini";
///
function pathget($vpathParam){
  global $path_kanshiphpini;
  $rtnVpath=array();
  foreach($vpathParam as $vpath){
    $fp = fopen($path_kanshiphpini,"r");
    while ($line = fgets($fp)) {
      $item=explode("=",$line);
      $key=trim($item[0]);
      $value=trim($item[1]);
      $value=str_replace('"','',$value);
      if($vpath==$key){
        array_push($rtnVpath,$value);
        break;
      }
    }
    fclose($fp);
  }  
  return $rtnVpath;
}
/*
/// 配列にキー文字列を指定、その順にパスが配列で返る
/// 取得の判定は、配列の要素数で行い、通常は要求した要素数でチェックする
$pathParam=array("vpath_mrtgbase");
$rtnPath=pathget($pathParam);
if(count($rtnPath)==1){
  print '<br>\r\nreturn ok:'.$rtnPath[0].PHP_EOL;
}else{
  var_dump($rtnPath);
}
*/
?>

