#!/usr/bin/python

import sys
import cx_Oracle

username = sys.argv[1]
password = sys.argv[2]
hostname = sys.argv[3]
service = sys.argv[4]

con_main = cx_Oracle.connect(username, password, '%s/%s' % (hostname, service), cx_Oracle.SYSDBA)

try:
    con_main.shutdown(cx_Oracle.DBSHUTDOWN_IMMEDIATE)
except cx_Oracle.DatabaseError,msg:
    pass

cur_main = con_main.cursor()

sqls = ["alter database close normal","alter database dismount"]
for sql in sqls:
    try:
        cur_main.execute(sql)
    except cx_Oracle.DatabaseError,msg:
        sys.exit()

try:
    con_main.shutdown(mode = cx_Oracle.DBSHUTDOWN_FINAL)
except cx_Oracle.DatabaseError,msg:
    sys.exit()

sys.exit(1)
