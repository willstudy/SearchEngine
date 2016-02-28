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

		self.links = []   		# save all links in the html
		self.pages = []			#  save next pages

	def StoreLinks(self):

		for link in self.links:
#			print link
			fp.write(link)
			fp.write('\n')

		fp.flush()

	def GetPages(self):

		i = 1

		while ( i < 1000 ):   	# total 704 pages
			page = 'http://home.meishichina.com/recipe/xiaochi/page/'+str(i)+'/'
			self.pages.append(page)
			i = i+1

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

		search_links = re.findall('id="J_list".*?</ul>' , unicodepage, re.S)

		for text in search_links:
			links = re.findall('http://home.meishichina.com/recipe.*?.html', text, re.S )

			for item in links:
				if item not in self.links:
					self.links.append(item)
#					print item

		self.StoreLinks()
		self.links = []

	def GetAllLinks(self):
		for page in self.pages:
			self.GetLinks(page)
			print page

	def ShowAllLinks(self):

		for link in self.links:
			print link

	def ShowAllPages(self):

		for page in self.pages:
			print page

os.chdir("data")  # "data" is a directory
fp = open('snack.db', 'a+')
spider = Spider()
spider.GetPages()
spider.GetAllLinks()
fp.close()
