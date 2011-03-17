<?php
   // Include the YSP PHP SDK to access the library.
   // You need to change the path of Yahoo.inc.
   include_once("lib/Yahoo.inc");
   include "leaders.php";
   include_once "conn.memcache.php";  
   include "track.php";
   // Define constants to store your Consumer Key and Consumer Secret.
   define("CONSUMER_KEY","dj0yJmk9T1BHckVOWmVWZWJNJmQ9WVdrOU4wSk9VbXQwTkRJbWNHbzlNVGsyTkRZeU5qSS0mcz1jb25zdW1lcnNlY3JldCZ4PTdl");
   define("CONSUMER_SECRET","c42a4292e6ebdeca8fa69dd12d0480e636140346");
   define("APP_ID","7BNRkt42");

   // Enable debugging. Errors are reported to Web server's error log.
 //  YahooLogger::setDebug(true);

   // Initializes session and redirects user to Yahoo! to sign in and 
   // then authorize app
   $yahoo_session = YahooSession::requireSession(CONSUMER_KEY, CONSUMER_SECRET,APP_ID); 
   if ($yahoo_session == NULL) {
       fatal_error("yahoo_session");
   }

 
  $fren_updates_key="fren:v1:updates";
  $fren_updates= mem_get($fren_updates_key);
  if (!$fren_updates)
  { 
    $data_orig=$yahoo_session->query('select * from social.updates.search where source="APP.7BNRkt42" limit 10');
    $fren_updates=$data_orig->query->results->update;
    mem_set($fren_updates_key,$fren_updates,3600);
  }
   
 
  $leaders=doEverything($yahoo_session);
  foreach($leaders as $leader)
   {
    $me=$leader; break;
   }

  $badge_names = array("agg.twitter" => "Tweetoo",
      "avatars" => "Beharupia",
      "buzz" => "Buzzooka",
      "y.mybloglog" => "BloggY!",
      "socialite" => "Socialite",
      "newbie1" => "Newbie"
      );

  $badge_desc = array("agg.twitter" => "You've shared 5 twitter updates!",
      "avatars" => "You've creating a Yahoo! avatar",
      "buzz" => "You've buzed up 3 times",
      "y.mybloglog" => "You've sharing 10 blog updates",
      "socialite" => "You've updates from 8 different sources",
      "newbie1" => "Congrats! On sharing your updates"
      );

  
 krsort($leaders);
 track($me["nickname"],$me["guid"],$me["count"]);
// $me=array("count"=>20);
  ?>
<style>
h1 {
color:#E57F3E;
font-size:18px;
font-weight:bold;
}
#maincolumn{float:left;margin-left:15px;width:270px;}
a {color:#0077BB;}
h2  {
background-color:#FFFFFF;
border-bottom:2px solid #5A7992;
clear:both;
color:#5A7992;
display:block;
margin:10px 0;
padding:5px 0;font-size:16px;
}
#sidecolumn{float:left;
margin-left:15px;padding:10px;
width:430px;}
</style>
<script type="text/javascript">
function toggle()
{
element="help";
                document.getElementById(element).style.display="none"; 
}
</script>
<div id="help" style="margin: 5px; padding: 10px; background-color: rgb(255, 255, 204);">Increase your score by <a href="http://pulse.yahoo.com/">sharing</a> more updates with Yahoo!, win badges and compete with your friends! <div style="float: right; width: 5px; margin-top: -10px;"><a style="text-decoration: none;" onclick="javascript:toggle();" href="javascript:toggle();">X</a></div></div>
<div id="maincolumn">
   <h1>Hi  <yml:name uid="viewer" linked="false" capitalize="true" />, Welcome to Updatesville</h1>
<div style="">
  <div style="float:left;margin-bottom:10px;"><yml:profile-pic uid="viewer" width="48" linked="false" /></div><div style="float:left;margin-left:15px;">   <p>Your Total Score: <?php echo $me['count'];?></p> </div>
</div>
<div> 
<h2>Leader Board</h2>
<table style="background: none repeat scroll 0% 0% white;">
<?php 
$i=1;
foreach($leaders as $leader) {
echo "<tr><td>$i</td><td> <img src='".$leader["image"]."'></td><td><p style='padding-left:10px;'><a href='".$leader["url"]."'>".$leader["nickname"]."</a><br>Score:".$leader["count"]."</p></td></tr>";
$i++;
}
?>
</table>
</div>
</div>
<div id ="sidecolumn">
<h2>My Badges</h2>

<div style="padding:10px;">
<?php foreach($me['badges'] as $badge => $val) {
echo '<img height="67" title="'.$badge_names[$badge].': '.$badge_desc[$badge].'" src="icons/'.$badge.'.png" alt="'.$badge_names[$badge].'"/> ';
}
?>
</div>
<h2>Recent Activity</h2>
<div style="clear:both;height:10px;">&nbsp;</div>
  <?php
 $updates=$fren_updates;
foreach($updates as $i=>$update)
{
    echo "<div style='height:100px;font-size:90%;padding-left:10px;'><div style='float:left;padding-right:5px;'> <a href='".$updates[$i]->profile_profileUrl."'><img style='border:0;' width='24px' src ='".$updates[$i]->profile_displayImage."'> </a></div><div> <a href='".$updates[$i]->profile_profileUrl."' style='text-decoration:none;'>".$updates[$i]->profile_nickname."</a> ".$update->title."<div style='margin-left:30px;'> <div style='float:left;'><img height='67px' width='60px' src='".$update->imgURL."'/> &nbsp;</div> <div style='float:left;color:gray;'>".$update->description."</div></div> </div></div>";

}
?></div>
