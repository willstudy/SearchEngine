#coding=utf-8
from __future__ import division
import os
import sys

reload(sys)
sys.setdefaultencoding('utf-8')

file_title = open('percent_title.txt', 'r')
file_material = open('percent_material.txt', 'r')
file_type = open('percent_type.txt', 'r')
file_gather = open('gather.txt', 'w+')

title_num = 123320
material_num = 292582
type_num = 495875

laplace_title = 1 / ( title_num * 2 )
laplace_material = 1 / ( material_num * 2 )
laplace_type = 1 / ( type_num * 2 )

dictory_title = {}
dictory_material = {}
dictory_type = {}

for line in file_title.readlines():

	line = line.strip('\n')
	result = line.split(':')
	dictory_title[result[0]] = result[1]

file_title.close()

for line in file_material.readlines():

	line = line.strip('\n')
	result = line.split(':')
	dictory_material[result[0]] = result[1]

file_material.close()

for line in file_type.readlines():

	line = line.strip('\n')
	result = line.split(':')
	dictory_type[result[0]] = result[1]

file_type.close()

dictory = {}

for key in dictory_title:
	
	percent = []

	if dictory.has_key( key ):
		pass
	else :
		percent.append(dictory_title[key])
		percent.append( str(laplace_material) )
		percent.append( str(laplace_type) )

		dictory[key] = percent


for key in dictory_material:

	percent = []

	if dictory.has_key(key):
		percent = dictory[key]
		percent[1] = dictory_material[key]
	else:
		percent.append( str(laplace_title) )
		percent.append(dictory_material[key])
		percent.append( str(laplace_type) )
	
	dictory[key] = percent

for key in dictory_type:

	percent = []

	if dictory.has_key( key ):
		percent = dictory[key]
		percent[2] = dictory_type[key]
	else:
		percent.append( str(laplace_title) )
		percent.append( str(laplace_material) )
		percent.append(dictory_type[key])

	dictory[key] = percent

for key in dictory:
	
	file_gather.write(key + ':')

	for item in dictory[key]:
		
		file_gather.write( item + ' ' )
	
	file_gather.write('\n')

file_gather.close()
