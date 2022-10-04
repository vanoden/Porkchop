<?php
	if ($_REQUEST['flush']) {
		if ($GLOBALS['_CACHE_']->flush()) {
			print "Cache flushed";
		}
		else {
			print "Flush failed: ".$GLOBALS['_CACHE_']->error();
		}
	}
?>
<table>
<?php
	$keys = $GLOBALS['_CACHE_']->keys();

	foreach ($keys as $key) {
?>
<tr><td><a href="/_admin/memcached_item?key=<?=$key?>"><?=$key?></a></td></tr>
<?php	} ?>
</table>
