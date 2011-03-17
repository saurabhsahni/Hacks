<?php 
function track($user,$guid="",$score=0){
$link = mysql_connect('localhost', 'root', 'new');
if($link)
{

mysql_select_db('updatesville',$link);
mysql_query("insert into stats (`user`,`guid`,`score`) values ('$user','$guid',$score)");
}
}

function record($guid,$badge)
{
$link = mysql_connect('localhost', 'root', 'new');
if($link)
{

mysql_select_db('updatesville',$link);
$result = mysql_query("SELECT * FROM badges where guid='$guid' and source='$badge'", $link);
$num_rows = mysql_num_rows($result);
if ($num_rows)
  return 0;

return mysql_query("insert into badges (`guid`,`source`) values ('$guid','$badge') on duplicate key update `source`='$badge'");

}

}

?>
