#!/bin/bash
hpath=$1
ppath=${hpath}/plot
bpath=${hpath}/bin
ls ${hpath}/mrtg/*.log | awk 'BEGIN{FS="mrtg/"}{print $2}' | sed s/.cpu.log// | grep -v ".log" >/tmp/loghost
ls ${hpath}/mrtg/*.log | awk 'BEGIN{FS="mrtg/"}{print $2}' | sed s/.ram.log// | grep -v ".log" >>/tmp/loghost
ls ${hpath}/mrtg/*.log | awk 'BEGIN{FS="mrtg/"}{print $2}' | sed s/.disk.log// | grep -v ".log" >>/tmp/loghost
hostall=`uniq /tmp/loghost`
for ghost in ${hostall[@]}
do
  ${bpath}/oklogmake.sh ${hpath} ${ghost} cpu 400
  ${bpath}/oklogmake.sh ${hpath} ${ghost} ram 400
  ${bpath}/oklogmake.sh ${hpath} ${ghost} disk 400
  echo "/usr/bin/gnuplot -e 'path="\"${ppath}\""; ghost="\"${ghost}\""' ${hpath}/bin/mkplot.plt" > ${ppath}/${ghost}.exe
  chmod +x ${ppath}/${ghost}.exe
  ${ppath}/${ghost}.exe
  #rm -f /tmp/loghost
  echo "${ghost}.svg image created"
done
