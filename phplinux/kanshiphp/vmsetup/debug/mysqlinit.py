import sys
import os

import mysqlsetup
import mysqldbset
import mysqltbset
import mysqlvalins

mysqlsetup.sqlvalset('mysqlsetup.cfg','mysqlconnect.template')
mysqlsetup.sqlvalset('mysqlsetup.cfg','mysqldbset.template')
mysqldbset.mysqldbset()
mysqltbset.mysqltbset()
mysqlvalins.mysqlvalins()

