#!/bin/bash 
###
source /var/www/html/ubin/varread.func
###
vpathreq=("vpath_kanshiphp vpath_ncat vpath_htdocs")
### call function
varread "${vpathreq[*]}"
### return function
anscount=${#vpathval[@]}
echo $anscount
#echo ${vpathval[0]}
#echo ${vpathval[1]}
#echo ${vpathval[2]}
for ansitem in ${vpathval[@]}
do
  echo $ansitem
done
vpathreq=("vpath_gnuplotbin")
varread "${vpathreq[*]}"
anscount=${#vpathval[@]}
echo $anscount
if [ $anscount -ne 1 ]
then
  echo "no data, check kanshiphp.ini"
else
  for ansitem in ${vpathval[@]}
  do
    echo $ansitem
  done
fi
