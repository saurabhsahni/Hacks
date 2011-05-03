#!/usr/bin/env python
#
# askBOSS http://ask-boss.appspot.com/


import logging

import wsgiref.handlers
from google.appengine.ext import webapp

from yos.crawl.rest import download
from yos.util import console
from whenqa import whensearch
from wwwqa import imgsearch
from wwwqa import wwwsearch

global FEEDBACK_THANKS
FEEDBACK_THANKS = ""


    
SAMPLE_SEARCHES =   "<a href=\"/qa?query=who+is+batman+in+the+dark+knight\">" \
                    "who is batman in the dark knight</a><br/>" \
                    "<a href=\"/qa?query=what+is+the+tallest+mountain+in+the+world\">" \
                    "what is the tallest mountain in the world</a><br/>" \
                    "<a href=\"/qa?query=which+is+the+first+animal+to+reach+space\">" \
                    "which is the first animal to reach space</a><br/>" \
                    "<a href=\"/qa?query=who+invented+the+pneumatic+tire\">" \
                    "who invented the pneumatic tire</a><br/>" 
STYLE="<style type=\"text/css\"> .h1 a { text-decoration:none; }  </style>"

HEAD_JS="<script>var feedback=false; function toggleFeedback(){element = document.getElementById('boss_feedback'); if (feedback){element.style.display='none'; feedback=false;} else {element.style.display='inline'; feedback=true;} }</script>"


HOME_TEMPLATE = "<html><head><title>askBoss</title>" + HEAD_JS +"</head><body>" \
                "&nbsp;<font size=\"2\" face=\"arial\" color=\"#CC6600\">" \
                "<center><h1>askBoss</h1>" \
                "<i><font color=\"grey\">a natural language image search powered by <a href='http://developer.yahoo.com/search/boss/'><font color=\"grey\">Yahoo Boss</font></a> and <a href='http://appengine.google.com/'><font color=\"grey\">Google App Engine</font></a></font></i><br/><br/>" \
                "<br><form name=\"input\" action=\"qa\" method=\"get\">" \
                                "<input type=\"text\" name=\"query\" size=44 value=\"\">&nbsp;&nbsp;" \
                                "<input type=\"submit\" value=\"ask\"></form><br><br>"\
                                ""  + SAMPLE_SEARCHES +        "</font><br><br>  <p><center>Featured at: <b><a href='http://www.techcrunch.com/2008/09/03/yahoo-boss-used-to-create-powerset-for-images-and-more/'>TechCrunch</a></b> and <b><a href='http://www.ysearchblog.com/archives/000621.html'>Yahoo Search Blog</a></b></center></p>" 
                                         
FOOTER_TEMPLATE = "<br><br><br><center><a href='http://www.saurabhsahni.com/2008/08/natural-language-image-search-with-boss-and-app-engine/'>About askBoss</a> | <a href='javascript:void(0)' onclick='toggleFeedback();'>Feedback</a>"\
  "<br><br><div id='boss_feedback' style='display:none;'>"\
  "<form name=\"input\" action=\"feedback\" method=\"post\">" \
                  "<table border=0><tr><td>Name: </td><td><input type=\"text\" name=\"name\" size=25 value=\"\"></td></tr>" \
                  "<tr><td>Email: </td><td><input type=\"text\" name=\"email\" size=25 value=\"\"></td></tr>" \
                  "<tr><td>Feedback: </td><td><textarea name=\"feedback\" rows=8 cols=40\"></textarea>" \
                  "<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Submit\"></td></tr></table></form><br><br>You can also drop your feedback to ssahni [at] yahoo-inc.com (Saurabh Sahni)"\
  "</div>"\
  "</font></center>"\
"<script type=\"text/javascript\">"\
"var gaJsHost = ((\"https:\" == document.location.protocol) ? \"https://ssl.\" : \"http://www.\");"\
"document.write(unescape(\"%3Cscript src='\" + gaJsHost + \"google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E\"));</script><script type=\"text/javascript\">"\
"var pageTracker = _gat._getTracker(\"UA-691952-3\");pageTracker._trackPageview();</script>"\
"</body></head></html>"
                # "<i>a question answer image search powered by <a href='http://developer.yahoo.com/search/boss/'>Yahoo Boss</a> and <a href='http://appengine.google.com/'>Google app engine</a></i><br/>" \

SERP_TEMPLATE  = "<html><head><title>askBoss: %s</title>" + HEAD_JS +"</head><body><div align='center'>" \
                 "&nbsp;<font size=\"2\" face=\"arial\">" \
                 "<table width=\"1000\" border=0><tr><td><h1><a href=\"/\" style=\"text-decoration:none;\"><font color=\"#CC6600\" style='underline:none;'>askBoss</font></a></h1></td> <td> &nbsp;&nbsp;</td>" \
                 "<td>%s" 

