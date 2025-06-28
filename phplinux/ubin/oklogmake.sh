#!/bin/bash
####################################################
## production
## description
##   Create a graph from the mrtg log.
## called arguments <vpath_base>
##   oklogmake.sh <vpath_mrtghome> <vpath_plothome> <host> <type> <lines> <vpath_mrtg>
## calling program
##   plotgraph.sh
####################################################
mpath=$1
ppath=$2
ghost=$3
gtype=$4
gline=$5
mrtg=$6
mipath=${mpath}/mrtgimage
pipath=${ppath}/plotimage
os=$(uname -s)
if [ ${mrtg:0:1} == '/' ]
then
  head -$gline ${mipath}/${ghost}.${gtype}.log | sort -k 1 -t " " | awk '{print $1,$2}' > ${mipath}/temptype
else
  tail -$gline ${mipath}/${ghost}.${gtype}.plog | sort -k 1 -t " " | awk '{print $1,$2}' > ${mipath}/temptype
fi
oldhour=0
hour=0
maxval=0
fsw=0
rm -f ${mipath}/gethour.log
while read unixtme load
do
  if [ $os == 'Darwin' ]
  then
    hour=`date -r ${unixtme} +"%H"`
  else
    hour=`date -d @${unixtme} +"%H"`
  fi
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
      echo "$oldhour $maxval" >> ${mipath}/gethour.log 
      maxval=0  
      if [ $maxval -lt $load ]
      then
        maxval=$load
      fi
      oldhour=$hour
    fi
  fi
done < ${mipath}/temptype
echo "$oldhour $maxval" >> ${mipath}/gethour.log 
tail -28 ${mipath}/gethour.log > ${pipath}/${ghost}.${gtype}.plot  
