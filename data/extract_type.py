#coding=utf-8
import os
import sys
import MySQLdb

reload(sys)
sys.setdefaultencoding('utf-8')

class Extract:

    def __init__( self ):

        self.url = ""               # 菜的url
        self.title = ""             # 菜的标题
        self.intro = ""             # 菜的介绍
        self.picture = ""           # 菜的图片
        self.material = ""          # 菜的主料
        self.type = []              # 菜的类型，有多个类型
        self.step = ""              # 菜的步骤
        self.tip = ""               # 菜的小窍门
        self.weight = 0             # 菜的权重
        self.table = []             # 每道菜该存入的表

    def clear( self ):

        self.url = ""               # 菜的url
        self.title = ""             # 菜的标题
        self.intro = ""             # 菜的介绍
        self.picture = ""           # 菜的图片
        self.material = ""          # 菜的主料
        self.type = []              # 菜的类型，有多个类型
        self.step = ""              # 菜的步骤
        self.tip = ""               # 菜的小窍门
        self.weight = 0             # 菜的权重
        self.table = []

    def show_info( self ):

        print 'URL :' + self.url
        print 'TITLE :' + self.title
        print 'INTRO :' + self.intro
        print 'PIC :' + self.picture
        print 'Material :' + self.material
        print 'Type :' + self.type
        print 'Step :' + self.step
        print 'TIP :' + self.tip
        print 'table :' + self.table

    def extract_info( self, filename ):

        lines = filename.readlines()

        self.url = lines[0]
        self.title = lines[1]
        self.intro = lines[2]
        self.picture = lines[3]
        self.material = lines[4]
        self.type = lines[5]
        self.step = lines[6]
        self.tip = lines[7]

    def extract_type( self ):

        expr = re.compile( u"['炒''煮''烧''蒸''炖''炸''煎''拌''烤']{1}" )
        target_text = expr.search( self.type.decode('utf-8') )

        if target_text :
            self.table.append(dictory[target_text.group().encode('utf-8')])
            #print 'find type :' + self.table
        else :
            self.table.append(dictory["其他"])
            #print 'not find type in :' + self.type

    def in_database( self ):

        for table in self.table:

            sql_dish = '''insert into ''' + table + '''(id,url,title,picture,introduce, \
            material,type,step,tip,weight) values(%d,'%s','%s','%s','%s','%s','%s','%s', \
    		'%s',%d )''' % (i, self.url.encode('UTF-8'), self.title.encode('UTF-8'), \
    		self.picture.encode('UTF-8'), self.introduce.encode('UTF-8'), \
    		self.material.encode('UTF-8'), self.type.encode('UTF-8'), \
    		self.step.encode('UTF-8'), self.tip.encode('UTF-8'),  \
    		self.weight )
"""
    		sql_url = '''insert into url2id(id, url) values(%d, '%s') '''  \
    			  % ( i, self.url )
"""
            try :
    			cur.execute( sql_dish )
#    			cur.execute( sql_url )
    			conn.commit()
    		except Exception,e :
                print e
                print "Error in " + self.url
                hello = raw_input()
    			continue

    def run( self, filename ):

        self.extract_info( filename )
        self.extract_type()
        #self.show_info()
        print 'type :' + self.type
        print 'table :' + self.table[0]




os.chdir("./data/appetizer")
dictory = { "炒":"parched", "煮":"cook", "烧":"burn", "蒸":"steam", "炖":"stew", \
            "炸":"fry", "煎":"decoct", "拌":"mix", "烤":"bake", "其他":"craft_other" }

conn = MySQLdb.connect( '139.129.129.74', '******', '******',   \
			'******', port = 3306, charset = "utf8" )    # 密码保密

cur = conn.cursor()
extract = Extract()

i = 1
while i < 371 :
    name = str(i) + '.txt'
    filename = open( name, 'r' )
    extract.run( filename )
    extract.clear()
    filename.close()
    i += 1

conn.close()
