###
reqkey=("vpath_ncat vpath_htdocs")
#for reqitem in ${reqkey[@]}
#do
  #echo $reqitem
#done
###
ansval=("")
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

for item in ${keyvalx[@]}
do
  keyitem=`echo $item | awk 'BEGIN{FS=":"}{print $1}'`
  echo $keyitem >> keyitem
  valitem=`echo $item | awk 'BEGIN{FS=":"}{print $2}'`
  echo $valitem >> valitem
  for reqitem in ${reqkey[@]}
  do
    if [ $reqitem == $keyitem ]
    then
      echo -n 'match:'
      echo $item
    fi
  done
done
