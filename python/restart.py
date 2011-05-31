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

#cur_main = con_main.cursor()
#
#sqls = ["alter database close normal","alter database dismount"]
#for sql in sqls:
#    try:
#        cur_main.execute(sql)
#    except cx_Oracle.DatabaseError,msg:
#        print "Failed: sql=%s" % sql, msg
#        sys.exit()
#
#try:
#    con_main.shutdown(mode = cx_Oracle.DBSHUTDOWN_FINAL)
#except cx_Oracle.DatabaseError,msg:
#    print "Failed to DBSHUTDOWN_FINAL.", msg
#    sys.exit()
#
#cur_main.close()

try:
    con_main = cx_Oracle.connect(username, password, '%s/%s' % (hostname, service), cx_Oracle.SYSDBA | cx_Oracle.PRELIM_AUTH)
except cx_Oracle.DatabaseError,msg:
    print "Failed.", msg
    sys.exit()

try:
    con_main.startup()
except cx_Oracle.DatabaseError,msg:
    print "Failed.", msg
    sys.exit()

try:
    con_main = cx_Oracle.connect(username, password, '%s/%s' % (hostname, service), cx_Oracle.SYSDBA)
except cx_Oracle.DatabaseError,msg:
    print "Failed.", msg
    sys.exit()

cur_main = con_main.cursor()

try:
    sql = "alter database mount"
    cur_main.execute(sql)
except cx_Oracle.DatabaseError,msg:
    print "Failed: sql=%s" % sql, msg
    sys.exit()

try:
    sql = "alter database open"
    cur_main.execute(sql)
except cx_Oracle.DatabaseError,msg:
    print "Failed: sql=%s" % sql, msg
    sys.exit()

sys.exit(1)
