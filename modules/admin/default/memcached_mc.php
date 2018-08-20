<table>
<?php
	$keys = $GLOBALS['_CACHE_']->keys();

	foreach ($keys as $key) {
?>
<tr><td><a href="/_admin/memcached_item?key=<?=$key?>"><?=$key?></a></td></tr>
<?php	} ?>
</table>