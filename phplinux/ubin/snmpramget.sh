#!/bin/bash
ipaddr=$1
ostype=$2
comm=$3
ping -c 2 -t 2 $ipaddr > /dev/null
if [ $? -eq 0 ]
then
  if [ "$ostype" == 'windows' ]
  then
    snmp=`snmpget -v1 -c$comm $ipaddr .1.3.6.1.2.1.25.2.3.1.5.5 2> /dev/null`
    if [ $? -eq 0 ]
    then
      rams=`echo $snmp | awk 'BEGIN{FS=":"}{print $4}'`
      ramu=`snmpget -v1 -c$comm $ipaddr .1.3.6.1.2.1.25.2.3.1.6.5 | awk 'BEGIN{FS=":"}{print $4}'`
      echo `expr $ramu \* 100 / $rams`
      echo 100
    else
      echo 0
      echo 0
    fi
  else
    snmp=`snmpget -v1 -c$comm $ipaddr .1.3.6.1.2.1.25.2.3.1.5.1 2> /dev/null`
    if [ $? -eq 0 ]
    then
      rams=`echo $snmp | awk 'BEGIN{FS=":"}{print $4}'`
      ramu=`snmpget -v1 -c$comm $ipaddr .1.3.6.1.2.1.25.2.3.1.6.1 | awk 'BEGIN{FS=":"}{print $4}'`
      echo `expr $ramu \* 100 / $rams`
      echo 100
    else
      echo 0
      echo 0
    fi
  fi
else
  echo 0
  echo 0
fi
