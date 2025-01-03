#!/bin/bash
host=$1
ostype=$2
comm=$3
if [ "$ostype" == 'windows' ]; then
  snmp=`snmpget -v1 -c$comm $host .1.3.6.1.2.1.25.2.3.1.5.1 2> /dev/null`
  if [ $? -eq 0 ]; then
    disks=`echo $snmp | awk 'BEGIN{FS=":"}{print $4}'`
    disku=`snmpget -v1 -c$comm $host .1.3.6.1.2.1.25.2.3.1.6.1 | awk 'BEGIN{FS=":"}{print $4}'`
    echo `expr $disku \* 100 / $disks`
    echo 100
  else
    echo 0
    echo 100
  fi
else
  snmpwalk -v1 -c$comm $host .1.3.6.1.2.1.25.2.3.1.3 &> /dev/null
  if [ $? -eq 0 ]; then
    lastoid=`snmpwalk -v1 -c$comm $host .1.3.6.1.2.1.25.2.3.1.3 | grep ': /$' | awk '{print $1}' | awk 'BEGIN{FS="."}{print $2}'`
    oid1=".1.3.6.1.2.1.25.2.3.1.5.$lastoid"
    oid2=".1.3.6.1.2.1.25.2.3.1.6.$lastoid"
    disks=`snmpget -v1 -c$comm $host $oid1 | awk '{print $4}'`
    disku=`snmpget -v1 -c$comm $host $oid2 | awk '{print $4}'`
    echo `expr $disku \* 100 / $disks`
    echo 100
  else
    echo 0
    echo 100
  fi
fi
