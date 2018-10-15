<div class="breadcrumbs">
<a href="/_engineering/home">Engineering</a> > Products
</div>
<h2 style="display: inline-block;">Products</h2>
<a class="button more" href="/_engineering/product">New Product</a>



<!--	START First Table -->
	<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 20%;">Code</div>
		<div class="tableCell" style="width: 25%;">Title</div>
		<div class="tableCell" style="width: 55%;">Description</div>
	</div>
<?php
	foreach ($products as $product) {
?>
	<div class="tableRow">
		<div class="tableCell">
			<a href="/_engineering/product/<?=$product->code?>"><?=$product->code?></a>
		</div>
		<div class="tableCell">
			<?=$product->title?>
		</div>
		<div class="tableCell">
			<?=$product->description?>
		</div>
	</div>
<?php	} ?>
</div>
<!--	END First Table -->

