<?  if (! role('product manager'))
    {
        print "<span class=\"form_error\">You are not authorized for this view!</span>";
        return;
    }
?>
<script language="JavaScript">
	var metadata = new Array();
	function showMeta(id) {
		alert(metadata[id]);
	}
</script>
<style>
	.smallValue{ width: 8%; }
	.codeValue { width: 15%; }
	.nameValue { width: 24%; }
	.descValue { width: 53%; }
</style>
<div class="body">
	<h2>Products</h2>
	<table class="body">
	<tr><th class="label codeValue">Code</th>
		<th class="label smallValue">Type</th>
		<th class="label smallValue">Status</th>
		<th class="label nameValue">Name</th>
		<th class="label descValue">Description</th>
		<th class="label smallValue">Object</th>
	</tr>
<?	foreach ($products as $product) { ?>
	<tr><td class="value codeValue<?=$greenbar?>"><a href="/_product/edit/<?=$product->code?>"><?=$product->code?></a></td>
		<td class="value smallValue<?=$greenbar?>"><?=$product->type?></td>
		<td class="value smallValue<?=$greenbar?>"><?=$product->status?></td>
		<td class="value nameValue<?=$greenbar?>"><?=$product->name?></td>
		<td class="value descValue<?=$greenbar?>"><?=$product->short_description?></td>
		<td class="value smallValue<?=$greenbar?>"><input type="button" name="btn_show_<?=$product->id?>" onclick="showMeta(<?=$product->id?>)" value="Show" /></td>
		<script language="JavaScript">
			metadata[<?=$product->id?>] = "<? foreach (get_object_vars($product) as $key => $value) { print "$key=$value\\n"; } ?>";
		</script>
	</tr>
<?	
		if (isset($greenbar) && $greenbar)
			$greenbar = '';
		else
			$greenbar = ' greenbar';
	} ?>
	</table>
</div>
