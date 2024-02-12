<?php
$path_kanshiphp='/var/www/html/kanshiphp';
$path_kanshiphpini=$path_kanshiphp."/vmsetup/kanshiphp.ini";
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
/// 配列にキー文字列を指定、その順にパスが配列で返る
/// 取得の判定は、配列の要素数で行い、通常は要求した要素数でチェックする
$patharr=array("vpath_mrtghome","vpath_weblog");
$pathfile=pathget($patharr);
if(count($pathfile)==2){
  var_dump($pathfile);
}else{
  var_dump($pathfile);
}
*/
?>
