#!/bin/bash
hpath=$1
ppath=${hpath}/plot
ipath=${hpath}/plot/plotimage
bpath=${hpath}/ubin
${bpath}/logwriter.sh 'plotgrapg.sh' 'start plotgraph.sh' $hpath
hostall=`grep Target ${hpath}/mrtg/newmrtg.cfg | awk 'BEGIN{FS="\`"}{print $2}' | awk '{print $2}' | uniq`
for ghost in ${hostall[@]}
do
  ${bpath}/oklogmake.sh ${hpath} ${ghost} cpu 400
  ${bpath}/oklogmake.sh ${hpath} ${ghost} ram 400
  ${bpath}/oklogmake.sh ${hpath} ${ghost} disk 400
  ${bpath}/logwriter.sh 'plotgrapg.sh' "make ${ghost}.exe" $hpath
  echo "/usr/bin/gnuplot -e 'path="\"${ipath}\""; ghost="\"${ghost}\""' ${bpath}/mkplot.plt" > ${ipath}/${ghost}.exe
  chmod +x ${ipath}/${ghost}.exe
  ${bpath}/logwriter.sh 'plotgrapg.sh' "execute ${ghost}.exe" $hpath
  ${ipath}/${ghost}.exe
  ${bpath}/logwriter.sh 'plotgrapg.sh' "${ghost} graph has been created" $hpath
  #echo "$ghost resource-graph has been created"
done
${bpath}/logwriter.sh 'plotgrapg.sh' 'end plotgraph.sh' $hpath
