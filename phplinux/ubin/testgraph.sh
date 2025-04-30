#!/bin/bash 
####################################################
# directory sample
#htdocs /var/www/html             <--- Document Root
#base   /var/www/html/phplinux    <--- Kanshi Root
####################################################
hpath=$1
ppath=${hpath}/plot
ipath=${hpath}/plot/plotimage
bpath=${hpath}/ubin
source ${bpath}/testread.func
###
vpathreq=("vpath_gnuplot")
varread "${vpathreq[*]}"
anscount=${#vpathval[@]}
if [ $anscount -ne 1 ]
then
  echo 'No path data, Check kanshiphp.ini'
  ${bpath}/logwriter.sh 'plotgraph.sh' 'No path data, Check kanshiphp.ini' $hpath
  exit 1
else
  gnuplot=${vpathval[0]}
  #gnuplotdir=${vpathval[0]}
fi
${bpath}/logwriter.sh 'plotgraph.sh' 'end plotgraph.sh' $hpath
