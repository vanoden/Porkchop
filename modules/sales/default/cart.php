<!-- Page Header -->
<?=$page->showAdminPageInfo() ?>
<!-- End Page Header -->

<?php
if ($order->id > 0) {
	$salesOrderStatus = strtoupper((string)($order->status ?? 'NEW'));
	$salesOrderStatusLabels = array(
		'NEW' => 'New',
		'QUOTE' => 'Quoted',
		'APPROVED' => 'Approved',
		'CANCELLED' => 'Cancelled',
		'ACCEPTED' => 'Accepted',
		'COMPLETE' => 'Complete',
	);
	$salesOrderStatusHints = array(
		'NEW' => 'Draft order — not yet quoted or approved.',
		'QUOTE' => 'Quote has been prepared for the customer.',
		'APPROVED' => 'Order approved and sent for fulfillment.',
		'CANCELLED' => 'This order has been cancelled.',
		'ACCEPTED' => 'Order accepted by the vendor.',
		'COMPLETE' => 'Billing, fulfillment, and receipt are closed.',
	);
	$salesOrderStatusLabel = $salesOrderStatusLabels[$salesOrderStatus] ?? $salesOrderStatus;
	$salesOrderStatusHint = $salesOrderStatusHints[$salesOrderStatus] ?? '';
	$salesOrderStatusClass = strtolower($salesOrderStatus);
}
?>

<?php if ($order->id > 0) { ?>
<div class="sales-order-status-banner sales-order-status-banner--<?=htmlspecialchars($salesOrderStatusClass, ENT_QUOTES, 'UTF-8')?>">
	<div class="sales-order-status-banner__main">
		<span class="sales-order-status-banner__label">Order Status</span>
		<span class="sales-order-status-badge sales-order-status-badge--<?=htmlspecialchars($salesOrderStatusClass, ENT_QUOTES, 'UTF-8')?>"><?=htmlspecialchars($salesOrderStatusLabel, ENT_QUOTES, 'UTF-8')?></span>
		<?php if ($salesOrderStatusHint !== '') { ?>
		<span class="sales-order-status-banner__hint"><?=htmlspecialchars($salesOrderStatusHint, ENT_QUOTES, 'UTF-8')?></span>
		<?php } ?>
	</div>
	<div class="sales-order-status-banner__meta">
		<span class="sales-order-status-banner__code-label">Order</span>
		<span class="sales-order-status-banner__code"><?=htmlspecialchars((string)$order->code, ENT_QUOTES, 'UTF-8')?></span>
	</div>
</div>
<?php } ?>

<script>
	function salesCartForm() {
		return document.getElementById('salesCartForm');
	}
	function submitCartAction(action) {
		var form = salesCartForm();
		if (!form) return false;
		form.btn_submit.value = action;
		form.submit();
		return false;
	}
	function setOrderStatus(status) {
		var form = salesCartForm();
		if (!form) return false;
		form.new_status.value = status;
		form.submit();
		return true;
	}
	function update(elem) {
		var form = salesCartForm();
		if (!form) return;
		if (elem) form.active_element.value = elem.name;
		form.btn_submit.value = '';
		form.submit();
	}
	function addProduct() {
		update();
	}
	function removeItem(itemid) {
		var form = salesCartForm();
		if (!form) return;
		form.remove_item.value = itemid;
		form.btn_submit.value = '';
		form.submit();
	}
</script>

<div id="sales_cart_form" class="sales-cart-page clear-both">
	<form id="salesCartForm" method="post" action="/_sales/cart<?php if ($order->id > 0 && !empty($order->code)) print '/'.htmlspecialchars($order->code, ENT_QUOTES, 'UTF-8'); ?>" class="sales-cart-form">
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
	  <input id="order_id" type="hidden" name="order_id" value="<?=$order->id?>" />
	  <input id="btn_submit" type="hidden" name="btn_submit" value="" />
	  <input id="new_status" type="hidden" name="new_status" />
	  <input id="remove_item" type="hidden" name="remove_item" />
	  <input id="active_element" type="hidden" name="active_element" value="<?=htmlspecialchars($_REQUEST['active_element'] ?? '', ENT_QUOTES, 'UTF-8')?>" />

	  <div class="tableBody sales-cart-order-info">
