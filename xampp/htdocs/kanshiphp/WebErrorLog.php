<?php
require_once "varread.php";
require_once "mysqlkanshi.php";
header("Content-Type:text/html; charset=Shift_JIS");
function branch($_page,$_param){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo "<form name='F' action={$_page} method='get'>";
  echo "<input type=hidden name=param value={$_param}>";
  echo '<input type="submit" name="next" value="Waiting...">';
  echo '</form>';
}
if(isset($_GET['remove'])){
  $user = $_GET['user'];
  $dellogs=$_GET['dellog'];
  $delarr = explode(',',$dellogs);
  $vpath_apache="";
  $vpatharr=array("vpath_apache");
  $rtnv=pathget($vpatharr);
  $vpath_apache=$rtnv[0];  
  foreach($delarr as $delrec){
    unlink($vpath_apache.'\\logs\\'.$delrec);
  }  
  $nextpage="MonitorManager.php";
  branch($nextpage,$user);
  exit;
}elseif(!isset($_GET['param'])){
  echo '<html>';
  echo '<body onLoad="document.F.submit();">';
  echo '<form name="F" action="WebErrorLog.php" method="get">';
  echo '<input type="hidden" name="param" value="">';
  echo '<input type="submit" name="next" style="display:none;" />';
  echo '</form></body></html>';
  echo '<script type="text/javascript">';
  echo 'var keyvalue = sessionStorage.getItem("user");';
  echo 'if (!keyvalue) {';
  echo '  keyvalue = "unknown";';
  echo '}';
  echo 'document.forms["F"].elements["param"].value = keyvalue;';
  echo '</script>';
}else{
  $user=$_GET['param'];
  $usql='select authority from user where userid="'.$user.'"';
  $rows=getdata($usql);
  $udata=explode(',',$rows[0]);
  $auth=$udata[0];
  echo '<html><head><meta>';
  echo '<link rel="stylesheet" href="kanshi1_py.css">';
  echo '</head><body>';
  echo '<h2><img src="header/php.jpg" width="30" height="30">&nbsp;&nbsp;���@Web�G���[���O�@��</h2>';
  $line_num = 20;   /// �\������s��
  echo "<h3>�ŐV {$line_num} �s</h3>";
  $vpath_apache="";
  $vpatharr=array("vpath_apache");
  $rtnv=pathget($vpatharr);
  $now=new DateTime();
  $ymd=$now->format("Ymd");
  $currelog="error_".$ymd.".log";
  $curralog="access_".$ymd.".log";
  if(count($rtnv)==1){
    $vpath_apache=$rtnv[0]; 
    $currpath = $vpath_apache."\\logs\\".$currelog; 
    if (file_exists($currpath)){
      $contents = file($currpath , FILE_IGNORE_NEW_LINES);
      $start_index = count($contents) - $line_num;
      if ( $start_index < 0) {
        $start_index = 0;
      }
      for ( $i=$start_index; $i<count($contents); $i++ ) {
        echo $contents[$i] . '<br />';
      }
      echo "^^^^^^^^^^^^^ �ŏI�s ^^^^^^^^^^^^^";
      echo '<form action="WebErrorLog.php" method="get">';
      echo "<input type='hidden' name='user' value={$uid} >";
      //echo '<input class=button type="submit" name="end" value="�\���I��" />';
      if ($auth=='1'){      
        $result=glob($vpath_apache.'\logs\*_*.log');
        //var_dump($result);
        $dellog="";
        foreach($result as $rec){        
          $filename=basename($rec);
          if (!($filename==$currelog || $filename==$curralog)){
            $erac=explode('_',$filename);
            if ($erac[0]=='error' || $erac[0]=='access'){
              $dellog=$dellog.$filename.',';
            }
          }
        }
        $dellog=rtrim($dellog,",");
        if ($dellog!=""){
          echo "<hr><h3>�폜�o���郍�O</h3><table><tr>";
          $dellogarr=explode(',',$dellog);
          foreach($dellogarr as $arrrec){
            echo "<td>{$arrrec}&emsp;</td>";
          }
          echo '</tr></table>';
          echo "<input type='hidden' name='dellog' value={$dellog} />";
          echo "<input type=hidden name=user value={$user}>";
          echo '&nbsp;&nbsp;<input class=buttondel type="submit" name="remove" value="��L���O�폜" />';
        }
      }
      echo '</form>';    
      echo "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>�Ď����j�^�[�֖߂�</span></a>";
      echo '</body></html>';
    }else{
      echo "<h4>$currpath</h4>";
      echo "<h3>�\�����ׂ���L�t�@�C��������܂���A�}�j���A�����Q�Ƃ��ĉ�����</h3>";
      echo "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>�Ď����j�^�[�֖߂�</span></a>";
      echo '</body></html>';
    }
  }else{
    writeloge($pgm,"variable vpath_apache could not get path");
    $rdsql="select * from admintb";
    $rows=getdata($rdsql);
    $sdata=explode(',',$rows[0]);
    $recv=$sdata[3];
    $sender=$sdata[4];
    $subj="Path�ϐ��s��";
    $body=$pgm."�p�X�ϐ� vpath_apache �擾�s��";
    mailsendany('other',$sender,$recv,$subj,$body);
    echo "&emsp;<h3><font color=red>�ϐ�vpath_php�擾�s�A�Ǘ��҂ɒʒm</font></h3><br>";
    echo "&emsp;<a href='MonitorManager.php?param={$user}'><span class=buttonyell>�Ď����j�^�[�֖߂�</span></a>";
    echo '</body></html>';
  }
}

?>
