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

file_read = open('split_material.txt', 'r')
file_write = open('percent_material.txt', 'w+')

for line in file_read.readlines():
	line = line.strip('\n')
	words.append(line)

word_set = set(words)   # exclude these repeated keyWords
length = len(words)
print length

for item in word_set :
	dictory[item] = words.count(item) / length    

new_dict = sorted( dictory.iteritems(), key = lambda d:d[1], reverse = True ) # sort by value

for key,value in new_dict:
	file_write.write( key + ":" + str(value) )
	file_write.write( '\n' )
