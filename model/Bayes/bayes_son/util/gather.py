#coding=utf-8
from __future__ import division
import os
import sys

reload(sys)
sys.setdefaultencoding('utf-8')

file_recai = open('../data/percent_recai.txt', 'r')
file_tanggeng = open('../data/percent_tanggeng.txt', 'r')
file_xiaochi = open('../data/percent_xiaochi.txt', 'r')
file_gather = open('../data/gather.txt', 'w+')

recai_num = 66550
tanggeng_num = 23848
xiaochi_num = 34423

laplace_recai = 1 / ( recai_num * 2 )
laplace_tanggeng = 1 / ( tanggeng_num * 2 )
laplace_xiaochi = 1 / ( xiaochi_num * 2 )

dictory_recai = {}
dictory_tanggeng = {}
dictory_xiaochi = {}

for line in file_recai.readlines():

	line = line.strip('\n')
	result = line.split(':')
	dictory_recai[result[0]] = result[1]

file_recai.close()

for line in file_tanggeng.readlines():

	line = line.strip('\n')
	result = line.split(':')
	dictory_tanggeng[result[0]] = result[1]

file_tanggeng.close()

for line in file_xiaochi.readlines():

	line = line.strip('\n')
	result = line.split(':')
	dictory_xiaochi[result[0]] = result[1]

file_xiaochi.close()

dictory = {}

for key in dictory_recai:
	
	percent = []

	if dictory.has_key( key ):
		pass
	else :
		percent.append(dictory_recai[key])
		percent.append( str(laplace_tanggeng) )
		percent.append( str(laplace_xiaochi) )

		dictory[key] = percent


for key in dictory_tanggeng:

	percent = []

	if dictory.has_key(key):
		percent = dictory[key]
		percent[1] = dictory_tanggeng[key]
	else:
		percent.append( str(laplace_recai) )
		percent.append(dictory_tanggeng[key])
		percent.append( str(laplace_xiaochi) )
	
	dictory[key] = percent

for key in dictory_xiaochi:

	percent = []

	if dictory.has_key( key ):
		percent = dictory[key]
		percent[2] = dictory_xiaochi[key]
	else:
		percent.append( str(laplace_recai) )
		percent.append( str(laplace_tanggeng) )
		percent.append(dictory_xiaochi[key])

	dictory[key] = percent

for key in dictory:
	
	file_gather.write(key + ':')

	for item in dictory[key]:
		
		file_gather.write( item + ' ' )
	
	file_gather.write('\n')

file_gather.close()
