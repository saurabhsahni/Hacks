<?php
//Guid - mine:  PWO54G3PC2K4LKI5IWQL5OZJBI
//$user = $session->getUser("BLDIDQLRSQGQIJH4R57C532AB4");

// Fetch the updates for the current user.
//$updates = $user->getUpdates();
//SELECT * FROM social.updates WHERE guid='BLDIDQLRSQGQIJH4R57C532AB4' AND pubDate > '1277729455'
function doEverything($session)
{
  $user = $session->getSessionedUser();
  $current_timestamp = time();
  $yesterday = $current_timestamp - 1*24*60*60;
  $last_week = $current_timestamp - 7*24*60*60;
  $last_month = $current_timestamp - 30*24*60*60;
  $last_year = $current_timestamp - 365*24*60*60;
  //$updatesQuery = "SELECT * FROM social.updates WHERE guid='{$user->guid}' AND pubDate > '$last_week'";
  $updatesQuery = "SELECT * FROM social.updates(100) WHERE guid='{$user->guid}'";
  $updates = $session->query($updatesQuery);
  //error_log(print_r(countUpdatesPerGuid($updates), true));

  /*error_log($updates->query->count);
    error_log($updates->query->results->update[0]->profile_nickname);
    error_log($updates->query->results->update[0]->profile_guid);
    error_log($updates->query->results->update[0]->profile_displayImage);
   */
  //print_r($updates);
  // Fetch the updates for the contacts of the current user.
  $contactUpdatesQuery = "select * from social.contacts.updates(0,10) where guid=me and updates='100';";//select * from social.connections.updates(0,100) where guid='{$user->guid}' AND updates = '200'";
  $mem_key_contacts="contacts:v9:updates:{$user->guid}";
  $contactUpdates= mem_get($mem_key_contacts);
  if (!$contactUpdates)
  {
    $contactUpdates = $session->query($contactUpdatesQuery);
    mem_set($mem_key_contacts,$contactUpdates,3600);
  }
  //  print_r($contactUpdates);
  $distinct_sources = array();
  $distinct_sources[0] = array();
  $allMyUpdates = countUpdatesForMe($updates,&$distinct_sources[0]);
 // error_log(print_r($allMyUpdates,true));
  $distinct_sources1 = array();
  $allContactUpdates = countUpdatesPerGuid($contactUpdates, &$distinct_sources1);
 $allDistinctSources = array_merge($distinct_sources, $distinct_sources1);
  $allUpdates = array_merge($allMyUpdates, $allContactUpdates);
  //   error_log(print_r($allMyUpdates, true));
  //    error_log(print_r($allContactUpdates, true));

  $badges=getMyBadges1($distinct_sources);
  foreach($allUpdates as $key=>$person)
  {
//    $badges = getMyBadges($person['guid'], $session);
    if(count($allDistinctSources[$key]) >= 8)
    {
      $badges = array_merge($badges, array("socialite" => count($allDistinctSources[$key])));
    }
    $allUpdates[$key]['badges'] = $badges;
    break;
  }
  foreach($allUpdates as $key=>$person)
  {
   $newkey=$person['count']*100+$key;
   $newallUpdates[$newkey]=$person;
   }
//  return ($newallUpdates);
  //$badges = getMyBadges($user->guid,$session);
  //echo json_encode($badges);
 // $badges = getMyBadges($user->guid, $session);
//  return $badges;
    sendVitality($user,$session,$badges);
    return $newallUpdates;
}
//
// Returns an array which contains badges of the user
//
function getMyBadges($guid,$session)
{
  $badge_thresholds = array("agg.twitter" => 5,
      "avatars" => 1,
      "buzz" => 1,
      "y.mybloglog" => 10
      );


  $badges = array();
  foreach($badge_thresholds as $source => $threshold)
  {
    $updatesQuery = "SELECT * FROM social.updates(200) WHERE guid='$guid' and source='$source'";
    $updates = $session->query($updatesQuery);
    if($updates->query->count >= $threshold) 
    {
      $badges[$source] = $updates->query->count;
    }
  }
   echo count($badges);
  if(count($badges)<1)
    $badges["newbie1"]=1;
  return $badges;
}


function getMyBadges1($updates)
{ 
  $badge_thresholds = array("agg.twitter" => 5,
      "avatars" => 1,
      "buzz" => 3,
      "y.mybloglog" => 10
      );


  $badges = array();
  foreach($badge_thresholds as $source => $threshold)
  {
   //  echo $source.": ". $updates[0][$source]." thresh:".$threshold." ";
    if(array_key_exists($source,$updates[0]))
    if($updates[0][$source] >= $threshold)
    { //echo "here";
      $badges[$source] = $updates[$source];
    }
  }
  if(count($badges)<=1)
    $badges["newbie1"]=1;

  return $badges;
}



