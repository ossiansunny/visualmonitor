import sys
import io
import mysqlkanshi

def mysqltbset():
  filename='/var/www/html/kanshi/vmsetup/mysqltbset.cfg'
  f = open(filename, 'r', encoding='UTF-8')
  datalist = f.readlines()
  for data in datalist:
    data=data.replace("\n", "")
    if data[0] != "#":
      datalst=data.split(':')
      sql='drop table if exists '+datalst[0]
      mysqlkanshi.pysqlconnput(sql)
      print('----------------')
      sql=datalst[1]
      print(datalst[1])
      mysqlkanshi.pysqlconnput(sql)
    ##}
  ##}
  f.close()
##}
