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
?>
