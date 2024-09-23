#!/bin/bash
dte=$1;host=$2;proc=$3
MYSQL_SCHEMA="kanshi"
ROOT_DIRECTORY="/usr/local/bin"
CMD_MYSQL="mysql --defaults-extra-file=$ROOT_DIRECTORY/snmptrapmysql.conf -t --show-warnings $MYSQL_SCHEMA"
QUERY="DELETE FROM trapstatistics WHERE host='$host'"
VALUE=`echo ${QUERY} | ${CMD_MYSQL}`
QUERY="INSERT INTO trapstatistics (host,tstamp,process) VALUES('$host','$dte','$proc')"
VALUE=`echo ${QUERY} | ${CMD_MYSQL}`
RESULT=$?
if [ $RESULT -eq 0 ];then
  exit 0
else
  exit 1
fi
