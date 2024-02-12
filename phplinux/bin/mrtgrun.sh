#!/bin/bash
htdocs=$1
${htdocs}/bin/logwriter.sh 'mrtgrun.sh' 'start mrtgrun.sh' $htdocs
env LANG=C /usr/bin/mrtg ${htdocs}/mrtg/newmrtg.cfg >/dev/null 2>&1
${htdocs}/bin/logwriter.sh 'mrtgrun.sh' 'end mrtgrun.sh' $htdocs