//
// Inserts Update into  Vitality 
//
function sendVitality($user, $session, $badges)
{ 
//echo "session";
//print_r($session);
  $badge_names = array("agg.twitter" => "Tweetoo",
      "avatars" => "Beharupia",
      "buzz" => "Buzzooka",
      "y.mybloglog" => "BloggY!",
      "socialite" => "Socialite",
      "newbie1" => "Newbie"
      );
  $badge_desc = array("agg.twitter" => "You've shared 5 twitter updates!",
      "avatars" => "You've created a Yahoo! avatar",
      "buzz" => "You've buzed up 3 times",
      "y.mybloglog" => "You've shared 10 blog updates",
      "socialite" => "You've updates from 8 different sources",
      "newbie1" => "Congrats! On sharing your updates"
      );

  foreach($badges as $source => $count)
  {
    $mem_key="{$user->guid}:$source";
    if(!record($user->guid,$source)) {
     continue;
}
    $title = "just unlocked the badge ".$badge_names[$source]." on Updatesville";
    $description = $badge_desc[$source];
    $imgURL = "http://hacks.saurabhsahni.com/updatesville/icons/$source.png";
    $imgWidth = "64";
    $imgHeight = "67";
    $iconURL = "http://hacks.saurabhsahni.com/updatesville/vitality_bigger2.gif";
    $link = "http://pulse.yahoo.com/y/apps/7BNRkt42/";

//    $updatesQuery = "INSERT INTO social.updates(guid,title,description,imgURL,imgWidth,imgHeight,iconURL,link) values ('{$user->guid}','$title','$description','$imgURL','$imgWidth','$imgHeight','$iconURL','$link')";
//    error_log($updatesQuery);
//    $updates = $session->query($updatesQuery);
    $updates = $user->insertUpdate(md5($source), $title, $link, $description, $imgURL, $imgWidth, $imgHeight, $iconURL, time());
    mem_set($mem_key,"1");
//    echo $updatesQuery;
//    print_r($updates);
//    error_log(print_r($updates,true));
  }
}


/**
 * Takes in the updates information, and returns an array of guids - with their associated information(profile pic, update counts, etc)
 *
 * @param $update Updates information
 * @return Array of guids with associated info
 */
function countUpdatesForMe($updates, $distinct_sources)
{
  $guids = array();
  $guid_me = array();
  $guid_me['count'] = 0;

  $results = $updates->query->results->update;

  
  $pattern = "/^agg*/";
  foreach($updates->query->results->update as $result)
  {
    if(array_key_exists($result->source,$distinct_sources))
      $distinct_sources[$result->source]++;
    else
      $distinct_sources[$result->source] = 1;
    //error_log(print_r($result, true));
    //error_log("{$result->profile_nickname}, {$result->profile_guid}, {$result->profile_displayImage}"); 
    //Giving three points for yahoo vitality events, and 1 point for offnetwork vitality
    if( preg_match($pattern, $result->source) )
      $guid_me['count']++;
    else
      $guid_me['count'] += 3;
  }

  $guid_me['guid'] = $result->profile_guid;
  $guid_me['image'] = $result->profile_displayImage;
  $guid_me['nickname'] = $result->profile_nickname;

  $guids[0]=$guid_me;
  return $guids;
}

function countUpdatesPerGuid($updates, $distinct_sources)
{
  //error_log(print_r($updates,true));
  $guids = array();
  $result = $updates->query->results->contact;
  $i = 0;
  $j=0;
  $pattern = "/^agg*/";
  //  error_log(print_r($result, true));
  foreach($updates->query->results->contact as $result)
  {
    $guids[$i] = array();
    $guids[$i]['count'] = 0;//$result->updates->count;
    foreach($result->updates->update as $update)
    {
      $distinct_sources[$i][$update->source] = 1;
      //error_log(print_r($update, true));
      if( preg_match($pattern, $update->source) )
        $guids[$i]['count']++;
      else
        $guids[$i]['count'] += 3;
    }
    $guids[$i]['guid'] = $result->guid;
    $guids[$i]['image'] = $update->profile_displayImage;
    $guids[$i]['nickname'] = $update->profile_nickname;
    $guids[$i]['url'] = $update->profile_profileUrl;
 
   $i++;
  }
  return $guids;
}

?>

