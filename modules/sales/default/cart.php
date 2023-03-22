<?=$page->showBreadcrumbs(); ?>
<?=$page->showTitle(); ?>
<?=$page->showMessages(); ?>
<style>
    fieldset {
        max-width: 500px;
    }
    form input[type=number] {
        max-width: 75px;
    }
    .tableCell {
       white-space: nowrap; 
    }
    .small-font {
        font-size:10px;
        display:inline;
    }
</style>
<script>
    function scrollToBottom (id) {
       var div = document.getElementById(id);
       div.scrollTop = div.scrollHeight - div.clientHeight;
    }
	function setOrderStatus(status) {
		document.forms[0].new_status.value = status;
		document.forms[0].method.value = 'update_status';
		document.forms[0].submit();
		return true;
	}
	function update(elem) {
		if (elem) document.forms[0].active_element.value = elem.name;
		document.forms[0].submit();
	}
	function addProduct() {
		document.forms[0].submit();
	}
	function removeItem(itemid) {
		document.getElementById('remove_item').value = itemid;
		document.forms[0].submit();
	}
	document.addEventListener("DOMContentLoaded", function() {
		scrollToBottom('sales_cart_form');
		var activeElem = "<?=$_REQUEST['active_element']?>";
		if (activeElem.length > 0) document.forms[0].activeElem.focus();
	});
</script>

