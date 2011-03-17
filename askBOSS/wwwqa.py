# Copyright (c) 2008 Yahoo! Inc. All rights reserved.
# Licensed under the Yahoo! Search BOSS Terms of Use
# (http://info.yahoo.com/legal/us/yahoo/search/bosstos/bosstos-2317.html)

"""
This answers questions that start with who/what/where 
It searches the question, extracts the capitalized n-grams from the abstracts and titles
Then does a group by, summing up the frequencies, and prints the top n-gram
This technique actually works sometimes ...
"""

__author__ = "Vik Singh (viksi@yahoo-inc.com)"
import random

from collections import defaultdict
from operator import itemgetter

from yos.util import text, typechecks
from yos import yql
from yos.boss import ysearch
from google.appengine.ext import db
from google.appengine.api import memcache

def calc_pl(q):
  if q.split()[0].lower() == "who":
    return 2
  return 1
  
class QueryMiner:
  def __init__(self, query, pl=1):
    self._querystops = set(text.tokenize(query))
    self._pl = calc_pl(query)

  def extract(self, r):
    content = text.strip_enclosed_carrots(r["title"]) + " " + text.strip_enclosed_carrots(r["abstract"])
    ctokens = content.split()
  
    phrases = []
    current = []
    for ct in ctokens:
      if text.is_capitalized(ct):
        current += filter(lambda w: w not in self._querystops, text.tokenize(ct))
      elif len(current) > 0:
        if len(current) >= self._pl:
          phrases.append(" ".join(current))
        current = []
    return phrases


class QuestionAnswers(db.Model):
	  question = db.StringProperty(required=True)
	  answer = db.StringProperty(required=True)
	


def wwwsearch(q):
 # ans = memcache.get(q)
 # if ans:  
 #    return ans
  q=q.replace("'","")
  query = db.GqlQuery("SELECT * FROM QuestionAnswers WHERE question = '"+q+"'")
  result = query.get()
  if result:
    return result.answer
  
 
  qm = QueryMiner(q)

  def phrases_udf(r):
    r.update({"phrases": qm.extract(r)}) ; return r

  pc = defaultdict(lambda: 1)
  
  def pc_udf(r):
    for p in r["phrases"]: pc[p] += 1

    
  w = yql.db.select(udf=phrases_udf, data=ysearch.search(q, count=50))
  yql.db.select(udf=pc_udf, table=w)

  if len(pc) <= 0:
      return "Not Found"

  items= sorted(pc.iteritems(), key=itemgetter(1), reverse=True)
  if len(pc) > 0:
    ans= str((sorted(pc.iteritems(), key=itemgetter(1), reverse=True)[0][0]).encode('latin-1','ignore'))
  else:
    ans= "Not found"

  #memcache.add(key=q, value=ans)
  answer = QuestionAnswers(question=q,
 	            answer=ans)
  answer.put()
  return ans
  
  
  ##################### DONE ################
     
  index=-1
  topresults=[]
  for item in items:
      index=index+1
      inner=-1
      if index > 1:
         break
      count=item[1] 
      base= str((item[0]).encode('latin-1','ignore'))
      for phr in items:
         inner=inner+1
         if inner != index:
           text = str((phr[0]).encode('latin-1','ignore'))
           #print "text:" + text +" base: "+base
           if text.rfind(base) > -1:
            # print "Found"+base
             count=count+phr[1]
      topresults.append(count)           
#  print topresults
  indexc=0
  max=0
  maxi=0
  for result in topresults:
    if result > max:
        max = result
        maxi= indexc
#        print str(result)+" "+str(index)
    indexc = indexc + 1
#  print "yes"       
#  print str(items[maxi][0]) + " oldcount: "+ str(items[maxi][1]) + "newcount: " + str(max) + "index" + str(maxi)
  
    
  indexv=0
  max1=0
  maxi1=0
  for result in topresults:
    if indexv != maxi:    
      if result > max1:
         max1 = result
         maxi1= indexv
    indexv = indexv + 1 
 # print "yes"
#  print str(items[maxi1][0]) + " oldcount: "+ str(items[maxi1][1]) + "newcount: " + str(max1) + "index" + str(maxi1)
     
  ans=str((items[maxi][0]).encode('latin-1','ignore')) #+ ", " + str(repr(items[maxi1][0]))
 
 

#  if len(pc) > 0:
#    ans= str(sorted(pc.iteritems(), key=itemgetter(1), reverse=True)[0][0])
#  else:
#    ans= "Not found"

