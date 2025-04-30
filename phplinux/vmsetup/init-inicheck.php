<?php
///
/// mrtg/mrtgimage, plot/plotimage およびkanshiphp/mrtgcfg/{cpu|disk|ram}内のデータ削除
///
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
require_once 'init-varread.php';

$pgm = "init_inicheck.php";
$vpath_all=array("vpath_base",
                 "vpath_kanshiphp",
                 "vpath_htdocs",
                 "vpath_ubin",
                 "vpath_weblog",
                 "vpath_ncat",
                 "vpath_phpmailer",
                 "vpath_mrtgbase",
                 "vpath_mrtghome",
                 "vpath_plothome",
                 "vpath_mrtg",
                 "vpath_gnuplot");
$oksw=0;
for($i=0;$i<count($vpath_all);$i++){
  $vpathArr=array($vpath_all[$i]);
  $vpathParam=pathget($vpathArr);
  if(count($vpathParam)==0){
    $basemsg = "init-inicheck.php: vpath パラメータ指定なし";
    switch ($vpath_all[$i]){
      case 'vpath_base';
        echo $basemsg.' vpath_baseは必須です'.PHP_EOL;
        break;
      case 'vpath_kanshiphp';
        echo $basemsg.' vpath_kanshiphpは必須です'.PHP_EOL;
        break;
      case 'vpath_htdocs';
        echo $basemsg.' vpath_htdocsは必須です'.PHP_EOL;
        break;
      case 'vpath_ubin';
        echo $basemsg.' vpath_ubinは必須です'.PHP_EOL;
        $oksw=1;
        break;
      case 'vpath_weblog';
        echo $basemsg.' vpath_weblogは必須です'.PHP_EOL;
        $oksw=1;
        break;
      case 'vpath_ncat';
        echo $basemsg.' vpath_ncatは必須です'.PHP_EOL;
        $oksw=1;
        break;
      case 'vpath_phpmailer';
        echo $basemsg.' vpath_phpmailerはメール機能追加時に必要です'.PHP_EOL;
        $oksw=1;
        break;
      case 'vpath_mrtg';
        echo $basemsg.' vpath_mrtgはグラフ機能追加時に必要です'.PHP_EOL;
        $oksw=1;
        break;
      case 'vpath_mrtghome';
        echo $basemsg.' vpath_mrtghomeはグラフ機能追加時に必要です'.PHP_EOL;
        $oksw=1;
        break;
      case 'vpath_plothome';
        echo $basemsg.' vpath_plothomeはグラフ機能追加時に必要です'.PHP_EOL;
        break;
      case 'vpath_mrtg';
        echo $basemsg.' vpath_mrtgはグラフ機能追加時に必要です'.PHP_EOL;
        $oksw=1;
        break;
      case 'vpath_gnuplot';
        echo $basemsg.' vpath_gnuplotはグラフ機能追加時に必要です'.PHP_EOL;
        $oksw=1;
        break;
    }
  }else{
    if(! file_exists($vpathParam[0])){
      echo ">>>>>>>>>>>>>>> ".$vpath_all[$i]." = ".$vpathParam[0]."　Not found".PHP_EOL;
      $oksw=1;
    }else{
      echo "<<< ".$vpath_all[$i]." = ".$vpathParam[0]." Found".PHP_EOL;
    }
  }
}
//if($oksw==0){
//  echo "init-inicheck.php: kanshiphp.ini は問題ありませんでした".PHP_EOL;
//}
?>
