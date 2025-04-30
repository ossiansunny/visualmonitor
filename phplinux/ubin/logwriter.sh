#!/bin/bash
###########################################################
## production
## called arguments
##   logwriter.sh <pgm_name> <log message> <vpah_base>
## calling program
##   mrtgrun.sh plotgraph.sh
########################################################### 
pgm=$1
msg=$2
kanshibase=$3
logname='plot' 
#echo "[INFO] 処理開始"
# 定数の定義
MYSQL_SCHEMA="kanshi"
ROOT_DIRECTORY="${kanshibase}/ubin"
CMD_MYSQL="mysql --defaults-extra-file=$ROOT_DIRECTORY/mysql.cfg -t --show-warnings $MYSQL_SCHEMA"
 
# SQLの指定
# カラムにアスタリスク（*）を使うとうまくいかなかったので、一つずつ指定
QUERY="SELECT debug FROM admintb "
# シェルを実行、実行ログを受け取る
VALUE=`echo ${QUERY} | ${CMD_MYSQL}`
# 処理の終了コードを取得
RESULT=$?
#echo $VALUE
debug=`echo $VALUE | awk '{print $7}'`
echo $debug
# 結果のチェック
if [ $RESULT -eq 0 ]; then
    #echo "[INFO] 処理終了"
    if [ $debug -eq 5 ] || [ $debug -eq 6 ]
    then
      dte=`date +'%y%m%d'`
      file="${logname}_$dte.log"
      if [ ! -f ${kanshibase}/plot/logs/$file ]
      then
        touch ${kanshibase}/plot/logs/$file
      fi
      stamp=`date +'%y%m%d%H%M%S'`
      echo "$pgm: $stamp $msg" >> ${kanshibase}/plot/logs/$file
    fi
    exit 0
else
    #echo "[ERROR] 予期せぬエラーが発生 異常終了"
    exit 1
fi