#  memcache.add(key=q, value=ans)
  answer = Answers(question=q,
	            answer=ans)
  answer.put()
  return ans

### ABOVE NOT USED ##########




def imgsearch(q,start=0,ques=""):
    image_results=[]
    image_results1=[]
    image_results2=[]
    count=0
    count1=0
    count2=0
    
    if ques!="":
        q2=text.mynorm(ques)
        if q2:
            ques=q2
            
        images2 = ysearch.search(ques, vertical="images", count=18, start=(int(start)/3))
        if images2:
            image_response2 = images2['ysearchresponse']
            count2 = int(image_response2['totalhits']) # + int(image_response['deephits'])
            if count2 > 0:
              image_results2 = image_response2['resultset_images'] 

        images1 = ysearch.search(q, vertical="images", count=18, start=(int(start)*2/3))
        if images1:
            image_response1 = images1['ysearchresponse']
            count1 = int(image_response1['totalhits']) # + int(image_response['deephits'])
            if count1 > 0: 
              image_results1 = image_response1['resultset_images']  
        
        count=count1 + count2
        
        c1=len(image_results1)
        c2=len(image_results2)
        
        if c1>=12 and c2>=6:   #both many
           image_results = image_results2[:6] + image_results1[:12]  
        else:
            
           if c1 >=12 and c2 > 0:  #more c1
              newc1=18-c2 
              image_results = image_results1[:newc1] + image_results2[:c2] 
           elif c2 >=6 and c1 > 0:  #more c2
              newc2=18-c1 
              image_results = image_results1[:c1] + image_results2[:newc2] 
           elif c1<=12 and c2<=6 and c1>0 and c2>0:   #both less
              image_results = image_results2[:6] + image_results1[:12]  
                    
           elif c1>0:
              newstart=int(start)-count2
              if int(newstart)<0:
                  newstart=0   
              images1 = ysearch.search(q, vertical="images", count=18, start=int(newstart))
              if images1:
                 image_response1 = images1['ysearchresponse']
                 count = int(image_response1['totalhits']) # + int(image_response['deephits'])
                 if count>0:
                   image_results = image_response1['resultset_images']                
           elif c2>0:
               
               newstart=int(start)-count1
               if int(newstart)<0:
                      newstart=0               
               
               images1 = ysearch.search(ques, vertical="images", count=18, start=int(newstart))
               if images1:
                  image_response1 = images1['ysearchresponse']
                  count = int(image_response1['totalhits']) # + int(image_response['deephits'])
                  if count>0:
                    image_results = image_response1['resultset_images']                
           
        
    else:
        if q!="":
          q2=text.mynorm(q)
          if q2:
              q=q2
        images1 = ysearch.search(q, vertical="images", count=18, start=int(start))
        if images1:
           image_response1 = images1['ysearchresponse']
           count = int(image_response1['totalhits']) # + int(image_response['deephits'])
           if count>0:
             image_results = image_response1['resultset_images']  
           
    
    
    random.seed(2)
    random.shuffle(image_results)      
    if image_results:
       # image_results = image_response1['resultset_images'] + image_response2['resultset_images']
        image = "<table width=\"1000\" border=0 ><tr><font size='2'>"
        if int(count) > 0:
          i=0
          size=0
          for images in image_results:
            if i > 0:
              if i % 6 == 0:
                image = image + "</tr><tr>"
            i+=1
            intSize=0
            try:
              intSize = float(images['size'])
            except ValueError:
              size=images['size']
            
            if intSize > 1024:
               intSize = intSize / 1024
               size = str(int(intSize)) + "K"
            elif intSize>0:
               size = str(int(intSize)) + "B"
            name=images['title']
            if len(name) > 17:
               name=name[:15]+"..."
            domain=images['refererurl']
            if len(domain) > 20:
	           domain=domain[:18]+"..."
            image = image +"<td><table><tr><td height='160px' style='vertical-align:bottom;'><a href='"+images['refererclickurl']+"'><img title='"+images['refererurl']+"'src='"+images['thumbnail_url']+"' style='max-height:150px;max-width:150px;'></a></td></tr><tr><td><center><small>"+name+"</small></center></td></tr><tr><td><center><font color='#444444'><small>"+images['width']+" X "+ images['height']+" | " + size + "</font></small></center></td></tr><tr><td><center><small><font color='#003399'>"+domain+"</font></small></center></td></tr></table></td> "
          
          image = image + "</font</tr></table>"
          return image,count     
        else:
          return "<br>No results found",0
    return "<br>No results found",0
          
            