FORM_TEMPLATE=                 "<font size=\"3\"><form name=\"input\" action=\"qa\" method=\"get\">" \
                                 "<input type=\"text\" name=\"query\" size=44 value=\"%s\">&nbsp;&nbsp;" \
                                 "<input type=\"submit\" value=\"ask\"></form>"

ANSW_TEMPLATE = "<font size=\"3\"><form name=\"input\" action=\"qa\" method=\"get\">" \
                "<input type=\"text\" name=\"query\" size=44 value=\"%s\">&nbsp;&nbsp;" \
                "<input type=\"submit\" value=\"ask\"></form>"\
                "</font></td><td>&nbsp;</td><td>&nbsp;</td><td> <small>"  + SAMPLE_SEARCHES +   "</small><td></tr></table>%s"


       
                
WHEN_TEMPLATE = "<b>Month:</b>&nbsp;%s&nbsp;&nbsp;<b>Year:</b>&nbsp;%s"
WWW_TEMPLATE = "<b>%s</b>%s "

FOUND_TEMPLATE="<br><table width=\"1000\" style='background:#f0f0f0;color:#777777;'><tr><td><small>Image Results</small></td><td style='text-align:right;'><right><small> %s - %s of about %s results for <b>%s</b></small></right></td></tr></table>"



ROOT_REDIRECT = "/qa?query="
#"qa?query=what+is+Agra+famous+for"
NO_QUERY = ""
#heya there, please provide a question"


def is_question(query):
  query = query.lower()
  if query.rfind('what ') > -1:
     return 1
  if query.rfind('where ') > -1:
     return 1     
  if query.rfind('which ') > -1:
     return 1
  if query.rfind('who ') > -1:
     return 1     
  return 0
  


def do_when(query):
  month, year = whensearch(query)
  answer = ANSW_TEMPLATE % (query, WHEN_TEMPLATE % (month, year))
  return SERP_TEMPLATE % (query, answer)

def do_www(query,page):
  phrase=""
  start = str(int(page) * 18)
  if is_question(query):
    phrase = str(wwwsearch(query))
    count=0
    images=""
    
    images,count = imgsearch(phrase,start,query)
  else:
    images,count = imgsearch(query,start)
  end=str(int(start)+18)
  if int(end) > int(count):
    end = count
 
  startIndex=int(start)+1
  if int(startIndex) > int(count):
    startIndex = count
  
     
  images = FOUND_TEMPLATE % (str(int(startIndex)), end,count,query) + images
  answer = ANSW_TEMPLATE % (query, WWW_TEMPLATE % ("",images))
  
  prev_num=str(int(page)-1)
  next_num=str(int(page)+1)
  
  previous=""
  next=""
  
  if page:
    if int(page) > 0:
      previous="Previous"
  
  if (int(start) + 18) < count:
    next = "Next"   
  
  navigation="<table width=\"1000px\"><tr><td><a href='/qa?query="+query+"&page="+prev_num+"'>"+previous+"</a></td><td style='text-align:right;'><right><a href='/qa?query="+query+"&page="+next_num+"'>"+next+"</a></right></td></tr></table>"

  
  page = SERP_TEMPLATE % (query, answer) + "<br>" + navigation  + "<br><br>"+ FORM_TEMPLATE % (query) + FOOTER_TEMPLATE
  return page

class QAHandler(webapp.RequestHandler):

  def get(self):
    query = console.strfix(self.request.get("query"))
    page = console.strfix(self.request.get("page"))
    global FEEDBACK_THANKS
    if page:
        page=page
    else:
      page=0
      
    start = ((page))
    
    feedback = self.request.get("feedback")
    global FEEDBACK_THANKS
      
    if feedback=="1":
      FEEDBACK_THANKS="<div align='center'><font color='red'>Thanks for your feedback!</font></div>"
    else:
      FEEDBACK_THANKS=""
    self.response.out.write(FEEDBACK_THANKS  )

#    qt = query.split()
#    if len(qt) == 0:
#      page = do_www(NO_QUERY)
#    elif qt[0].lower() == "when":
#      page = do_when(query)
#    else:
    page = do_www(query,start)
    self.response.out.write(page)
    
class RootHandler(webapp.RequestHandler):

  def get(self):
    feedback = self.request.get("feedback")
    global FEEDBACK_THANKS
      
    if feedback=="1":
      FEEDBACK_THANKS="<div align='center'><font color='red'>Thanks for your feedback!</font></div>"
    else:
      FEEDBACK_THANKS=""
    self.response.out.write(FEEDBACK_THANKS  )
    
    self.response.out.write(HOME_TEMPLATE + FOOTER_TEMPLATE)




def main():
  logging.getLogger().setLevel(logging.DEBUG)
  application = webapp.WSGIApplication([('/', RootHandler),
                                        ('/qa', QAHandler)],
                                       debug=True)
  wsgiref.handlers.CGIHandler().run(application)


if __name__ == '__main__':
  main()
