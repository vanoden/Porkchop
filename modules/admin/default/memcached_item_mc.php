<pre>
<?php
	$cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$_REQUEST['key']);
	$object = $cache_item->get();
	print_r($object);
?>
</pre>