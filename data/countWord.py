#coding=utf-8
import os
import sys
import re

reload(sys)
sys.setdefaultencoding('utf-8')

file = open('./data/keyword.db', 'r')
text = file.read()

words = []
dictory = {}
word_set = []
new_dict = {}

target_text = re.findall( u"工艺：[\u4e00-\u9fa5]{1,2}", text.decode('utf-8'), re.S )

for item in target_text :
    key = item.split( "：" )
    words.append(key[1])

word_set = set(words)   # exclude these repeated keyWords

for item in word_set :
    dictory[item] = words.count(item)

new_dict = sorted( dictory.iteritems(), key = lambda d:d[1], reverse = True ) # sort by value

for key,value in new_dict:
    print "%s : %d" % (key, value)
    #print "%s" % key
