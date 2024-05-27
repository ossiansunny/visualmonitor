#!/bin/bash
ghost=$1;gtype=$2;gline=$3
head -$gline mrtg/${ghost}.${gtype}.log | sort -k 1 -t " " | awk '{print $1,$2}' > plot/temptype
oldhour=0; hour=0; maxval=0; fsw=0
rm -f plot/gethour.log
while read unixtme load
do
  hour=`date -d @${unixtme} +"%H"`
  if [ $fsw -eq 0 ]; then
   fsw=1
   if [ $maxval -lt $load ]; then
     maxval=$load
   fi
   oldhour=$hour
  else
   if [ $oldhour -ne $hour ]; then
     echo "$oldhour $maxval" >> plot/gethour.log
     maxval=0
     if [ $maxval -lt $load ]; then
       maxval=$load
     fi
     oldhour=$hour
   fi
  fi
done < plot/temptype
echo "$oldhour $maxval" >> plot/gethour.log
tail -28 plot/gethour.log > plot/${ghost}.${gtype}.plot
