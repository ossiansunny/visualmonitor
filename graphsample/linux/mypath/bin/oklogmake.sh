hpath=$1
ghost=$2
gtype=$3
gline=$4
mpath=${hpath}/mrtg
ipath=${hpath}/plot
head -$gline ${mpath}/${ghost}.${gtype}.log | sort -k 1 -t " " | awk '{print $1,$2}' > ${ipath}/temptype
oldhour=0
hour=0
maxval=0
fsw=0
rm -f ${ipath}/gethour.log
while read unixtme load
do
  hour=`date -d @${unixtme} +"%H"`
  if [ $fsw -eq 0 ]
  then
    fsw=1
    if [ $maxval -lt $load ]
    then
      maxval=$load
    fi
    oldhour=$hour
  else
    if [ $oldhour -ne $hour ]
    then
      echo "$oldhour $maxval" >> ${ipath}/gethour.log 
      maxval=0  
      if [ $maxval -lt $load ]
      then
        maxval=$load
      fi
      oldhour=$hour
    fi
  fi
done < ${ipath}/temptype
echo "$oldhour $maxval" >> ${ipath}/gethour.log 
tail -28 ${ipath}/gethour.log > ${ipath}/${ghost}.${gtype}.plot  
