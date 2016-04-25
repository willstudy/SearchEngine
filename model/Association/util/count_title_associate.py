#coding=utf-8

from __future__ import division
import os

"""
关联计算规则：
              C( A -> B )  
alpha =   ------------------
               S( B )

C( A -> B ) : A -> 的置信度
S( B )      : B 的支持度
"""

words = []
title = []
association = {}

with open('../data/title_idf.txt', 'r') as title_word:
	for line in title_word.readlines():
		words.append(line.strip())

with open('../data/title.txt', 'r') as title_file:
	for line in title_file.readlines():
		title.append(line.strip())

N = len(title)
word_count = {}

for word_A in words:
	associate = {}
	for word_B in words:

		if word_A == word_B:
			continue
		
		countA = 0
		countB = 0
		countAB = 0
		
		if word_count.has_key(word_A):
			countA = word_count[word_A]
		if word_count.has_key(word_B):
			countB = word_count[word_B]

		if countA != 0 and wordB != 0 :
			for title_name in title:
				if word_A in title_name and word_B in title_name:
					countAB += 1
		else :
			for title_name in title:
				if word_A in title_name and word_B in title_name:
					countAB += 1
				if word_A in title_name:
					countA += 1
				if word_B in title_name:
					countB += 1

		C_AB = countAB / countA
		S_B = countB / N	

		alpha = C_AB / S_B

		if alpha > 1 :
			associate[word_B] = alpha

	associate = sorted( associate.iteritems(), key = lambda a:a[1], reverse = True )
	association[word_A] = associate

with open('../data/title_associate.txt', 'w+') as file_write:
	for key in association:
		file_write.write( key )
		associate = association[key]

		i = 0
		for word, alpha in associate:
			if i > 5:
				break
			i += 1
			file_write.write( ':' + word + ':' + str(alpha) )
		file_write.write('\n')
