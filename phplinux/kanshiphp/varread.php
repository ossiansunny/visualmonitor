<?php
$path_kanshiphp=__DIR__;
$path_base=array();
$path_kanshiphpini="";
/// Windowsのkannshiphpアプリは、...xampp/htdocs/kanshiphpであり htdocsで分けると
/// [0]...xampp と[1]kanshiphpになる、vmsetupはxamppの下にあるので
/// [0]/vmsetup　になる
if (strtoupper(substr(PHP_OS,0,3))==="WIN") {
  /// windows xampp
  //var_dump($path_kanshiphp);
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
      if(! (substr($line,0,2)=="//" or empty(trim($line)))){ //skip comment line
        $item=explode("=",$line);
        $key=trim($item[0]);
        $value=trim($item[1]);
        $value=str_replace("\"","",$value);
        if($arg==$key){
          //echo 'key:'.$key.' value:'.$value.' arg:'.$arg.PHP_EOL;
          //echo 'matched'.PHP_EOL;
          array_push($rtnarr,$value);
          break;
        }
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
echo count($pathfile);
if(count($pathfile)==2){
  var_dump($pathfile);
  echo 'ok';
}else{
  echo 'ng';
}
*/
?>
