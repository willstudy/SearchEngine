#coding=utf
from __future__ import division
import os

"""
用于计算split_unique_type.txt中每个词条之间的关联度，
其结果保存在associate_type.txt中，格式在assoc_material.py中有说明
"""

dish_title = []                      # 这个列表存储所有的菜名
split_word = []                      # 这个列表存储，分割所有菜名后得到的词条
dict_word_count = {}                 # 存储每个词条在多少道菜中出现
dict_word_dish = {}                  # 存储每个词条所在的菜名
dict_assoc = {}                      # 存储词条之间的关联度    

# 处理之后的type_strip.txt 将空格和冒号去掉，加快速度
with open( 'type_strip.txt', 'r' ) as dish_file:
	for line in dish_file.readlines():
		line.strip()
		dish_title.append( line )
dish_file.close()

with open( 'split_unique_type.txt', 'r' ) as word_file:
	for word in word_file.readlines():
		word = word.strip()
		split_word.append( word )
word_file.close()

"""
计算这个词条在多少道菜中出现,
并把词条和对应出现的菜名保存在dict_word_dish字典中
"""
for word in split_word:

	count = 0;
	dish_list = []

	for dish in dish_title :
		if word in dish:
			dish_list.append(dish)
			count += 1
	if count > 0 :
		dict_word_count[word] = count
		dict_word_dish[word] = dish_list

print "各词条出现在菜名集合的数目计算完毕"


"""
计算两个词条同时出现在多少道菜中,
并取同时出现次数最高的前6个，并保存在
dict_assoc字典中
"""
for word in split_word:

	word_list = {}
	dish_list_word = dict_word_dish[word]

	for item in split_word:
		# 它们之间若相互包含，则认为是同一件事物,eg: 豆腐 == 油豆腐
		if item in word or word in item:
			continue
		count = 0
		for dish in dish_list_word:
			if word in dish and item in dish:
				count += 1

		if count > 0:
			word_list[item] = count 
	if len(word_list) > 5 :
		# 先对出现的次数进行排序，取前6个
		result = sorted( word_list.iteritems(), key = lambda d:d[1], reverse = True )
		new_list = {}
		i = 0
		for key,value in result:
			if i > 5:
				break
			i += 1
			new_list[key] = value
		dict_assoc[word] = new_list

print "关联度计算完毕"

with open( 'associate_type.txt','w+' ) as associate_file:
	for item in dict_assoc:
		word_list = dict_assoc[item]
		associate_file.write( str(item) )
		for word in word_list:
			associate_file.write( ':' )
			associate_file.write(str(word))
			associate_file.write(':')
			associate_file.write(str(word_list[word]))
		associate_file.write('\n')
associate_file.close()

