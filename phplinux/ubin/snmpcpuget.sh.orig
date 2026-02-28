#!/bin/bash
ipaddr=$1
ostype=$2
comm=$3
#echo "$ipaddr $ostype $comm"
hpath=`pwd`
hpath=`dirname $hpath`
bpath="${hpath}/ubin"
cpuarr=()
ping -c 2 -t 2 $ipaddr > /dev/null
if [ $? -eq 0 ]; then
#${bpath}/logwriter.sh "snmpcpuget.sh" " $ipaddr ping ok" "$hpath"
    #${bpath}/logwriter.sh "snmpcpuget.sh" "$ostype" "$hpath"
    cpuarr+=(`snmpwalk -v1 -c$comm $ipaddr .1.3.6.1.2.1.25.3.3.1.2 2> /dev/null | awk 'BEGIN{FS=":"}{print $4}'`)
    #${bpath}/logwriter.sh "snmpcpuget.sh" "return code:$?" "$hpath"
    if [ $? -eq 0 ]; then
      #${bpath}/logwriter.sh "snmpcpuget.sh" "snmpwalk ok" "$hpath"
      maxcpu=0
      for cpu in ${cpuarr[@]}
      do
          #echo $cpu
        #${bpath}/logwriter.sh "snmpcpuget.sh" "cpuvalue:$cpu" "$hpath"
        if [ $cpu -gt $maxcpu ]; then
          maxcpu=$cpu
          #echo $cpu
        fi
      done 
      #${bpath}/logwriter.sh "snmpcpuget.sh" "maxcpu:$maxcpu" "$hpath"
      echo $maxcpu; echo 100
    else
      #${bpath}/logwriter.sh "snmpcpuget.sh" "snmpwalk ng" "$hpath"
      echo 0; echo 100
    fi
else
#### ping ng
  #${bpath}/logwriter.sh "snmpcpuget.sh" "$ipaddr ping ng" "$hpath"
  echo 0; echo 100
fi
