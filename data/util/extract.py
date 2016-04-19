import os

file_dish = open('../db/web_text/dish.txt', 'r')
file_title = open('../db/title.txt', 'w+')
file_material = open('../db/material.txt', 'w+')
file_type = open('../db/type.txt', 'w+')

try :
	while True:
		file_dish.readline()	
		file_title.write(file_dish.readline())	
		file_dish.readline()	
		file_dish.readline()	
		file_material.write(file_dish.readline())	
		file_type.write(file_dish.readline())	
		file_dish.readline()	
		file_dish.readline()	
		file_dish.readline()	
except:
	file_title.close()
	file_material.close()
	file_type.close()
