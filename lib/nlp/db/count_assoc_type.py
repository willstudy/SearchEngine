#coding=utf-8
from __future__ import division
import os
import sys
import re

reload(sys)
sys.setdefaultencoding('utf-8')


words = []
dictory = {}
word_set = []
new_dict = {}

file_read = open('split_type.txt', 'r')
file_write = open('split_unique_type.txt', 'w+')

for line in file_read.readlines():
	line = line.strip('\n')
	words.append(line)

word_set = set(words)   # exclude these repeated keyWords

for item in word_set :
	dictory[item] = words.count(item)    

new_dict = sorted( dictory.iteritems(), key = lambda d:d[1], reverse = True ) # sort by value

count = 0
for key,value in new_dict:
	if len(key) > 3 :      # 只取包含两个字以上的词条，作为关联度
		file_write.write( key )
		file_write.write( '\n' )
		count += 1

file_write.close()
