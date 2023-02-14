<script language="JavaScript">
	var metadata = new Array();
	function showMeta(id) {
		alert(metadata[id]);
	}
</script>
<style>
	.codeValue { width: 21%; }
	.smallValue{ width: 7%; } /* for Type and Status */
	.nameValue { width: 35%; }
	.descValue { width: 30%; }
</style>
<div class="body">
	<div class="title">Products</div>
	<a href="/_product/edit">New Product</a>
	<table class="body">
	<tr><th class="label codeValue">Code</th>
		<th class="label smallValue">Type</th>
		<th class="label smallValue">Status</th>
		<th class="label nameValue">Name</th>
		<th class="label descValue">Description</th>
		<th class="label smallValue">Object</th>
	</tr>
<?php
foreach ($products as $product) { 
    if (isset($greenbar) && $greenbar) $greenbar = ''; else $greenbar = ' greenbar';
?>
	<tr><td class="value codeValue<?=$greenbar?>"><a href="/_product/edit/<?=$product->code?>"><?=$product->code?></a></td>
		<td class="value smallValue<?=$greenbar?>"><?=isset($product->type) ? $product->type : ''?></td>
		<td class="value smallValue<?=$greenbar?>"><?=isset($product->status) ? $product->status : ''?></td>
		<td class="value nameValue<?=$greenbar?>"><?=isset($product->name) ? $product->name : ''?></td>
		<td class="value descValue<?=$greenbar?>"><?=isset($product->short_description) ? $product->short_description : ''?></td>
		<td class="value smallValue<?=$greenbar?>"><input type="button" name="btn_show_<?=$product->id?>" onclick="showMeta(<?=$product->id?>)" value="Show" /></td>
		<script language="JavaScript">
			metadata[<?=$product->id?>] = "<?php foreach (get_object_vars($product) as $key => $value) { print "$key=$value\\n"; } ?>";
		</script>
	</tr>
<?php	
	} 
	?>
	</table>
</div>
