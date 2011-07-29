<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Recent photos of openhackindia from YQL  with PHP </title>
    <style>li{   list-style: none;}</style>
</head>
<body>
<?php
$yql_url = 'http://query.yahooapis.com/v1/public/yql?';
$query = 'SELECT * FROM flickr.photos.search(20) WHERE text="openhackindia"';
$query_url = $yql_url . 'q=' . urlencode($query) . '&format=xml';

$photos = simplexml_load_file($query_url);
$result = build_photos($photos->results->photo);
echo $result;
?>
</body>
</html>
<?php
function build_photos($photos){
    $html = '<ul>';
    if (count($photos) > 0){
        foreach ($photos as $photo){
            $html .= '<li><a href="http://www.flickr.com/photos/'.
            $photo['owner'].'/'.$photo['id'].
                '"><img src="http://farm'.$photo['farm'].
            '.static.flickr.com/'.$photo['server'].
                '/'.$photo['id'].'_'.$photo['secret'].
                '.jpg" width="600"  alt="'.$photo['title'].
                '" /></a></li>';
        }
    } else {
        $html .= 'No Photos Found';
    }
    $html .= '</ul>';
    return $html;
}
?>

