set title ghost." Load Average"
set xlabel "Time Hour"
set ylabel "Load %"
set yrange [0:100]
set size 1 , 0.9
set terminal svg
set output path.ghost.".svg"
plot path.ghost.".ok" using 2:xticlabels(1) with lines title "CPU", path.ghost.".ok" using 3 with lines title "Memory", path.ghost.".ok" using 4 with lines title "Disk"
unset output
quit
