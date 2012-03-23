<DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1" />
<title>Movie Search</title>
<style type="text/css">
/* SIMPLE STYLING FOR SEARCH RESULTS */
body { font-family:arial; font-size:12px; }
div.result { margin-bottom:15px; background-color:#efefef; padding:10px; }
div.result a { font-size:18px; font-weight:bold; }
div.result div { float:left; font-size:30px; font-weight:bold; margin-right:10px; color:#c0c0c0; }
</style>
</head>
<body>
<h1>Movie Search</h1>
<form action="" method="get">
<?php
echo 'Search for <input type="text" name="q" value="';

if(empty($_GET['q']))
  $_GET['q']="";

// Echo the query in the search text field 
$query = htmlentities($_GET['q'], ENT_QUOTES, 'UTF-8');
echo $query . '" />   <input type="submit" value="Go" /></form>';

// If there is a query
if(!empty($_GET['q']))
{
	echo '<hr />';
	$url='select * from boss.search where q="The Artist"  and sites="imdb.com,movies.yahoo.com,indiatimes.com" and ck="dj0yJmk9YWF3ODdGNWZPYjg2JmQ9WVdrOWVsWlZNRk5KTldFbWNHbzlNVEEyTURFNU1qWXkmcz1jb25zdW1lcnNlY3JldCZ4PTUz" and secret="a3d93853ba3bad8a99a175e8ffa90a702cd08cfa"';
	$res=getResultFromYQL($url);
	foreach ($res->query->results->bossresponse->web->results->result as $item) {
	 echo '<div class="result"><a href="'.$item->url.'">'.$item->title->content.'</a><br/>'.$item->abstract->content.'<br><font color="green">'.$item->dispurl->content.'</font></div>';
	}

}


/**
 * Function to get results from YQL
 *
 * @param String $yql_query - The YQL Query
 * @param String $env - Environment in which the YQL Query should be executed. (Optional)
 *
 * @return object response
 */
function getResultFromYQL($yql_query) {
    $yql_base_url = "http://query.yahooapis.com/v1/public/yql";
    $yql_query_url = $yql_base_url . "?q=" . urlencode($yql_query);
    $yql_query_url .= "&format=json&env=store://datatables.org/alltableswithkeys";

    $session = curl_init($yql_query_url);
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($session, CURLOPT_PROXY, '10.3.100.209:8080');

    $json = curl_exec($session);
    curl_close($session);

    return json_decode($json);
}
?>

