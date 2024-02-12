import sys
import io
import pymysql
from datetime import datetime
#-----------------------------------------
#----Connect-------------------------------------
#-----------------------------------------
def pysqlconnect():
  myhost='localhost'
  myuser='root'
  mypass='bl13la06'
  mydb='kanshi'
  try:
    conn=pymysql.connect(host=myhost,user=myuser,password=mypass,db=mydb)
  except Exception as e:
    print('[DB Connect Error]',e)
    sys.exit(1)
  conn.ping(reconnect=True)
  return conn
##}

#conn=pysqlconnect()
#print(conn)

