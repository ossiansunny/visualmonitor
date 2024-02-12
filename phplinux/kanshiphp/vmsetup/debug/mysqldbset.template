import sys
import io
import mysql.connector

def mysqldbset():
  myuser='root'
  mypass='bl13la06'
  myhost='localhost'
  cnx = None

  try:
    cnx = mysql.connector.connect(
        user=myuser,  # ユーザー名
        password=mypass,  # パスワード
        host=myhost  # ホスト名(IPアドレス）
    )
    cursor = cnx.cursor()
    cursor.execute("DROP DATABASE IF EXISTS kanshi2")
    cursor.execute("CREATE DATABASE kanshi2")
#    cursor.execute("SHOW DATABASES")
#    print(cursor.fetchall())
    cursor.close()

  except Exception as e:
    print(f"Error Occurred: {e}")

  finally:
    if cnx is not None and cnx.is_connected():
        cnx.close()
  ##} 
##} 

