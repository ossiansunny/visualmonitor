set title ghost." Load Average"
set xlabel "Time Hour"
set ylabel "Load %"
set yrange [0:100]
set size 1 , 0.9
set terminal svg
set output path."/".ghost.".svg"
plot path."/".ghost.".cpu.plot" using 2:xticlabels(1) with lines title "CPU", path."/".ghost.".ram.plot" using 2 with lines title "Memory", path."/".ghost.".disk.plot" using 2 with lines title "Disk"
unset output
quit
