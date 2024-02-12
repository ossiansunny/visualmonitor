import io
import sys
import mysqlkanshi

def kanshicfgttl(filename):
  cfglist=[]
  f = open(filename, 'r', encoding='UTF-8')
  datalist = f.readlines()
  for data in datalist:
    data=data.replace("\n", "")
    if '[' in data:
      cfglist.append(data)
    ##}
  ##}
  f.close()
  return cfglist
##}

def kanshicfg(filename,seckey):
  cfglist=[]
  f = open(filename, 'r', encoding='UTF-8')
  datalist = f.readlines()
  tsw=0
  for data in datalist:
    data=data.replace("\n", "")
    if tsw==1:
      if '[' not in data:
        cfglist.append(data)
      ##}
    ##}
    if seckey in data:
      tsw=1
    else:
      if '[' in data:
        tsw=0
      ##}
    ##}
  ##}
  f.close()
  return cfglist
##}

def mysqlvalins():
  cfg='/var/www/html/kanshi/vmsetup/mysqlvalins.cfg'
  cfgttl=kanshicfgttl(cfg)
  #print(cfgttl)
  for ttlitem in cfgttl:
    sqlnam=''
    sqlval=''
    table=ttlitem.lstrip('[')
    table=table.rstrip(']')
    cfgdata=kanshicfg(cfg,ttlitem)
    for citem in cfgdata:
      citemlst=citem.split('=')
      #print(citem)
      #citemval=citemlst[1].strip("'")
      #citemval=citemval.strip('"')
      #cl.append(citemval)
      sqlnam=sqlnam+citemlst[0]+','
      sqlval=sqlval+citemlst[1]+','
    ##}
    sqlnam=sqlnam.rstrip(',')
    sqlval=sqlval.rstrip(',')
    sqldel='delete from '+table
    print(sqldel)
    mysqlkanshi.pysqlconnput(sqldel)  
    sqlins='insert into '+table+ ' ('+sqlnam+') values('+sqlval+')'
    print(sqlins)
    mysqlkanshi.pysqlconnput(sqlins)
  ##}
##}
