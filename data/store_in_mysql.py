#coding=utf-8
import os
import sys
import MySQLdb

reload(sys)
sys.setdefaultencoding('utf-8')

conn = MySQLdb.connect( ‘139.129.129.74’, 'user', 'passwd',   \
			'fairy', port = 3306, charset = "utf8" )

cur = conn.cursor()

class Store :

	def __init__(self):

		self.url = ""
		self.title = ""
		self.introduce = ""
		self.picture = ""
		self.material = ""
		self.type = ""
		self.step = ""
		self.tip = ""
		self.weight = 0

	def Clear( self ) :

		self.url = ""
		self.title = ""
		self.introduce = ""
		self.picture = ""
		self.material = ""
		self.type = ""
		self.step = ""
		self.tip = ""
		self.weight = 0

	def Encode( self ) :

		self.url.encode('UTF-8')
		self.title.encode('UTF-8')
		self.introduce.encode('UTF-8')
		self.picture.encode('UTF-8')
		self.material.encode('UTF-8')
		self.type.encode('UTF-8')
		self.step.encode('UTF-8')
		self.tip.encode('UTF-8')

	def ShowInfo( self ):

		print 'url : ' + self.url
		print 'title : ' + self.title
		print 'introduce : ' + self.introduce
		print 'picture : ' + self.picture
		print 'material : ' + self.material
		print 'type : ' + self.type
		print 'step : ' + self.step
		print 'tip : ' + self.tip
		print 'weight : %d ' % self.weight
		print '------------------'

	def GetWeight( self ):
		return 10

	def GetText( self, filename ):

		try :
			fp = open( filename, 'r' )

			lines = fp.readlines()

			self.url = lines[0]
			self.title = lines[1]
			self.introduce = lines[2]
			self.picture = lines[3]
			self.keyword = lines[4]
			self.type = lines[5]
			self.step = lines[6]
			self.tip = lines[7]

			self.weight = self.GetWeight()

			self.ShowInfo()
			fp.close()

		except IOError:
			print 'file %s open failed' % filename
			fp.close()

	def InDb( self, i ):

		sql_dish = '''insert into dish(id,url,title,picture,introduce,material,type,
		step,tip,weight) values(%d,'%s','%s','%s','%s','%s','%s','%s',
		'%s',%d )''' % (i, self.url.encode('UTF-8'), self.title.encode('UTF-8'), \
		self.picture.encode('UTF-8'), self.introduce.encode('UTF-8'), \
		self.material.encode('UTF-8'), self.type.encode('UTF-8'), \
		self.step.encode('UTF-8'), self.tip.encode('UTF-8'),  \
		self.weight )

		sql_url = '''insert into url2id(id, url) values(%d, '%s') '''  \
			  % ( i, self.url )


		try :
			cur.execute( sql_dish )
			cur.execute( sql_url )
			conn.commit()
		except :
			return

	def Run( self ):

		i = 1

		while i < 371 :

			filename = str(i) + '.txt'
			self.GetText( filename )
			self.InDb(i)
			self.Clear()

			i += 1



os.chdir("data/appetizer")

store = Store()
store.Run()

conn.close()
