<?php
require_once "alarmwindow.php";
///
function branch($_page,$_param){
  print '<html>';
  print '<body onLoad="document.F.submit();">';
  print "<form name='F' action={$_page} method='get'>";
  print '<input type=hidden name=param value="'.$_param.'">';
  print '<input type="submit" name="next" value="Waiting...">';
  print '</form>';
  exit();
}
///
function paramGet($_pgm){
  print '<html>';
  print '<body onLoad="document.F.submit();">';
  print "<form name='F' action={$_pgm} method='get'>";
  print '<input type="hidden" name="param" value="">';
  print '<input type="submit" name="next" style="display:none;" />';
  print '</form></body></html>';
  print '<script type="text/javascript">';
  print 'var keyvalue = sessionStorage.getItem("user");';
  print 'if (!keyvalue) {';
  print '  keyvalue = "unknown";';
  print '}';
  print 'document.forms["F"].elements["param"].value = keyvalue;';
  print '</script>';
}
///
function paramSet(){
  global $brcode, $user, $brmsg;
  $inform=$_GET['param'];
  //var_dump($inform);
  if (substr($inform,0,1)=="#"){
    $branchArr=explode("#",ltrim($inform,"#"),4);
    $brcode=$branchArr[0];
    $user=$branchArr[1];
    $brmsg=$branchArr[2];
  }else{
    $user=$inform;
  }
  if ($user=='unknown'){
    /// Lost Userを赤(2)で表示
    setstatus("2","Lost User");
  }else{
    delstatus("Lost User");
  }
}
///
function alert($_msg){
  $alert = "<script type='text/javascript'>alert('".$_msg."');</script>";
  echo $alert;
}
?>
