echo 'select debug from admintb' > tempsql
dbg=`mysql -ukanshiadmin -pkanshipass kanshi < tempsql`
key=`echo $dbg |awk '{print $1}'`
value=`echo $dbg |awk '{print $2}'`
echo $key: $value

debg=`mysql -ukanshiadmin -pkanshipass kanshi << EOT
select debug from admintb;
EOT`
key2=`echo $debg |awk '{print $1}'`
value2=`echo $debg |awk '{print $2}'`
echo $key2: $value2
