#!/bin/bash
httpbase='/var/www/html'
/bin/python3 ${httpbase}/kanshi/vmsetup/mysqlsetup.py
rm -f ${httpbase}/kanshi/mysqlconnect.py
cp ${httpbase}/kanshi/vmsetup/mysqlconnect.py ${httpbase}/kanshi/mysqlconnect.py
/bin/python3 ${httpbase}/kanshi/vmsetup/mysqldbset.py
/bin/python3 ${httpbase}/kanshi/vmsetup/mysqlinit.py
