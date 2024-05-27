#!/bin/bash
rm -fr plot
mkdir -m 777 plot
ls -1 mrtg/*.log | awk 'BEGIN{FS="/"}{print $2}' >> plot/hostlist
fsw=0; oldhost=""; type=""
while read fname
do
  type=`echo $fname | awk 'BEGIN{FS="."}{print $(NF-1)}'`
  typelog=".${type}.log"
  ghost=`echo $fname | sed "s/$typelog//"`
  if [ $fsw -eq 0 ]; then
    fsw=1; oldhost=$ghost
  else
    if [ $oldhost != $ghost ]; then
      join plot/${oldhost}.cpu.plot plot/${oldhost}.ram.plot > plot/okcpuram.log
      join plot/okcpuram.log plot/${oldhost}.disk.plot > plot/${oldhost}.ok
      echo "/usr/bin/gnuplot -e 'path="\"plot\""; ghost="\"${oldhost}\""' bin/mkplot.plt" > plot/${oldhost}.exe
      chmod +x plot/${oldhost}.exe
      plot/${oldhost}.exe
      echo "$oldhost resource-graph has been created"
      oldhost=$ghost
    fi
  fi
  bin/oklogmake.sh ${ghost} ${type} 400
done < plot/hostlist
join plot/${ghost}.cpu.plot plot/${ghost}.ram.plot > plot/okcpuram.log
join plot/okcpuram.log plot/${ghost}.disk.plot > plot/${ghost}.ok
echo "/usr/bin/gnuplot -e 'path="\"plot\""; ghost="\"${ghost}\""' bin/mkplot.plt" > plot/${ghost}.exe
chmod +x plot/${ghost}.exe
plot/${ghost}.exe
echo "$ghost resource-graph has been created"
