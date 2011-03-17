#!/usr/bin/env python
#
# Copyright 2007 Google Inc.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#




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
