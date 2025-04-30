#!/bin/bash 
####################################################
## production
## description
##   Create a graph from the mrtg log.
## called arguments <vpath_base>
##   plotgraph.sh <vpath_base>
## calling program
##   MrtgAutoRun.php
####################################################
hpath=$1
ppath=${hpath}/plot
ipath=${hpath}/plot/plotimage
bpath=${hpath}/ubin
gnuplot=$2
###
${bpath}/logwriter.sh 'plotgraph.sh' 'start plotgraph.sh' $hpath
hostall=`grep Target ${hpath}/mrtg/newmrtg.cfg | awk 'BEGIN{FS="\`"}{print $2}' | awk '{print $2}' | uniq`
for ghost in ${hostall[@]}
do
  ${bpath}/oklogmake.sh ${hpath} ${ghost} cpu 400
  ${bpath}/oklogmake.sh ${hpath} ${ghost} ram 400
  ${bpath}/oklogmake.sh ${hpath} ${ghost} disk 400
  ${bpath}/logwriter.sh 'plotgraph.sh' "make ${ghost}.exe" $hpath
  echo "gnuplot:${gnuplot}" 
  #echo "gnuplotbin:${gnuplotdir}/gnuplot" 
  echo "${gnuplot} -e 'path="\"${ipath}\""; ghost="\"${ghost}\""' ${bpath}/mkplot.plt" > ${ipath}/${ghost}.exe
  #echo "${gnuplotdir}/gnuplot -e 'path="\"${ipath}\""; ghost="\"${ghost}\""' ${bpath}/mkplot.plt" > ${ipath}/${ghost}.exe
  chmod +x ${ipath}/${ghost}.exe
  ${bpath}/logwriter.sh 'plotgraph.sh' "execute ${ghost}.exe" $hpath
  ${ipath}/${ghost}.exe > /dev/null 2>&1
  ${bpath}/logwriter.sh 'plotgraph.sh' "${ghost} graph has been created" $hpath
  #echo "$ghost resource-graph has been created"
done
${bpath}/logwriter.sh 'plotgraph.sh' 'end plotgraph.sh' $hpath
