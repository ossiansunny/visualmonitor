import sys
import io
import pymysql
import mysqlconnect
from datetime import datetime
#-----------------------------------------
#----Connect-------------------------------------
#-----------------------------------------
#def pysqlconnect():
#  try:
#    conn=pymysql.connect(host='localhost',user='root',password='bl13la06',db='kanshi')
#  except Exception as e:
#    print('[DB Connect Error]',e)
#    sys.exit(1)
#  conn.ping(reconnect=True)
#  return conn
#------------------------------------------
#----pysqlget--------------------------------------
#------------------------------------------
#def pysqlget(_conn,_sql):
#  cur = _conn.cursor()
#  res = []
#  try:
#    cur.execute(_sql)
#    _conn.commit()
#    res = cur.fetchall()
#    return res
#  except Exception as e:
#    print('[DB Connect & get Error]',e)
#    sys.exit(1)
#------------------------------------------
#----pysqlconnget--------------------------------------
#------------------------------------------
def pysqlconnget(_sql):
  conndef = mysqlconnect.pysqlconnect()
  cur = conndef.cursor()
  res = []
  try:
    cur.execute(_sql)
    conndef.commit()
    res = cur.fetchall()
    return res
  except Exception as e:
    print('[DB connget Error]',e)
    sys.exit(1)
#------------------------------------------
#----pysqlput--------------------------------------
#------------------------------------------
#def pysqlput(_conn,_sql):
#  cur = _conn.cursor()
#  try:
#    cur.execute(_sql)
#    _conn.commit()
#  except Exception as e:
#    print('[DB Connect & put  Error]',e)
#    sys.exit(1)
#------------------------------------------
#----pysqlconnput--------------------------------------
#------------------------------------------
def pysqlconnput(_sql):
  conndef = mysqlconnect.pysqlconnect()
  cur = conndef.cursor()
  try:
    cur.execute(_sql)
    conndef.commit()
  except Exception as e:
    print('[DB connput Error]',e)
    sys.exit(1)
#-----------------------------------------
#----read log-------------------------------------
#-----------------------------------------
def readlog():
  rtable=[]
  with open('kanshi.log', 'r') as f:
    rtable.append(f.read())
  return rtable
#-----------------------------------------
#----write log-------------------------------------
#-----------------------------------------
def writelog(_pgm,_msg):
  with io.open('kanshi.log', 'a', encoding='utf8') as f:
    now=datetime.now()
    tstamp = now.strftime("%y%m%d%H%M%S")
    data=tstamp+' '+_pgm+' '+_msg+'\n'
    f.write(data)

