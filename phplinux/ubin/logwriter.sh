pgm=$1
logname='plot'
echo $pgm | grep core > /dev/null
if [ $? -eq 0 ]
then
  logname='core'
fi
msg=$2
htdocs=$3
#--------mysql select debug from admintb ----------
debg=`MYSQL_PWD=kanshipass mysql -h localhost -ukanshiadmin kanshi << EOT
select debug from admintb;
EOT`
#echo $debg
val=`echo $debg |awk '{print $2}'` 
#---------- $debg is debug value ----------------
if [ $val -eq '5' ]
then
  dte=`date +'%y%m%d'`
  file="${logname}_$dte.log"
  if [ ! -f ${htdocs}/plot/logs/$file ]
  then
    touch ${htdocs}/plot/logs/$file
  fi
  stamp=`date +'%y%m%d%H%M%S'`
#  echo "$pgm: $stamp $msg" 
  echo "$pgm: $stamp $msg" >> ${htdocs}/plot/logs/$file
fi
