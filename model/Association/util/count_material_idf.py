#coding=utf-8

from __future__ import division
import os

"""
把一些重要的词提取出来

这些词包括：

1：在每条记录中出现频度高
2：只包含2个汉字

目的是为了只为了计算这些词之间的关联度，减少计算量
"""

title = []
words = []

with open('../data/split_material.txt', 'r') as title_file:
	for line in title_file.readlines():
		line = line.strip()
		title.append(line)
		line = line.split(' ')
		for word in line:
			if len(word) < 4 or len(word) > 6:
				continue
			words.append(word)

title_num = len(title)
word_weight = {}
word_set = set(words)

for word in word_set:
	count = 0
	for item in title:
		if word in item:
			count += 1
	word_weight[word] = (count + 1)/title_num

word_weight = sorted(word_weight.iteritems(), key = lambda a:a[1], reverse=True)

file_write = open('../data/material_idf.txt', 'w+')
count = 0
for key, value in word_weight:
	if count > 1000:
		break
	file_write.write(key + '\n')
	count += 1
file_write.close()
