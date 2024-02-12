pgm=$1
msg=$2
htdocs=$3
#--------mysql select debug from admintb ----------
debg=`mysql -ukanshiadmin -pkanshipass kanshi << EOT
select debug from admintb;
EOT`
key2=`echo $debg |awk '{print $1}'`
value2=`echo $debg |awk '{print $2}'`
#echo $key2: $value2
#---------- $value2 is debug value ----------------
if [ $value2 -eq '5' ]
then
  dte=`date +'%y%m%d'`
  file="plot_$dte.log"
  if [ ! -f ${htdocs}/plot/logs/$file ]
  then
    touch ${htdocs}/plot/logs/$file
  fi
  stamp=`date +'%y%m%d%H%M%S'`
  echo "$pgm: $stamp $msg" >> ${htdocs}/plot/logs/$file
fi
