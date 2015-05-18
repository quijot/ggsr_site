#!/usr/bin/env python
# -*- coding: UTF-8 -*-

from os import system


sourcetables = 'src/py/sourcetables.conf'
s2g_script = 'src/py/sourcetable2geojson.py'

st = open(sourcetables, 'r')
for line in st:
  if not line.startswith('#'):
    arg = line.split(',')
    system('python %s %s %s %s' % (s2g_script, arg[0], arg[1], arg[2]))
st.close()
