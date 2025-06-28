#!/bin/bash
###########################################################
## production
## called arguments
##   logwriter.sh <pgm_name> <log message> <vpah_plothome> <debug>
## calling program
##   mrtgrun.sh plotgraph.sh
########################################################### 
pgm=$1
msg=$2
plotdir=$3
debug=$4
#echo "debug: $debug"
if [ "$debug" == "5" ] || [ "$debug" == "6" ]
then
  dte=`date +'%y%m%d'`
  file="plot_$dte.log"
  if [ ! -f ${plotdir}/logs/$file ]
  then
    touch ${plotdir}/logs/$file
  fi
  stamp=`date +'%y%m%d%H%M%S'`
  echo "$pgm: $stamp $msg" >> ${plotdir}/logs/$file
  exit 0
fi