<?php if ($order->id > 0) { ?>
	    <div class="tableRowHeader">
        <div class="tableCell">Created On</div>
		    <div class="tableCell">Created By</div>
		    <div class="tableCell">Status</div>
		    <div class="tableCell">Shipping Location</div>
	    </div>
      <div class="tableRow">
        <div class="tableCell"><?=$order->date_created()?></div>
        <div class="tableCell"><?php if ($order->salesperson_id > 0) { $salesperson = $order->salesperson(); echo $salesperson->full_name(); } else echo "N/A"; ?></div>
        <div class="tableCell">
          <span class="sales-order-status-badge sales-order-status-badge--<?=htmlspecialchars($salesOrderStatusClass, ENT_QUOTES, 'UTF-8')?> sales-order-status-badge--compact"><?=htmlspecialchars($salesOrderStatusLabel, ENT_QUOTES, 'UTF-8')?></span>
        </div>
        <div class="tableCell">
          <select id="shipping_location" name="shipping_location" onchange="update()">
            <option value="">Select</option>
            <?php		foreach ($locations as $select_loc) { ?>
            <option value="<?=$select_loc->id?>"<?php if ($select_loc->id == $form["shipping_location_id"]) print " selected"; ?>><?=$select_loc->name?></option>
            <?php		} ?>
          </select>
        </div>
      </div>
	    <div class="tableRow">
        <div class="tableCell">Organization</div>
        <div class="tableCell">Customer</div>
        <div class="tableCell">Billing Location</div>
        <div class="tableCell">Shipping Vendor</div>
      </div>
      <div class="tableRow">
        <div class="tableCell">
          <select id="organization_id" name="organization_id" class="input value" onchange="update()">
            <option value="">Select</option>
            <?php	foreach ($organizations as $select_org) { ?>
            <option value="<?=$select_org->id?>"<?php if ($select_org->id == $form["organization_id"]) print " selected";?>><?=$select_org->name?></option>
            <?php	} ?>
				  </select>
        </div>
        <div class="tableCell">
          <select id="customer_id" name="customer_id" class="input value" onchange="update()">
            <option value="">Select</option>
            <?php		foreach ($customers as $select_cust) { ?>
            <option value="<?=$select_cust->id?>"<?php if ($select_cust->id == $form["customer_id"]) print " selected"; ?>><?=$select_cust->full_name();?></option>
            <?php		} ?>
          </select>
        </div>
        <div class="tableCell">
          <select id="billing_location" name="billing_location" onchange="update()">
            <option value="">Select</option>
            <?php		foreach ($locations as $select_loc) { ?>
            <option value="<?=$select_loc->id?>"<?php if ($select_loc->id == $form["billing_location_id"]) print " selected"; ?>><?=$select_loc->name?></option>
            <?php		} ?>
          </select>
        </div>
        <div class="tableCell">
          <select name="shipping_vendor_id" id="shipping_vendor_id" onchange="update()">
            <option value="">Select</option>
            <?php		foreach ($shippingVendors as $select_vend) { ?>
            <option value="<?=$select_vend->id?>"<?php if ($select_vend->id == $form["shipping_vendor_id"]) print " selected"; ?>><?=$select_vend->name?></option>
            <?php		} ?>
          </select>
        </div>
      </div>
<?php } else { ?>
	    <div class="tableRowHeader">
        <div class="tableCell">Organization</div>
        <div class="tableCell">Customer</div>
        <div class="tableCell">Billing Location</div>
        <div class="tableCell">Shipping Location</div>
        <div class="tableCell">Shipping Vendor</div>
      </div>
      <div class="tableRow">
        <div class="tableCell">
          <select id="organization_id" name="organization_id" class="input value" onchange="update()">
            <option value="">Select</option>
            <?php	foreach ($organizations as $select_org) { ?>
            <option value="<?=$select_org->id?>"<?php if ($select_org->id == $form["organization_id"]) print " selected";?>><?=$select_org->name?></option>
            <?php	} ?>
				  </select>
        </div>
        <div class="tableCell">
          <select id="customer_id" name="customer_id" class="input value" onchange="update()">
            <option value="">Select</option>
            <?php		foreach ($customers as $select_cust) { ?>
            <option value="<?=$select_cust->id?>"<?php if ($select_cust->id == $form["customer_id"]) print " selected"; ?>><?=$select_cust->full_name();?></option>
            <?php		} ?>
          </select>
        </div>
        <div class="tableCell">
          <select id="billing_location" name="billing_location" onchange="update()">
            <option value="">Select</option>
            <?php		foreach ($locations as $select_loc) { ?>
            <option value="<?=$select_loc->id?>"<?php if ($select_loc->id == $form["billing_location_id"]) print " selected"; ?>><?=$select_loc->name?></option>
            <?php		} ?>
          </select>
        </div>
        <div class="tableCell">
          <select id="shipping_location" name="shipping_location" onchange="update()">
            <option value="">Select</option>
            <?php		foreach ($locations as $select_loc) { ?>
            <option value="<?=$select_loc->id?>"<?php if ($select_loc->id == $form["shipping_location_id"]) print " selected"; ?>><?=$select_loc->name?></option>
            <?php		} ?>
          </select>
        </div>
        <div class="tableCell">
          <select name="shipping_vendor_id" id="shipping_vendor_id" onchange="update()">
            <option value="">Select</option>
            <?php		foreach ($shippingVendors as $select_vend) { ?>
            <option value="<?=$select_vend->id?>"<?php if ($select_vend->id == $form["shipping_vendor_id"]) print " selected"; ?>><?=$select_vend->name?></option>
            <?php		} ?>
          </select>
        </div>
      </div>
<?php } ?>
		</div><!-- end table -->

	<!-- START Order Items -->
	<h3>Order Items</h3>
	<?php	if ($order->id > 0) { ?>
	<div class="tableBody sales-cart-items">
		<div class="tableRowHeader">
			<div class="tableCell sales-cart-col-code">Product Code</div>
			<div class="tableCell sales-cart-col-serial">Serial Number</div>
			<div class="tableCell sales-cart-col-description">Description</div>
			<div class="tableCell sales-cart-col-qty">QTY</div>
			<div class="tableCell sales-cart-col-price">Price</div>
			<div class="tableCell sales-cart-col-total">TOTAL</div>
			<div class="tableCell sales-cart-col-delete"><span class="sales-cart-color-666">Delete</span></div>
		</div>
	  <?php	foreach ($orderItems as $item) { ?>
		<div class="tableRow">
			<div class="tableCell sales-cart-col-code">
				<input type="hidden" name="items[<?=$item->id?>]" value="1" />
				<?=$item->product()->code?>
			</div>
			<?php	if ($item->product()->type != 'unique') { ?>
			  <div class="tableCell sales-cart-col-serial">N/A</div>
			<?php	} else { ?>
			  <div class="tableCell sales-cart-col-serial"><input type="text" name="serial_number[<?=$item->id?>]" value="<?=$item->serial_number?>" onchange="update(this)" /></div>
			<?php	} ?>
			<div class="tableCell sales-cart-col-description"><input type="text" class="sales-cart-description" name="description[<?=$item->id?>]" value="<?=htmlspecialchars(strip_tags($item->description), ENT_QUOTES, 'UTF-8')?>" onchange="update()" /></div>
		  <?php	if ($item->product()->type == 'unique') { ?>
			  <div class="tableCell sales-cart-col-qty"><span class="sales-cart-text-right"><?=number_format($item->quantity,0)?></span></div>
		  <?php	} else { ?>
	 		  <div class="tableCell sales-cart-col-qty"><input id="quantity[<?=$item->id?>]" name="quantity[<?=$item->id?>]" class="sales-cart-qty-input" value="<?=number_format($item->quantity,0)?>" onchange="update()" /></div>
		  <?php	} ?>
			<div class="tableCell sales-cart-col-price"><span class="sales-cart-price-wrap">$<input id="price[<?=$item->id?>]" class="sales-cart-price-input" type="text" value="<?=$item->unit_price?>" name="price[<?=$item->id?>]" onchange="update()" /></span></div>
			<div class="tableCell sales-cart-col-total sales-cart-text-right">$ <?=number_format($item->total(),2)?></div>
			<div class="tableCell sales-cart-col-delete"><input type="image" name="btn_remove" src="/img/icons/icon_tools_trash_active.svg" onclick="removeItem(<?=$item->id?>);" alt="Remove item" /></div>
	  </div>
    <?php	} ?>
		<div class="tableRow">
			<div class="tableCell sales-cart-col-code">
				<select id="new_item" name="new_item" class="input value" onchange="addProduct()">
					<option value="">Add Product</option>
		      <?php	foreach ($products as $product) { ?>
					<option value="<?=$product->id?>"><?=$product->code?></option>
		      <?php	} ?>
				</select>
			</div>
			<div class="tableCell sales-cart-col-serial">&nbsp;</div>
			<div class="tableCell sales-cart-col-description">&nbsp;</div>
			<div class="tableCell sales-cart-col-qty">&nbsp;</div>
			<div class="tableCell sales-cart-col-price">&nbsp;</div>
			<div class="tableCell sales-cart-col-total">&nbsp;</div>
			<div class="tableCell sales-cart-col-delete">&nbsp;</div>
		</div>
		<div class="tableRowHeader sales-cart-items-footer">
			<div class="tableCell sales-cart-col-code"></div>
			<div class="tableCell sales-cart-col-serial"></div>
			<div class="tableCell sales-cart-col-description"></div>
			<div class="tableCell sales-cart-col-qty"></div>
			<div class="tableCell sales-cart-col-price">Total Quote:</div>
			<div class="tableCell sales-cart-col-total sales-cart-text-right">$<?=number_format($order->total(),2)?></div>
			<div class="tableCell sales-cart-col-delete"></div>
		</div>
	</div>
    <!-- END Order Items -->
<?php } ?>

	<div class="sales-cart-actions filter-bar">
		<div class="button-group filter-bar__actions">
<?php if ($order->id < 1) { ?>
			<button type="button" class="button" onclick="submitCartAction('Create Order')">Create Order</button>
<?php } else { ?>
			<button type="button" class="button" onclick="submitCartAction('Save For Later')">Save For Later</button>
			<button type="button" class="button btn-secondary" onclick="submitCartAction('Create a Quote')">Create a Quote</button>
			<button type="button" class="button" onclick="submitCartAction('Approve Order')">Approve Order</button>
			<button type="button" class="button btn-secondary" onclick="submitCartAction('Cancel Order')">Cancel Order</button>
<?php } ?>
		</div>
	</div>
	</form>
</div>
