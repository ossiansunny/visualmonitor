#!/bin/sh
rm -f /tmp/traphandle.txt
touch /tmp/traphandle.txt
dte=`date +'%y%m%d%H%M%S'`
while read line
do
 echo "${line}" >> /tmp/traphandle.txt
 ikey=`echo "${line}" | awk '{print $1}'`
 if [ $ikey == "NET-SNMP-MIB::netSnmpExperimental.1" ]
 then 
   itrap=`echo "${line}" | awk '{print $2}'`
   #echo "${itrap}" >> /tmp/traphandle.txt
   ihost=`echo "${itrap}" | awk 'BEGIN{FS=":"}{print $1}'`
   jhost=`echo "${ihost}" | sed s/\"//` 
   iproc=`echo "${itrap}" | awk 'BEGIN{FS=":"}{print $2}'`
   jproc=`echo "${iproc}" | sed s/\"//` 
   echo "${dte} ${jhost} ${jproc}" >> /tmp/traphandle.txt
   /usr/local/bin/snmptrapmysql.sh ${dte} ${jhost} ${jproc}
 fi
done
