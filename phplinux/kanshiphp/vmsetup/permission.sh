htdocs='/var/www/html'
owner='apache:apache'
chown $owner ${htdocs}/kanshiphp/logs
chown $owner ${htdocs}/kanshiphp/mrtgcfg
chown $owner ${htdocs}/mrtg
chown $owner ${htdocs}/mrtg/mrtgimage
chown $owner ${htdocs}/mrtg/newmrtg.cfg
chown $owner ${htdocs}/plot
chown $owner ${htdocs}/plot/plotimage
chown $owner ${htdocs}/plot/logs
chmod 770 ${htdocs}/kanshiphp/logs
chmod 770 ${htdocs}/kanshiphp/mrtgcfg
chmod 770 ${htdocs}/mrtg
chmod 770 ${htdocs}/mrtg/mrtgimage
chmod 770 ${htdocs}/mrtg/newmrtg.cfg
chmod 770 ${htdocs}/plot
chmod 770 ${htdocs}/plot/plotimage
chmod 770 ${htdocs}/plot/logs
