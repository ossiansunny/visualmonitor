# inpu: host community
processes=`snmpget -v1 -cprivate localhost VM-MIB::mystring2.0 | awk '{print $4}' | sed 's/;/ /g' | sed 's/\"//g'`
#echo $processes
procs=""
for item in ${processes[@]}
do
  prc=`ps -ef | grep $item | grep -v grep | grep -v snmptrap | wc -l`
  if [ $prc -eq 0 ]
  then
    procs=`echo "$procs$item;"`
    #echo $procs
  fi
done
if [ -z $procs ]
then
  ans='allok'
else
  ans=`echo $procs | sed 's/;$//'`
fi
#echo $ans
snmpset -v1 -cprivate localhost VM-MIB::mystring3.0 s "$ans" &> /dev/null
