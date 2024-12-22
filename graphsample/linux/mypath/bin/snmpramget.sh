#!/bin/bash
host=$1
ostype=$2
comm=$3
if [ "$ostype" == 'windows' ];then
  snmp=`snmpget -v1 -c$comm $host .1.3.6.1.2.1.25.2.3.1.5.5 2> /dev/null`
  if [ $? -eq 0 ]; then
    rams=`echo $snmp | awk 'BEGIN{FS=":"}{print $4}'`
    ramu=`snmpget -v1 -c$comm $host .1.3.6.1.2.1.25.2.3.1.6.5 | awk 'BEGIN{FS=":"}{print $4}'`
    echo `expr $ramu \* 100 / $rams`
    echo 100
  else
    echo 0
    echo 100
  fi
else
  snmp=`snmpget -v1 -c$comm $host .1.3.6.1.2.1.25.2.3.1.5.1 2> /dev/null`
  if [ $? -eq 0 ]; then
    rams=`echo $snmp | awk 'BEGIN{FS=":"}{print $4}'`
    ramu=`snmpget -v1 -c$comm $host .1.3.6.1.2.1.25.2.3.1.6.1 | awk 'BEGIN{FS=":"}{print $4}'`
    echo `expr $ramu \* 100 / $rams`
    echo 100
  else
    echo 0
    echo 100
  fi
fi
