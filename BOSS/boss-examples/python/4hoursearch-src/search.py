# Copyright (c) 2008 Yahoo! Inc. All rights reserved.
# Licensed under the Yahoo! Search BOSS Terms of Use
# (http://info.yahoo.com/legal/us/yahoo/search/bosstos/bosstos-2317.html)
# @author Sam Pullara, samp@yahoo-inc.com
import logging
import wsgiref.handlers
import os

from google.appengine.ext.webapp import template
from datetime import datetime
from google.appengine.ext import webapp
from google.appengine.ext import db
from google.appengine.api import users
from yos.boss.ysearch import search
from yos.boss.ysearch import suggest
from yos.boss.ysearch import glue
from yos.crawl.rest import load_json
from yos.crawl.rest import load_xml
from django.utils import simplejson
from urllib import quote_plus
from types import *
from google.appengine.api import memcache

class Search(webapp.RequestHandler):
	def get(self):
		q = self.request.get("q")
		m = self.request.get("m")
		if q:
			start = self.request.get("p")
			query = q
			if m:
				query = m
			if start:
				result = search(query, count=10, start=int(start))
				images = search(query, vertical="images", count=1, start=int(start), filter="yes")
			else:
				result = search(query, count=10)
				images = search(query, vertical="images", count=1, filter="yes")
			resultset_glue = glue(q)
			ysr = result['ysearchresponse']
			if ysr.has_key('resultset_web'):
				results = ysr['resultset_web']
				template_values = {
					'query': q,
					'totalhits': int(ysr['totalhits']) + int(ysr['deephits']),
					'results': results,
                    'stats': memcache.get_stats()
                }
				if images:
					image_response = images['ysearchresponse']
					if int(image_response['count']) > 0:
						template_values['image'] = image_response['resultset_images'][0]
				if resultset_glue:
					categories = []
					if resultset_glue.has_key('glue') and resultset_glue['glue'].has_key('navbar'):
						navbars = resultset_glue['glue']['navbar']
						if navbars:
							for navbar in navbars:
								if isinstance(navbar, DictType):
									if navbar.has_key('navEntry'):
										if navbar['type'] == 'disambiguation':
											navEntries = navbar['navEntry']
											if isinstance(navEntries, DictType):
												categories.append(navEntries)
											else:
												for navEntry in navEntries:
													categories.append(navEntry)
						template_values['categories'] = categories
				if m:
					template_values['category'] = m.replace(" ", "%20")
				if start and int(start) != 0:
					template_values['start'] = start
					template_values['prev'] = int(start) - 10
					template_values['next'] = int(start) + 10
				else:
					template_values['next'] = 10
				path = os.path.join(os.path.dirname(__file__), "search.html")
				self.response.out.write(template.render(path, template_values))
			else:
				template_values = {
					'query': q,
				}
				path = os.path.join(os.path.dirname(__file__), "empty.html")
				self.response.out.write(template.render(path, template_values))
		else:
			self.redirect("/")
		
class Suggest(webapp.RequestHandler):
	def get(self):
		q = self.request.get("q")
		if q:
			suggests = suggest(q)
			self.response.headers['Content-Type'] = 'application/json'
			resultset = suggests['ResultSet']
			if isinstance(resultset, DictType):
				self.response.out.write("{ 'Results':[")
				results = resultset['Result']
				for result in results:
					self.response.out.write("{'v':'" + result + "'},")
				self.response.out.write("{}] }")
			else:
				self.response.out.write("{ 'Results':[]}")

class Index(webapp.RequestHandler):
	def get(self):
		path = os.path.join(os.path.dirname(__file__), "index.html")
		self.response.out.write(template.render(path, None))

def main():
  application = webapp.WSGIApplication([('/', Index),
    									('/search', Search),
										('/suggest', Suggest)
],
                                       debug=True)
  wsgiref.handlers.CGIHandler().run(application)

if __name__ == "__main__":
  main()
