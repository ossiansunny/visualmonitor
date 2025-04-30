#!/bin/bash
ipaddr=$1
ostype=$2
comm=$3
ping -w 1 $ipaddr > /dev/null
if [ $? -eq 0 ]
then
  if [ "$ostype" == 'windows' ]
  then
    snmp=`snmpget -v1 -c$comm $ipaddr .1.3.6.1.2.1.25.2.3.1.5.1 2> /dev/null`
    if [ $? -eq 0 ]
    then
      disks=`echo $snmp | awk 'BEGIN{FS=":"}{print $4}'`
      disku=`snmpget -v1 -c$comm $ipaddr .1.3.6.1.2.1.25.2.3.1.6.1 | awk 'BEGIN{FS=":"}{print $4}'`
      echo `expr $disku \* 100 / $disks`
      echo 100
    else
      echo 0
      echo 0
    fi
  else
    #echo 'unix'
    snmpwalk -v1 -c$comm $ipaddr .1.3.6.1.2.1.25.2.3.1.3 &> /dev/null
    if [ $? -eq 0 ]
    then
      lastoid=`snmpwalk -v1 -c$comm $ipaddr .1.3.6.1.2.1.25.2.3.1.3 | grep ': /$' | awk '{print $1}' | awk 'BEGIN{FS="."}{print $2}'`
      #echo "lastOid: $lastoid"
      oid1=".1.3.6.1.2.1.25.2.3.1.5.$lastoid"
      oid2=".1.3.6.1.2.1.25.2.3.1.6.$lastoid"
      #  echo "OidSize: $oid1"
      #  echo "OidUsed: $oid2"
      disks=`snmpget -v1 -c$comm $ipaddr $oid1 | awk '{print $4}'`
      disku=`snmpget -v1 -c$comm $ipaddr $oid2 | awk '{print $4}'`
      #  echo "Size: $disks"
      #  echo "Used: $disku"
      echo `expr $disku \* 100 / $disks`
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
