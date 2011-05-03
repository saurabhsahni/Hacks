#!/usr/bin/env python




import wsgiref.handlers
import os
from google.appengine.ext import webapp
from google.appengine.api import mail

class MainHandler(webapp.RequestHandler):

  def get(self):
    self.response.out.write("<a href='http://ask-boss.appspot.com'>askBoss</a>")

  def post(self):
      name = (self.request.get("name"))
      email = (self.request.get("email"))
      if email=='':
          email='noreply@ask-boss.appspot.com'
      feedback = (self.request.get("feedback"))
      body= "askBoss Feedback:\n\nFrom: "+name +"\nEmail:"+email+"\nComments:\n"+feedback + "\nRemote Host: "+ str(os.getenv('REMOTE_HOST'))+" ("+str(os.getenv('REMOTE_ADDR'))+")"
      mail.send_mail(sender="saurabh.sahni@gmail.com",
                    to="Saurabh Sahni <saurabh.sahni@gmail.com>",
                    subject="Feedback: askBoss",
                    body=body)
     
      redir=os.getenv('HTTP_REFERER')

      if redir == '':
          redir="/"

      if redir.find('?') > -1:
          redir = redir + "&feedback=1"
      else:
          redir = redir + "?feedback=1"
            
      print "Location: "+redir         
      print         
      self.response.out.write("Thanks!")
      

def main():
  application = webapp.WSGIApplication([('/feedback', MainHandler)],
                                       debug=True)
  wsgiref.handlers.CGIHandler().run(application)


if __name__ == '__main__':
  main()
