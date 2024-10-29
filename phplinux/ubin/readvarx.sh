#!/bin/bash
###
###
ansval=()
###
### function ###
###
vpathget(){
  reqkey=$1
  keyvalx=("")
  while read kanshidata
  do
    #echo $kanshidata
    key=`echo $kanshidata | awk 'BEGIN{FS="="}{print $1}' | sed 's/\n//'`
    val=`echo $kanshidata | awk 'BEGIN{FS="="}{print $2}' | sed 's/"//g' | sed 's/\n//' | sed 's/\r//'`
    keyx=`echo -n $key`
    valx=`echo -n $val`
    keyvalx+=($keyx:$valx)
  done < /var/www/html/kanshiphp/vmsetup/kanshiphp.ini
  ###
  echo 'start'

  for reqitem in ${reqkey[@]}
  do
    #echo $reqitem
    for ansitem in ${keyvalx[@]}
    do
      keyitem=`echo $ansitem | awk 'BEGIN{FS=":"}{print $1}'`
      #echo $keyitem
      if [ $reqitem == $keyitem ]
      then
        echo -n 'match:'
        echo $ansitem
        valitem=`echo $ansitem | awk 'BEGIN{FS=":"}{print $2}'`
        #echo $valitem >> valitem
        ansval+=($valitem)
      fi
    done
  done
}
###
### end function
###
vpathreq=("vpath_kanshiphp vpath_ncat vpath_htdocs")
### call function
vpathget "${vpathreq[*]}"
### return function
echo ${ansval[0]}
echo ${ansval[1]}
echo ${ansval[2]}
for ansitem in ${ansval[@]}
do
  echo $ansitem
done
