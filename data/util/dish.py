#coding=utf-8
import os
import sys
import MySQLdb

reload(sys)
sys.setdefaultencoding('utf-8')

conn = MySQLdb.connect( '139.129.129.74', 'disher', 'disher',   \
			'fairy', port = 3306, charset = "utf8" )

cur = conn.cursor()

class Store :

	def __init__(self):

		self.url = ""
		self.title = ""
		self.introduce = ""
		self.picture = ""
		self.material = ""
		self.craft = ""
		self.step = ""
		self.tip = ""
		self.weight = 0

	def Clear( self ) :

		self.url = ""
		self.title = ""
		self.introduce = ""
		self.picture = ""
		self.material = ""
		self.craft = ""
		self.step = ""
		self.tip = ""
		self.weight = 0

	def Encode( self ) :

		self.url.encode('UTF-8')
		self.title.encode('UTF-8')
		self.introduce.encode('UTF-8')
		self.picture.encode('UTF-8')
		self.material.encode('UTF-8')
		self.craft.encode('UTF-8')
		self.step.encode('UTF-8')
		self.tip.encode('UTF-8')

	def ShowInfo( self ):

		print 'url : ' + self.url
		print 'title : ' + self.title
		print 'introduce : ' + self.introduce
		print 'picture : ' + self.picture
		print 'material : ' + self.material
		print 'craft : ' + self.craft
		print 'step : ' + self.step
		print 'tip : ' + self.tip
		print 'weight : %d ' % self.weight
		print '------------------'

	def GetWeight( self ):
		return 10

	def read_text( self, filename ):

		try :

			self.url = filename.readline().strip()
			self.title = filename.readline().strip()
			self.introduce = filename.readline().strip()
			self.picture = filename.readline().strip()
			self.material = filename.readline().strip()
			self.craft = filename.readline().strip()
			self.step = filename.readline().strip()
			self.tip = filename.readline().strip()
			self.weight = self.GetWeight()
			filename.readline()

		#	self.ShowInfo()
			return 1

		except IOError:
			return 0

	def store( self, i ):

		sql_dish = '''insert into dish(id,url,title,picture,introduce,material,type,
		step,tip,weight) values(%d,'%s','%s','%s','%s','%s','%s','%s',
		'%s',%d )''' % (i, self.url.encode('UTF-8'), self.title.encode('UTF-8'), \
		self.picture.encode('UTF-8'), self.introduce.encode('UTF-8'), \
		self.material.encode('UTF-8'), self.craft.encode('UTF-8'), \
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

		with open('../db/web_text/dish.txt', 'r') as web_text:

			i = 0
			while i < 53633:
				self.read_text( web_text )
				i += 1
				self.store(i)
				self.Clear()
				print i

store = Store()
store.Run()
conn.close()
