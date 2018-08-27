<style>
	td.column_code {
		width: 140px;
	}
	td.column_title {
		width: 220px;
	}
	td.column_date {
		width: 160px;
	}
	td.column_person {
		width: 160px;
	}
	td.column_status {
		width: 120px;
	}
	a.more {
		position: relative;
		display: block;
		font-weight: bold;
		padding-left: 15px;
		margin-top: 4px;
		font-size: 20px;
	}
	div.title {
		float: left;
		margin-right: 10px;
	}
	table.body {
		clear: both;
	}
</style>
<div class="title">Products</div>
<a class="more" href="/_engineering/product">New Product</a>
<table class="body">
<tr><td class="label column_code">Code</td>
	<td class="label column_title">Title</td>
	<td class="label" style="width: 600px">Description</td>
</tr>
<?php
	foreach ($products as $product) {
?>
<tr><td class="value"><a href="/_engineering/product/<?=$product->code?>"><?=$product->code?></a></td>
	<td class="value"><?=$product->title?></td>
	<td class="value"><?=$product->description?></td>
<?php	} ?>
</table>