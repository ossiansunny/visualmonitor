#!/bin/bash
htdocs=$1
plotpath='/var/www/html/plot'
sleep 1 
${htdocs}/bin/getsnmphost $htdocs
${htdocs}/bin/plotadjust $htdocs 2> /dev/null
