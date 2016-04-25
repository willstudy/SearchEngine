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
		self.material = ""
		self.craft = ""
		self.step = ""
		self.tip = ""
		self.index = 0


	def Clear( self ) :

		self.url = ""
		self.title = ""
		self.introduce = ""
		self.material = ""
		self.craft = ""
		self.step = ""
		self.tip = ""
		self.index = 0

	def ShowInfo( self ):

		print 'url : ' + self.url
		print 'title : ' + self.title
		print 'introduce : ' + self.introduce
		print 'material : ' + self.material
		print 'craft : ' + self.craft
		print 'step : ' + self.step
		print 'tip : ' + self.tip
		print 'index : ' + self.index
		print '------------------'

	def GetWeight( self ):
		return 10

	def read_text( self, filename ):

		try :

			self.url = filename.readline().strip()
			self.title = filename.readline()
			self.introduce = filename.readline()
			filename.readline()
			self.material = filename.readline()
			self.craft = filename.readline()
			self.step = filename.readline()
			self.tip = filename.readline()
			filename.readline()

		#	self.ShowInfo()
			return 1

		except IOError:
			return 0

	def get_index( self ):

		sql = '''select id from url2id where url='%s' ''' % (self.url)

		try :
			cur.execute(sql)
			conn.commit()
			data = cur.fetchone()

			return int(data[0])
		except :
			print self.url + ' : error'
			return 0

	def Run( self ):

		with open('../db/web_text/recai.txt', 'r') as web_text:

			file_lda = open('../db/lda_model/recai/recai_lda.txt', 'w+')
			file_bayes = open('../db/lda_model/recai/recai_bayes.txt', 'w+')
			i = 0

			while i < 19513:

				self.read_text( web_text )
				index = self.get_index()
				
				file_bayes.write( self.title )
				if index != 0 :
					file_lda.write(str(self.get_index()) + ' ' + self.title )
				self.Clear()
				i += 1

			file_lda.close()
			file_bayes.close()

store = Store()
store.Run()
conn.close()
