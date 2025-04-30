#!/bin/bash
base=''
kanshiphp=''
group=''
if [ $# -eq 3 ]
then
  base=$1
  kanshiphp=$2
  group=$3
else
  echo "引数を指定して下さい <vpath_base> <vpath_kanshiphp> <group>"
  exit 1
fi
chgrp $group ${kanshiphp}
chgrp $group ${kanshiphp}/logs
chgrp $group ${kanshiphp}/mrtgcfg
chgrp $group ${base}/mrtg
chgrp $group ${base}/mrtg/mrtgimage
chgrp $group ${base}/mrtg/newmrtg.cfg
chgrp $group ${base}/plot
chgrp $group ${base}/plot/plotimage
chgrp $group ${base}/plot/logs
chmod 755 ${kanshiphp}
chmod 770 ${kanshiphp}/logs
chmod 770 ${kanshiphp}/mrtgcfg
chmod 770 ${base}/mrtg
chmod 770 ${base}/mrtg/mrtgimage
chmod 770 ${base}/mrtg/newmrtg.cfg
chmod 770 ${base}/plot
chmod 770 ${base}/plot/plotimage
chmod 770 ${base}/plot/logs
chmod 777 ${base}/ubin/*.sh
echo "エラーが無ければグループ設定完了"
