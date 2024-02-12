import io
import os
import sys

def sqlvalset(cfgfile,pyfile):
  cfglist=[]
  filename='/var/www/html/kanshi/vmsetup/'+cfgfile
  f = open(filename, 'r', encoding='UTF-8')
  datalist = f.readlines()
  for data in datalist:
    data=data.replace("\n", "")
    if '=' in data:
      datalist=list(data)
      dataval=datalist[1].replace("'","")
      dataval=dataval.replace('"','')
      dataval='"'+dataval+'"'
      cfglist.append(dataval)
    ##}
  ##}
  f.close()
  pyfilelst=pyfile.split('.')
  filename='/var/www/html/kanshi/vmsetup/'+pyfile
  outfile='/var/www/html/kanshi/vmsetup/'+pyfilelst[0]+'.py'
  if os.path.exists(outfile):
    os.remove(outfile)
  ##}
  fw = open(outfile,"w")
  f = open(filename, 'r', encoding='UTF-8')
  datalist = f.readlines()
  for data in datalist:
    data=data.replace("\n", "")
    datalenrecord=len(data)
    datalenstring=len(data.strip(' '))
    dataleftspace=datalenrecord-datalenstring
    sdata=data.strip(' ')
    if '=' in data:
      sw=0
      for kitem in cfglist:
        kitemlen=len(kitem)
        skitem=kitem.split('=')
        kskitem=skitem[0]+'='
        if kskitem in data:
          ll=dataleftspace+kitemlen
          lenlen="{:>"+str(ll)+"}"
          #print(lenlen.format(kitem))
          fw.write(lenlen.format(kitem)+"\n")
          sw=1
          break
        ##}
      ##}
      if sw==0:
        #print(data)
        fw.write(data+"\n")
      ##}
    else:
      #print(data)
      fw.write(data+"\n")
    ##}      
  ##}
  f.close()
  fw.close()
##}

sqlvalset('mysqlsetup.cfg','mysqlconnect.template')
sqlvalset('mysqlsetup.cfg','mysqldbset.template')

