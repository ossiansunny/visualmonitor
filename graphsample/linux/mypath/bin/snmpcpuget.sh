#!/bin/bash
host=$1
ostype=$2
comm=$3
cpuarr=`snmpwalk -v1 -c${comm} ${host} .1.3.6.1.2.1.25.3.3.1.2 2>/dev/null | awk 'BEGIN{FS=":"}{print $4}'`
maxcpu=0
for cpu in ${cpuarr[@]}
do
  if [ $cpu -gt $maxcpu ]; then
    maxcpu=$cpu
  fi
done
echo $maxcpu
echo 100
