#coding=utf-8
import sys
import urllib2
import urllib
import re
import os
import shutil

index = 0
reload(sys)
sys.setdefaultencoding('utf-8')

class Spider:
	
	def __init__(self):

		self.title = ""
		self.keyword = []
		
	def StoreLinks(self):

		for link in self.links:
			print link
			fp.write(link)
			fp.write('\n')

		fp.flush()
	


	def GetTargetText(self,url): 		# get the target content

		user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_0)'
		headers = {'User-Agent' : user_agent }
		
		try:
			req = urllib2.Request(url, headers = headers)
			myResponse = urllib2.urlopen(req)
			mypage = myResponse.read()
			unicodepage = mypage.decode('utf-8')
		
		except:
			return 

		target = re.compile( 'id="recipe_title".*?>' )
		text = target.search( unicodepage ).group()
		chinese = re.compile(u"[\u4e00-\u9fa5]+")
		title_text = chinese.search( text )  
		
		if title_text :
			title = title_text.group()
			print title 
		else :
			links_unsolved.write( url )
			links_unsolved.write( '\n' )
			return 

		target_text = re.findall('"category_s1".*?"category_s2"', unicodepage, re.S )
		chinese = re.compile(u"[\u4e00-\u9fa5]+")
		for item in target_text:
			word = chinese.search( item )  
			
			if word :
				key = word.group().split( "的" )
				print key[0]
		
os.chdir("data")  # "data" is a directory
links_solved = open('breakfast.info', 'w')
links_unsolved = open('unsolved.db', 'a' )
spider = Spider()
#spider.GetTargetText('http://home.meishichina.com/recipe-242830.html')
spider.GetTargetText('http://home.meishichina.com/recipe-242759.html')
links_solved.close()
links_unsolved.close()


