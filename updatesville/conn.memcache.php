<?php

$MEMCACHE = new Memcache;
@$MEMCACHE->connect('localhost', 11211);

function mem_get($key) {
   if($GLOBALS['MEMCACHE']) {
      return $GLOBALS['MEMCACHE']->get($key);
   }
   return null;
}
function mem_set($key, $value, $expire=0) {
   if($GLOBALS['MEMCACHE']) {
      return $GLOBALS['MEMCACHE']->set($key, $value, 0, $expire);
   }
   return null;
}

?>
