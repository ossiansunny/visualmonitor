#!/bin/bash
mpath=$1; ipath=$2; ghost=$3; gtype=$4; gline=$5
head -$gline ${mpath}/${ghost}.${gtype}.log | sort -k 1 -t " " | awk '{print $1,$2}' > ${ipath}/tmptype
oldhour=0; hour=0; maxval=0; fsw=0
rm -f ${ipath}/tmpgethour
while read unixtme load
do
  hour=`date -d @${unixtme} +"%H"`
  if [ ${fsw} -eq 0 ]; then
   fsw=1
   if [ ${maxval} -lt ${load} ]; then
     maxval=${load}
   fi
   oldhour=${hour}
  else
   if [ ${oldhour} -ne ${hour} ]; then
     echo "${oldhour} ${maxval}" >> ${ipath}/tmpgethour
     maxval=0
     if [ ${maxval} -lt ${load} ]; then
       maxval=${load}
     fi
     oldhour=${hour}
   fi
  fi
done < ${ipath}/tmptype
echo "${oldhour} ${maxval}" >> ${ipath}/tmpgethour
tail -28 ${ipath}/tmpgethour > ${ipath}/${ghost}.${gtype}.plot
rm -f ${ipath}/tmpgethour ${ipath}/tmptype
