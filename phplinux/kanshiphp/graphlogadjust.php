<?php
require_once "mysqlkanshi.php";
function adjustlog($logadjust){
  $loginpath=$logadjust;
  $logoutpath=$logadjust.'.bk';
  $loginfile=file($loginpath);
  $logline=count($loginfile);
  $limit=400;
  $c=0;
  $wtfp=fopen($logoutpath,'w');
  $rdfp=fopen($loginpath,'r');
  if ($rdfp){
    while (($line = fgets($rdfp)) !== false){
      $c++;
      if ($c <= $limit){
        //echo 'log num: '.$c. ' '.$line;
        fwrite($wtfp, $line);
      }else{
        break;
      }
    }
    fclose($rdfp);
    fclose($wtfp);
    /// rename from <host>.<type>.log.bk to <host>.<type>.log
  }else{
    $msg="log file: ".$logadjust." open error<br>";
    writeloge($pgm,$msg);
  }
}
//--------------------------
function adjustplog($plogadjust){
  $ploginpath=$plogadjust;
  $plogoutpath=$plogadjust.'.bk';
  $ploginfile=file($ploginpath);
  $logline=count($ploginfile);
  $limit=400;
  if ($logline <= $limit){
    $passlimit=0;
  }else{
    $passlimit=$logline-$limit;
  }
  echo 'plogcount '.$logline.' diff: '.$passlimit.PHP_EOL; 
  $c=0;
  $wtfp=fopen($plogoutpath,'w');
  $rdfp=fopen($ploginpath,'r');
  if ($rdfp){
    while (($line = fgets($rdfp)) !== false){
      $c++;
      if ($c <= $passlimit){
        continue;
      }else{
        //echo 'plog num: '.$c. ' '.$line;
        fwrite($wtfp, $line);
      }
    }
    fclose($rdfp);
    fclose($wtfp);
    /// rename from <host>.<type>.plog.bk to <host>.<type>.plog
    rename($plogoutpath,$ploginpath);
  }else{
    $msg="plog file: ".$plogadjust." open error<br>";
    writeloge($pgm,$msg);
  }
}

function graphlogadjust($mrtgdir){
  foreach(glob($mrtgdir."/mrtgimage/*.*.*.*.*.log") as $tgtFile){
    //echo $tgtFile.PHP_EOL;
    adjustlog($tgtFile);
  }
  foreach(glob($mrtgdir."/mrtgimage/*.*.*.*.*.plog") as $tgtFile){
    //echo $tgtFile.PHP_EOL;
    adjustlog($tgtFile);
  }
}

//graphlogadjust('/var/www/html/mrtg');
?>
