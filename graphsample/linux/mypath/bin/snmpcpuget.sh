#!/bin/bash
host=$1
ostype=$2
comm=$3
ping -w 1 $host > /dev/null
if [ $? -eq 0 ]
then
  if [ "$ostype" == 'windows' ]
  then
    cpuarr=`snmpwalk -v1 -c${comm} ${host} .1.3.6.1.2.1.25.3.3.1.2 2>/dev/null | awk 'BEGIN{FS=":"}{print $4}'`
    maxcpu=0
    for cpu in ${cpuarr[@]}
    do
      #echo $cpu
      if [ $cpu -gt $maxcpu ]
      then
        maxcpu=$cpu
      fi
    done
    echo $maxcpu
    echo 100
  else
    cpuarr=`snmpwalk -v1 -c${comm} ${host} .1.3.6.1.4.1.2021.10.1.5 2>/dev/null | awk 'BEGIN{FS=":"}{print $4}'`
    maxcpu=0
    for cpu in ${cpuarr[@]}
    do
      #echo $cpu
      if [ $cpu -gt $maxcpu ]
      then
        maxcpu=$cpu
      fi
    done
    echo $maxcpu
    echo 100
  fi
else
  echo 0
  echo 0
fi
