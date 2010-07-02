<?php
   // Include the YSP PHP SDK to access the library.
   // You need to change the path of Yahoo.inc.
   include_once("lib/Yahoo.inc");
   
   // Define constants to store your Consumer Key and Consumer Secret.
   define("CONSUMER_KEY","<your consumer key>");
   define("CONSUMER_SECRET","<your consumer secret>");   

  // Enable debugging. Errors are reported to Web server's error log.
   YahooLogger::setDebug(true);

   // Initializes session and redirects user to Yahoo! to sign in and 
   // then authorize app
   $yahoo_session = YahooSession::requireSession(CONSUMER_KEY, CONSUMER_SECRET);
   if ($yahoo_session == NULL) {
       fatal_error("yahoo_session");
   }


  $data_orig=$yahoo_session->query('select * from social.connections.updates where guid=me');
  $updates=($data_orig->query->results->update);

  $data=$yahoo_session->query('select * from google.translate where q in (select title from social.connections.updates where guid=me) and target="hi"');

  foreach($data->query->results->translatedText as $i=>$update)
  {
    echo "<div style='height:70px;padding-left:10px;'><div style='float:left;padding:2px;'> <a href='".$updates[$i]->profile_profileUrl."'><img style='border:0;' src ='".$updates[$i]->profile_displayImage."'> </a></div><div> <a href='".$updates[$i]->profile_profileUrl."'>".$updates[$i]->profile_nickname."</a> $update </div></div>";
  }
?>