<div id="sales_cart_form" style="clear: both">
	<form method="post" action="/_sales/cart">
	<input id="order_id" type="hidden" name="order_id" value="<?=$order->id?>" />
	<input id="new_status"	type="hidden" name="new_status" />
	<input id="remove_item" type="hidden" name="remove_item" />
	<input id="new_status" type="hidden" name="new_status" />
	<input id="active_element" type="hidden" />

	<table style="width: 1000px">
	<tr><td><div class="order_header"><span class="label">Created On</span><span class="value"><?=$order->date_created()?></div></td>
		<td><div class="order_header"><span class="label">Created By</span><span class="value"><?=$order->saleperson()?></div></td>
		<td><div class="order_header"><span class="label">Status</span><span class="value"><?=$order->status?></div></td>
	</tr>

	<tr><td><div class="order_header"><span class="label">Organization</span>
				<select id="organization_id" name="organization_id" class="input value" onchange="update()">
					<option value="">Select</option>
			<?php	foreach ($organizations as $select_org) { ?>
					<option value="<?=$select_org->id?>"<?php if ($select_org->id == $form["organization_id"]) print " selected";?>><?=$select_org->name?></option>
			<?php	} ?>
				</select>
			</div>
		</td>

		<td>&nbsp;</td>
		<td><div class="order_header"><span class="label">Customer</span>
				<select id="customer_id" name="customer_id" class="input value" onchange="update()">
					<option value="">Select</option>
			<?php		foreach ($customers as $select_cust) { ?>
					<option value="<?=$select_cust->id?>"<?php if ($select_cust->id == $form["customer_id"]) print " selected"; ?>><?=$select_cust->full_name();?></option>
			<?php		} ?>
				</select>
			</div>
		</td>
	</tr>

	<tr><td><div class="order_header"><span class="label">Billing Location</span>
				<select id="billing_location" name="billing_location" onchange="update()">
					<option value="">Select</option>
			<?php		foreach ($locations as $select_loc) { ?>
					<option value="<?=$select_loc->id?>"<?php if ($select_loc->id == $form["billing_location_id"]) print " selected"; ?>><?=$select_loc->name?></option>
			<?php		} ?>
				</select>
			</div>
		</td>

		<td><div class="order_header"><span class="label">Shipping Location</span>
				<select id="shipping_location" name="shipping_location" onchange="update()">
					<option value="">Select</option>
			<?php		foreach ($locations as $select_loc) { ?>
					<option value="<?=$select_loc->id?>"<?php if ($select_loc->id == $form["shipping_location_id"]) print " selected"; ?>><?=$select_loc->name?></option>
			<?php		} ?>
				</select>
			</div>
		</td>

		<td><div class="order_header"><span class="label">Shipping Vendor</span>
				<select name="shipping_vendor_id" id="shipping_vendor_id" onchange="update()">
					<option value="">Select</option>
			<?php		foreach ($shippingVendors as $select_vend) { ?>
					<option value="<?=$select_vend->id?>"<?php if ($select_vend->id == $form["shipping_vendor_id"]) print " selected"; ?>><?=$select_vend->name?></option>
			<?php		} ?>
				</select>
			</div>
		</td>
	</tr>
	</table>
	<!-- START Order Items -->
	<br><br><br>
	<p><span class="label">Order Items</span></p>
	<?php	if ($order->id > 0) { ?>
	<div class="tableBody min-tablet">
		<div class="tableRowHeader">
			<div class="tableCell" style="width: 10%;">Product Code</div>
			<div class="tableCell" style="width: 16%;">Serial Number</div>
			<div class="tableCell" style="width: 35%;">Description</div>
			<div class="tableCell" style="width: 3%;">QTY</div>
			<div class="tableCell" style="width: 15%;">Price</div>
			<div class="tableCell" style="width: 15%;">TOTAL</div>
			<div class="tableCell" style="width: 5%;"><span style="color: #666;">Delete</span></div>
		</div>
	<?php	foreach ($orderItems as $item) { ?>
		<div class="tableRow">
			<input type="hidden" name="items[<?=$item->id?>]" value="1" />
			<div class="tableCell"><?=$item->product()->code?></div>
			<?php	if ($item->product()->type != 'unique') { ?>
			<div class="tableCell">N/A</div>
			<?php	} else { ?> 
			<div class="tableCell"><input type="text" name="serial_number[<?=$item->id?>]" value="<?=$item->serial_number?>" onchange="update(this)" /></div>
			<?php	} ?>
			<div class="tableCell"><textarea style="max-height: 35px;" name="description[<?=$item->id?>]" onchange="update()"><?=$item->description?></textarea></div>
		<?php	if ($item->product()->type == 'unique') { ?>
			<div class="tableCell"><span class="value" style="text-align: right"><?=number_format($item->quantity,0)?></div>
		<?php	} else { ?>
	 		<div class="tableCell"><input id="quantity[<?=$item->id?>]" name="quantity[<?=$item->id?>]" style="width: 40px; textalign: right" value="<?=number_format($item->quantity,0)?>" onchange="update()" /></div>
		<?php	} ?>
			<div class="tableCell">$ <input id="price[<?=$item->id?>]" style="width: 90px; text-align: right" type="text" value="<?=$item->unit_price?>" name="price[<?=$item->id?>]" onchange="update()" /></div>
			<div class="tableCell">$ <?=number_format($item->total(),2)?></div>
			<div class="tableCell"><button type="submit" name="btn_remove" onclick="removeItem(<?=$item->id?>);">&#x2716;</button></div>
	    </div>
    <?php	} ?>
		<div class="tableRow">
			<div class="tableCell">
				<select id="new_item" name="new_item" class="input value" onchange="addProduct()">
					<option value="">Add Product</option>
		<?php	foreach ($products as $product) { ?>
					<option value="<?=$product->id?>"><?=$product->code?></option>
		<?php	} ?>
				</select>
			</div>
			<div class="tableCell">&nbsp;</div>
			<div class="tableCell">&nbsp;</div>
			<div class="tableCell">&nbsp;</div>
			<div class="tableCell">&nbsp;</div>
			<div class="tableCell">&nbsp;</div>
			<div class="tableCell">&nbsp;</div>
		</div>
		<div class="tableRowHeader">
			<div class="tableCell" style="width: 10%;"></div>
			<div class="tableCell" style="width: 16%;"></div>
			<div class="tableCell" style="width: 25%;"></div>
			<div class="tableCell" style="width: 3%;"></div>
			<div class="tableCell" style="width: 15%;">Total Quote:</div>
			<div class="tableCell" style="width: 15%;">$<?=number_format($order->total(),2)?></div>
			<div class="tableCell" style="width: 5%;"></div>
		</div>
	</div>
    <!-- END Order Items -->

	<input type="submit" class="continue_button" name="btn_submit" value="Save For Later" />
	<input type="submit" class="continue_button" name="btn_submit" value="&check; Create a Quote" />
	<input type="submit" class="continue_button" name="btn_submit" value="&plus; Approve Order" />
	<input type="submit" class="continue_button" name="btn_submit" value="&plus; Cancel Order" />
	<?php } ?>
	</form>
</div>
