#!/bin/bash
################################################################
## production
## called arguments
##   mrtgrun.sh <vpath_base> <vpath_mrtg>
## calling program
##   MrtgAutoRun.php
################################################################
base=$1
mrtg=$2
${base}/ubin/logwriter.sh 'mrtgrun.sh' 'start mrtgrun.sh' $base
env LANG=C ${mrtg} ${base}/mrtg/newmrtg.cfg >/dev/null 2>&1
${base}/ubin/logwriter.sh 'mrtgrun.sh' 'end mrtgrun.sh' $base
