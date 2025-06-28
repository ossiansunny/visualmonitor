#!/bin/bash
################################################################
## production
## called arguments
##   mrtgrun.sh <vpath_ubin> <vpath_mrtg> <vpath_mrtghome> <vpath_plothome> <debug>
## calling program
##   MrtgAutoRun.php
################################################################
ubin=$1
mrtg=$2
mpath=$3
ppath=$4
debug=$5
if [ ${mrtg:0:1} == '/' ] 
then
  ${ubin}/logwriter.sh 'mrtgrun.sh' 'start mrtgrun.sh' $bggth $debug
  env LANG=C ${mrtg} ${mpath}/newmrtg.cfg >/dev/null 2>&1
  ${ubin}/logwriter.sh 'mrtgrun.sh' 'end mrtgrun.sh' $ppath $debug
else
  utime=`date +%s`
  hostall=`grep Target ${mpath}/newmrtg.cfg | awk 'BEGIN{FS="\`"}{print $2}' | awk '{print $2","$3","$4}' | uniq`
  for hostoscomm in ${hostall[@]}
  do
    ${ubin}/logwriter.sh 'mrtgrun.sh' 'start mrtgrun.sh' $bggth $debug
    #echo $hostoscomm
    ghost=`echo $hostoscomm | awk 'BEGIN{FS=","}{print $1}'`
    gos=`echo $hostoscomm | awk 'BEGIN{FS=","}{print $2}'`
    comm=`echo $hostoscomm | awk 'BEGIN{FS=","}{print $3}'`
    #echo "$ghost $gos $comm"
    cpu=`${ubin}/snmpcpuget.sh $ghost $gos $comm`
    cpu1=`echo $cpu | awk '{print $1}'`
    cpu2=`echo $cpu | awk '{print $2}'` 
    echo "$utime $cpu1 $cpu2" >> ${mpath}/mrtgimage/${ghost}.cpu.plog
    ram=`${ubin}/snmpramget.sh $ghost $gos $comm`
    ram1=`echo $ram | awk '{print $1}'`
    ram2=`echo $ram | awk '{print $2}'` 
    echo "$utime $ram1 $ram2" >> ${mpath}/mrtgimage/${ghost}.ram.plog
    disk=`${ubin}/snmpdiskget.sh $ghost $gos $comm`
    disk1=`echo $disk | awk '{print $1}'`
    disk2=`echo $disk | awk '{print $2}'` 
    echo "$utime $disk1 $disk2" >> ${mpath}/mrtgimage/${ghost}.disk.plog
    ${ubin}/logwriter.sh 'mrtgrun.sh' 'end mrtgrun.sh' $ppath $debug
  done
fi
