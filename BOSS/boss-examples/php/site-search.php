<DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<-- 
Created by Marco Vitanza
http://marcovitanza.com

This code is hereby released into the public domain.
Feel free to copy, modify, and reuse it in any way.
-->
<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1" />
<title>Custom Search with Yahoo BOSS</title>
<style type="text/css">
/* SIMPLE STYLING FOR SEARCH RESULTS */
body { font-family:arial; font-size:12px; }
div.result { margin-bottom:15px; background-color:#efefef; padding:10px; }
div.result a { font-size:18px; font-weight:bold; }
div.result div { float:left; font-size:30px; font-weight:bold; margin-right:10px; color:#c0c0c0; }
</style>
</head>
<body>
<h1>Custom Search with Yahoo BOSS</h1>
<?php
// Echo the search form
echo '<form action="" method="get">';
echo 'Search for <input type="text" name="q" value="';

// Needed if MAGIC_QUOTES is on
// $_GET['q'] = stripslashes($_GET['q']);
	
// Echo the query in the search text field 
$query = htmlentities($_GET['q'], ENT_QUOTES, 'UTF-8');
echo $query . '" />   <input type="submit" value="Go" /></form>';

// If there is a query
if(!empty($_GET['q']))
{
	echo '<hr />';
	
	// IMPORTANT: FILL IN THESE FIELDS
	//////////////////////////////////////////////////////////////////////////////////////
	// Yahoo AppID - get one by registering at http://developer.yahoo.com/search/boss
	$appid = 'YOUR_YAHOO_APP_ID_HERE';
	// Comma-separated list of domains to search in
	$domains = 'yourdomain.com';
	//////////////////////////////////////////////////////////////////////////////////////
	
	// Main part of BOSS query URL - note the "web/" for web pages only
	$pre = 'http://boss.yahooapis.com/ysearch/web/v1/';
	// Set the current result page. Specific page may be requested from a Next or Prev link
	$results_per_page = 10;
	$page = intval($_GET['page']);
	if($page < 1)
		$page = 1;
	// Partial query paramaters. Format can also be JSON
	$params = '&format=xml&sites=' . $domains . '&count=' . $results_per_page . '&start=' . (($page - 1) * $results_per_page);

	// Initialize our CURL session with the BOSS query URL
	$ch = curl_init($pre . urlencode($_GET['q']) . '?appid=' . $appid . $params);
	// We want the XML data to be returned (not echoed) so we can parse it
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// Execute the CURL request to retrieve the search results in XML format
	// By default, there will be a maximum of 10 results in one XML response
	// You can change this value and the result offset (for page 2, 3...) by adding more query paramaters
	// See BOSS documentation for more details
	$xml = curl_exec($ch);
	curl_close($ch);
	
	// variables for storing data from the XML
	$curtag = $y_link = $y_abstract = $y_title = '';
	// result counters
	$i = ($page - 1) * $results_per_page + 1;
	$totalhits = 0;
	
	// XML PARSING FUNCTIONS
	/////////////////////////////////////////////////////////////////////////////////////////
	// The parser found a start tag for an element
	function start_tag($parser, $name, $attribs)
	{ 
		global $curtag, $totalhits; 
		// Save the current tag name so we know where we are
		$curtag = $name;
		// Get the total hits from the <resultset_web> tag
		if($curtag == 'RESULTSET_WEB')
			$totalhits = intval($attribs['TOTALHITS']);
	}
	// The parser found some character data inside an element
	function tag_data($parser, $data)
	{
		global $curtag, $y_link, $y_abstract, $y_title;
		// If we are inside an <abstract> <title> or <url> tag, save the data for display later
		// If you want the default keyword highlighting (bold) then don't strip_tags()
		if($curtag == 'ABSTRACT')
			$y_abstract = htmlentities(strip_tags($data), ENT_QUOTES, 'UTF-8');
		elseif($curtag == 'TITLE')
			$y_title = htmlentities(strip_tags($data), ENT_QUOTES, 'UTF-8');
		elseif($curtag == 'URL')
			$y_link = htmlentities(strip_tags($data), ENT_QUOTES, 'UTF-8');
	}
	// The parser found an end tag for an element
	function end_tag($parser, $name)
	{ 
		global $curtag, $y_link, $y_abstract, $y_title, $i;
		// If this is the end of a <result> element
		if($name == 'RESULT')
		{
			// Echo the result listing with URL, title, and abstract
			echo '<div class="result"><div>' . $i . '</div><a href="' . $y_link . '">' . 
				$y_title . '</a><br />' . $y_abstract . '</div>';
			// Reset the temporary data vars
			$y_link = '';
			$y_abstract = '';
			$y_title = '';
			// Increment result counter
			$i++;
		}
		// Reset the current tag name
		$curtag = ''; 
	}
	/////////////////////////////////////////////////////////////////////////////////////////

	// Create an XML parser
	$parser = xml_parser_create();
	// Setup our handler functions so the parser calls them when it finds tags and data
	xml_set_element_handler($parser, 'start_tag', 'end_tag');
	xml_set_character_data_handler($parser, 'tag_data');
	// Parse the search results XML. The results will be printed by the functions above
	// Remember, $xml is the variable where the XML data was stored from the CURL response
	xml_parse($parser, $xml);
	
	// Write the Prev and Next page links
	echo '<hr /><div style="float:right; margin-top:15px;">';
	if($page > 1)
		echo '<a href="?q=' . $query . '&page=' . ($page - 1) . '">< Prev</a> | ';
	echo 'Page ' . $page;
	if($i - 1 < $totalhits)
		echo ' | <a href="?q=' . $query . '&amp;page=' . ($page + 1) . '">Next ></a>';
	echo '</div>';
	
	// Write the number of results. Note that this may be more than shown on the first page
	echo '<div style="margin-top:15px;"><i>' . $totalhits . ' Total Result(s)';
	// Shout out to the BOSS folks
	echo ' | Powered by <a href="http://developer.yahoo.com/search/boss">Yahoo BOSS</a></i></div>';
}
?>
</body>
</html>
