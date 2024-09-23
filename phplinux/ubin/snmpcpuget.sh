#!/bin/bash
ipaddr=$1
ostype=$2
comm=$3
ping -w 1 $ipaddr > /dev/null
if [ $? -eq 0 ]; then
#### ping ok
  if [ "$ostype" == 'windows' ]; then
  #### windows
    snmpwalk -v1 -c$comm $ipaddr .1.3.6.1.2.1.25.3.3.1.2 2> /dev/null > tempcpu
    if [ $? -eq 0 ]; then
      maxcpu=0
      while read cpurec
      do
        cpu=`echo $cpurec | awk 'BEGIN{FS=":"}{print $4}'`
        if [ $cpu -gt $maxcpu ]; then
          maxcpu=$cpu
        fi
      done <tempcpu 
      echo $maxcpu; echo 100
    else
      echo 0; echo 100
    fi
  else
  #### Linux
    snmp=`snmpwalk -v1 -c$comm $ipaddr .1.3.6.1.4.1.2021.10.1.5 2> /dev/null > tempcpu`
    if [ $? -eq 0 ]; then
      maxcpu=0
      while read cpurec
      do
        cpu=`echo $cpurec | awk 'BEGIN{FS=":"}{print $4}'`
        if [ $cpu -gt $maxcpu ]; then
          maxcpu=$cpu
        fi
      done <tempcpu
      echo $maxcpu; echo 100
    else
      echo 0; echo 100
    fi
  fi
else
#### ping ng
  echo 0; echo 100
fi
