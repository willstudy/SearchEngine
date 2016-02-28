#coding=utf-8
import sys
import re
import os
import shutil

reload(sys)
sys.setdefaultencoding('utf-8')

class GetField(object):

    def __init__( self ):

		self.title = ""
		self.introduce = ""
		self.material = ""
		self.type = ""
		self.step = ""
		self.tip = ""

    def clear( self ):

        self.title = ""
        self.material = ""
        self.type = ""
        self.step = ""

    def get_field( self, fp ):

        lines = fp.readlines()

        self.title = lines[1]
        self.introduce = lines[2]
        self.material = lines[4]
        self.type = lines[5]
        self.step = lines[6]
        self.tip = lines[7]

    def write_field( self, fp_title, fp_introduce, fp_material, fp_type, fp_step, fp_tip ):

        fp_title.write( self.title )
        fp_type.write( self.type )
        fp_step.write( self.step )
        fp_material.write( self.material )

        if self.introduce != u'NULL' :
            fp_introduce.write( self.introduce )
        if self.tip != u'NULL' :
            fp_tip.write( self.tip )

    def run( self ):

       fp_title = open("title.txt",'a+')
       fp_introduce = open("introduce.txt", 'a+')
       fp_material = open("material.txt", 'a+')
       fp_type = open("type.txt", 'a+')
       fp_step = open("step.txt", 'a+')
       fp_tip = open("tip.txt", 'a+')

       os.chdir("data/soup")

       i = 1

       while i < 5868 :

           filename = str(i) + '.txt'
           fp_text = open( filename, 'r' )

           self.get_field( fp_text )
           self.write_field( fp_title, fp_introduce, fp_material, fp_type, fp_step, fp_tip )
           self.clear()

           fp_text.close()
           i += 1

       fp_title.close()
       fp_introduce.close()
       fp_material.close()
       fp_type.close()
       fp_step.close()
       fp_tip.close()

field = GetField()
field.run()
