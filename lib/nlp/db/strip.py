#coding=utf-8
import os

with open('material_strip.txt', 'w+') as material_strip:
	with open('material.txt', 'r') as material_file:
		for line in material_file.readlines():
			line = line.replace(' ','')
			material_strip.write(line)

with open('type_strip.txt', 'w+') as type_strip:
	with open('type.txt', 'r') as type_file:
		for line in type_file.readlines():
			line = line.replace(' ','')
			line = line.replace('：', '')
			type_strip.write(line)

material_strip.close()
material_file.close()
type_strip.close()
type_file.close()
