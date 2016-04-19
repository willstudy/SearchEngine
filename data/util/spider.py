#coding=utf-8
import sys
import urllib2
import urllib
import re
import os
import shutil

reload(sys)
sys.setdefaultencoding('utf-8')


class Spider:

	def __init__(self):
		self.title = ""
		self.tips = ""
		self.picture = ""
		self.introduce = ""
		self.keyWords = []
		self.steps = []
		self.types = []

	def ShowTitle(self):
		print self.title

	def ShowTips(self):
		print self.tips

	def ShowPicture(self):
		print self.picture

	def ShowIntroduce(self):
		print self.introduce

	def ShowKeyWords(self):
		for key in self.keyWords:
			print key

	def ShowTypes(self):
		for type in self.types:
			print type

	def ShowSteps(self):
		for step in self.steps:
			print step

	def ShowTypes(self):
		for type in self.types:
			print type

	def Clear(self):
		self.title = ""
		self.tips = ""
		self.picture = ""
		self.introduce = ""
		self.keyWords = []
		self.steps = []
		self.types = []

	def SaveUnsolvedUrl(self, url):
		links_unsolved.write(url)
		links_unsolved.write('\n')

	def GetTargetTitle( self, target_text, chinese, url ):

		expr = re.compile( 'id="recipe_title".*?>' )
		content = expr.search( target_text ).group()
		title_text = chinese.search( content )

		if title_text :
			self.title = title_text.group()
			return 1
		else :
			print "GetTargetTitle failed to sovle the url"
			self.SaveUnsolvedUrl(url)
			return 0

	def GetTargetTips( self, target_text ): # some foods don't have tips about cooking

		expr = re.compile( 'recipeTip.*?div', re.S )
		content = expr.search( target_text )

		if content :
			tips = re.findall( u"[，。？！：\u4e00-\u9fa5]+", content.group(), re.S )
			for tip in tips:
				self.tips += tip
		else :
			print "Not find tips about this food !"
			self.tips = "NULL"
		return 1

	def GetTargetPicture( self, target_text, url ):

		expr = re.compile('http://i3.meishichina.com/attachment/recipe/.*?[jJ][pP][gG]')
		content = expr.search( target_text )

		if content :
			self.picture = content.group()
			return 1
		else :
			print "GetTargetPicture failed to sovle the url"
			self.SaveUnsolvedUrl(url)
			return 0

	def GetTargetIntroduce( self, target_text, chinese, url ): # some foods don't have introduce...

		expr = re.compile( 'txt_tart.*?txt_end', re.S )
		content = expr.search( target_text )

		if content :
			introduce_content = chinese.search( content.group() )
			if introduce_content :
				self.introduce = introduce_content.group()
		else :
			print "GetTargetIntroduce failed to sovle the url"
			self.introduce = "NULL"

		return 1

	def GetTargetKeyWords( self, target_text, chinese, url ):

		content = re.findall('"category_s1".*?"category_s2"', target_text, re.S )
		for item in content :
			key_word = chinese.search( item )
			if key_word :
				key = key_word.group().split( "的" )
				self.keyWords.append( key[0] )
			else :
				print "GetTargetKeyWord failed to sovle the url"
				self.SaveUnsolvedUrl(url)
				return 0
		return 1

	def GetTargetSteps( self, target_text, chinese, url ):

		content = re.findall( 'recipeStep_num.*?</li>', target_text, re.S )

		if content :
			for item in content:
				step = chinese.search( item )
				if step :
					self.steps.append( step.group() + ' ' )
			return 1
		else :
			print "GetTargetSteps failed to sovle the url"
			self.SaveUnsolvedUrl(url)
			return 0

	def GetTargetTypes( self, target_text, chinese, url ):

		expr = re.compile('recipeCategory clear.*?recipeStep', re.S )
		content = expr.search( target_text )

		if content :
			type_text = content.group()
		else :
			print "GetTargetTypes failed to sovle the url"
			self.SaveUnsolvedUrl(url)
			return 0

		types = re.findall( '<a title=.*?href="http://home\.meishichina\.com', type_text, re.S )

		for item in types :
			type_item = chinese.search( item )

			if type_item :
				self.types.append( type_item.group() )
			else :
				print "GetTargetTypes failed to sovle the url"
				self.SaveUnsolvedUrl(url)
				return 0
		return 1

	def GetTargetText( self, url ): 		# get the target content

		user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_0)'
		headers = {'User-Agent' : user_agent }

		try:
			req = urllib2.Request(url, headers = headers)
			myResponse = urllib2.urlopen(req)
			mypage = myResponse.read()
			unicodepage = mypage.decode('utf-8')

			target = re.findall('class="space_left".*?id="recipe_addcolist"', unicodepage, re.S )
			target_text = target[0]    # all useful information was stored here

			return target_text
		except:
			print "GetTargetText failed to sovle the url"
			self.SaveUnsolvedUrl(url)
			return 0

	def Run( self, url ):

		chinese = re.compile( u"[【】。，？：；、\u4e00-\u9fa5]+", re.S )
		target_text = spider.GetTargetText( url ) # reduce the word load


		if target_text :

			if self.GetTargetTitle( target_text, chinese, url ) == 0 :
				return 0          # if some fileds was parsed failed, we should finish it
			if self.GetTargetKeyWords( target_text, chinese, url ) == 0 :
				return 0
			if self.GetTargetPicture( target_text, url ) == 0 :
				return 0
			if self.GetTargetTypes( target_text, chinese, url ) == 0 :
				return 0
			if self.GetTargetTips( target_text ) == 0 :
				return 0
			if self.GetTargetIntroduce( target_text, chinese, url ) == 0 :
				return 0
			if self.GetTargetSteps( target_text, chinese, url ) == 0 :
				return 0
			return 1

	def Store( self, filefd ):

		filefd.write( self.title )
		filefd.write('\n')
		filefd.write( self.introduce )
		filefd.write('\n')
		filefd.write( self.picture )
		filefd.write('\n')

		for item in self.keyWords:
			filefd.write( item + "  " )
		filefd.write('\n')

		for item in self.types:
			filefd.write( item + "  " )
		filefd.write('\n')

		for item in self.steps:
			filefd.write(item)
		filefd.write('\n')

		filefd.write( self.tips )
		filefd.write('\n')
		filefd.write('\n')

	def GetAllInfo( self, links_write ):

		count = 1
		while ( count < 7557 ) :          # the number of links
			url = links_solved.readline().strip()
			if self.Run(url) ==  1 :
				self.Store(links_write)
				print count
			else :
				print "Unsolve: %s" % url

			self.Clear()
			count += 1

os.chdir("db")   # "data" is a directory
links_solved = open('url/xiao.db', 'r')
links_unsolved = open('url/unsolved.db', 'a+' )   # when parsed failed, it will be stored here
links_write = open('web_text/xiaochi.txt', 'a')

spider = Spider()
spider.GetAllInfo(links_write)

links_solved.close()
links_unsolved.close()
links_write.close()
