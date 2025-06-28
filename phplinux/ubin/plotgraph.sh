#!/bin/bash 
####################################################
## production
## description
##   Create a graph from the mrtg log.
## called arguments <vpath_base>
##   plotgraph.sh <vpath_ubin> <vpath_mrtghome> <vpath_plothome> <vpath_gnuplot> <vpath_mrtg> <debug>
## calling program
##   MrtgAutoRun.php
####################################################
ubin=$1
bpath=${hpath}/ubin
mpath=$2
ppath=$3
ipath=${ppath}/plotimage
gnuplot=$4
mrtg=$5
debug=$6
###
${ubin}/logwriter.sh 'plotgraph.sh' 'start plotgraph.sh' $ppath $debug
hostall=`grep Target ${mpath}/newmrtg.cfg | awk 'BEGIN{FS="\`"}{print $2}' | awk '{print $2}' | uniq`
for ghost in ${hostall[@]}
do
  ${ubin}/oklogmake.sh ${mpath} ${ppath} ${ghost} cpu 400 ${mrtg}
  ${ubin}/oklogmake.sh ${mpath} ${ppath} ${ghost} ram 400 ${mrtg}
  ${ubin}/oklogmake.sh ${mpath} ${ppath} ${ghost} disk 400 ${mrtg}
  ${ubin}/logwriter.sh 'plotgraph.sh' "make ${ghost}.exe" $ppath $debug
  echo "gnuplot:${gnuplot}" 
  echo "${gnuplot} -e 'path="\"${ipath}\""; ghost="\"${ghost}\""' ${ubin}/mkplot.plt" > ${ipath}/${ghost}.exe
  #echo "${gnuplotdir}/gnuplot -e 'path="\"${ipath}\""; ghost="\"${ghost}\""' ${bpath}/mkplot.plt" > ${ipath}/${ghost}.exe
  chmod +x ${ipath}/${ghost}.exe
  ${ubin}/logwriter.sh 'plotgraph.sh' "execute ${ghost}.exe" $ppath $debug
  ${ipath}/${ghost}.exe > /dev/null 2>&1
  ${ubin}/logwriter.sh 'plotgraph.sh' "${ghost} graph has been created" $ppath $debug
  #echo "$ghost resource-graph has been created"
done
${ubin}/logwriter.sh 'plotgraph.sh' 'end plotgraph.sh' $ppath $debug
