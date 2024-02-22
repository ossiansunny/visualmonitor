#!/bin/bash
htdocs=$1
sleep 1 
${htdocs}/bin/logwriter.sh 'plotrun.sh' 'start getsnmphost' $htdocs
${htdocs}/bin/getsnmphost $htdocs
${htdocs}/bin/logwriter.sh 'plotrun.sh' 'start plotadjust' $htdocs
${htdocs}/bin/plotadjust $htdocs 2> /dev/null
${htdocs}/bin/logwriter.sh 'plotrun.sh' 'end plotrun.sh' $htdocs
