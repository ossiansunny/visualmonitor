#!/bin/bash
if [ $# == 3 ]
then
  bpath=$1; mpath=$2; ppath=$3
else
  bpath="/mypath/bin"; mpath="/mypath/mrtg"; ppath="/mypath/plot"
fi
host="";gtype="";gline="400"
rm -f ${ppath}/tmphostlist ${ppath}/tmpokcpuram
rm -f ${ppath}/tmphostlist; 
ls -1 ${mpath}/*.log >> ${ppath}/tmphostlist
fsw=0; oldhost=""; gtype=""
while read fname
do
  gtype=`echo ${fname} | awk 'BEGIN{FS="."}{print $(NF-1)}'`
  gtypelog=".${gtype}.log"
  host=`echo ${fname} | sed "s/$gtypelog//" | awk '!/^#/{n=split($0,ary,"/");print(ary[n])}'`
  if [ ${fsw} -eq 0 ]; then
    fsw=1; oldhost=${host}
  else
    if [ ${oldhost} != ${host} ]; then
      join ${ppath}/${oldhost}.cpu.plot ${ppath}/${oldhost}.ram.plot > ${ppath}/tmpokcpuram
      join ${ppath}/tmpokcpuram ${ppath}/${oldhost}.disk.plot > ${ppath}/${oldhost}.ok
      echo "/usr/bin/gnuplot -e 'path="\"${ppath}\""; ghost="\"${oldhost}\""' ${bpath}/mkplot.plt" > ${ppath}/${oldhost}.exe
      chmod +x ${ppath}/${oldhost}.exe
      ${ppath}/${oldhost}.exe
      echo "${oldhost} resource-graph has been created"
      oldhost=${host}
    fi
  fi
  ${bpath}/oklogmake.sh ${mpath} ${ppath} ${host} ${gtype} ${gline}
done < ${ppath}/tmphostlist
join ${ppath}/${host}.cpu.plot ${ppath}/${host}.ram.plot > ${ppath}/tmpokcpuram
join ${ppath}/tmpokcpuram ${ppath}/${host}.disk.plot > ${ppath}/${host}.ok
echo "/usr/bin/gnuplot -e 'path="\"${ppath}\""; ghost="\"${host}\""' ${bpath}/mkplot.plt" > ${ppath}/${host}.exe
chmod +x ${ppath}/${host}.exe
${ppath}/${host}.exe
rm -f ${ppath}/tmphostlist ${ppath}/tmpokcpuram
echo "${host} resource-graph has been created"
