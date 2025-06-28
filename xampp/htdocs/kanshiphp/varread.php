<?php
$path_kanshiphp=__DIR__;
$path_base=array();
$path_kanshiphpini="";
/// Windowsのkannshiphpアプリは、...xampp/htdocs/kanshiphpであり htdocsで分けると
/// [0]...xampp と[1]kanshiphpになる、vmsetupはxamppの下にあるので
/// [0]/vmsetup　になる
if (strtoupper(substr(PHP_OS,0,3))==="WIN") {
  /// windows xampp
  $path_base=explode("\htdocs",$path_kanshiphp);
  $path_kanshiphpini=$path_base[0]."\\vmsetup\\kanshiphp.ini"; 
  
}else{
/// unix linuxのkanshiphpアプリは、...html/kanshiphpであり、 kanshiphpで分ければ
/// [0]htmlになり、vmsetupはhtmlの下にあるので、[0]/vmsetupとなる 
  $path_base=explode("/kanshiphp",$path_kanshiphp);
  $path_kanshiphpini=$path_base[0]."/vmsetup/kanshiphp.ini";
}
///
/// vmsetup内のinit-varread.phpとは相違するのでコピーして使えない
///
function pathget($path){
  global $path_kanshiphpini;
  $rtnarr=array();
  foreach($path as $arg){
    $fp = fopen($path_kanshiphpini,"r");
    while ($line = fgets($fp)) {
      $trimline=trim($line);
      if(! (substr($line,0,2)=="//" ) or (empty($trimline)) ){ 
        ///skip comment and empty line
        $item=explode("=",$line);
        $key=trim($item[0]);
        $value=trim($item[1]);
        $value=str_replace("\"","",$value);
        if($arg==$key){
          array_push($rtnarr,$value);
          break;
        }
      }
    }
    fclose($fp);
  }
  return $rtnarr;
}

?>
