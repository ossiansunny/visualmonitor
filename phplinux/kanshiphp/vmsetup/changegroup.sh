htdocs='/var/www/html'
group='apache'
chgrp $group ${htdocs}/kanshiphp/logs
chgrp $group ${htdocs}/kanshiphp/mrtgcfg
chgrp $group ${htdocs}/mrtg
chgrp $group ${htdocs}/mrtg/mrtgimage
chgrp $group ${htdocs}/mrtg/newmrtg.cfg
chgrp $group ${htdocs}/plot
chgrp $group ${htdocs}/plot/plotimage
chgrp $group ${htdocs}/plot/logs
chmod 770 ${htdocs}/kanshiphp/logs
chmod 770 ${htdocs}/kanshiphp/mrtgcfg
chmod 770 ${htdocs}/mrtg
chmod 770 ${htdocs}/mrtg/mrtgimage
chmod 770 ${htdocs}/mrtg/newmrtg.cfg
chmod 770 ${htdocs}/plot
chmod 770 ${htdocs}/plot/plotimage
chmod 770 ${htdocs}/plot/logs
