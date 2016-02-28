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

		self.links = []   			# save all links in the html
		self.enable = False

	def GetLinks(self,url): 		# get the links and the words

		user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_0)'
		headers = {'User-Agent' : user_agent }
		
		try:
			req = urllib2.Request(url, headers = headers)
			myResponse = urllib2.urlopen(req)
			mypage = myResponse.read()
			unicodepage = mypage.decode('utf-8')
		
		except:
			return 

		search_links = re.findall('href="http.*?"' , unicodepage, re.S)

		for text in search_links:
			links = re.findall('http://.*?html', text, re.S )

			for item in links:
				self.links.append(item)
				print item
		return 
				

	def GetAllLinks(self,url):   # too many bugs, don't use it!
		
		if len(self.links) > 100:
			print "haved exceed 100 pages"
			return

		self.GetLinks(url)

		for link in self.links:

			self.GetAllLinks(link)
	
	def ShowAllLinks(self):
		
		for link in self.links:
			print link

	def GetKeyWords(self):

		analyUrl = 'http://s.tool.chinaz.com/tools/robot.aspx'
		
		for link in self.links:
			
			global index 
			index = index + 1

			fp = open( str(index)+'.key' , 'w+' )
			fp.write( link + '\n' )
			postdata = urllib.urlencode({'url':link,'btn':' 查 询 '} )
			user_agent = 'Mozilla/4.0 (compatible; MSIE 5.5; Windows NT)' 
			headers = {'User-Agent':user_agent}

			req = urllib2.Request(analyUrl, postdata, headers)
			myResponse = urllib2.urlopen(req)
			myPage = myResponse.read()
			unicodepage = myPage.decode('utf-8')

			selectText = re.search('<textarea.*?</textarea>', unicodepage, re.S)
			p = re.compile('<[^>]+>')
			keyWord = p.sub("", selectText.group())
			fp.write(keyWord)
			fp.close()
			return

os.chdir("data")  # "data" is a directory
spider = Spider()
#spider.GetAllLinks('http://www.kugou.com')
#spider.GetLinks('http://i.meishi.cc/recipe_list/detail.php?cid=6755308')
spider.GetLinks('http://home.meishichina.com/recipe.html')
#spider.GetKeyWords()
