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
	.smallValue{
		width: 100px;
	}
	.codeValue {
		width: 200px;
	}
	.nameValue {
		width: 300px;
	}
	.descValue {
		width: 700px;
	}
	table.body {
		border: 1px solid #333333;
	}
</style>
<div class="body">
	<span class="title">Products</span>
	<table class="body">
	<tr><td class="label codeValue">Code</td>
		<td class="label smallValue">Type</td>
		<td class="label smallValue">Status</td>
		<td class="label nameValue">Name</td>
		<td class="label descValue">Description</td>
		<td class="label smallValue">Object</td>
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
