<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<h3>Order Count: <?=isset($page->isSearchResults) ? "[Matched Orders: ". count($orders)."]" : "";?></h3>

<?php if (!isset($page->isSearchResults)) { ?> <a class="button" href="/_sales/cart">New Order</a> <?php } ?>

<form action="/_sales/orders" method="post">

  <div id="search_container">
    <div><input type="checkbox" name="new" value="1"<?php if (isset($_REQUEST['new']) && $_REQUEST['new']) print " checked"; ?> /><label>New</label></div>
    <div><input type="checkbox" name="quote" value="1"<?php if (isset($_REQUEST['quote']) && $_REQUEST['quote']) print " checked"; ?> /><label>Quote</label></div>
    <div><input type="checkbox" name="cancelled" value="1"<?php if (isset($_REQUEST['cancelled']) && $_REQUEST['cancelled']) print " checked"; ?> /><label>Cancelled</label></div>
    <div><input type="checkbox" name="approved" value="1"<?php if (isset($_REQUEST['approved']) && $_REQUEST['approved']) print " checked"; ?> /><label>Approved</label></div>
    <div><input type="checkbox" name="accepted" value="1"<?php if (isset($_REQUEST['accepted']) && $_REQUEST['accepted']) print " checked"; ?> /><label>Accepted</label></div>
    <div><input type="checkbox" name="complete" value="1"<?php if (isset($_REQUEST['complete']) && $_REQUEST['complete']) print " checked"; ?>/><label>Complete</label></div>
    <input type="submit" name="btn_submit" class="button" value="Apply Filter" />
  </div>

  <div class="tableBody min-tablet">

    <div class="tableRowHeader">
      <div class="tableCell tableCell-width-15">
      <a href="/_sales/orders?pageNumber=<?php echo isset($pageNumber) ? $pageNumber : 1; ?>&sort_by=code&order_by=<?php echo isset($controls['sort']) && $controls['sort'] === 'code' && isset($controls['order']) && $controls['order'] === 'asc' ? 'desc' : 'asc'; ?>">Code</a>
      </div>
      <div class="tableCell tableCell-width-15">Created</div>
      <div class="tableCell tableCell-width-20">
        <a href="/_sales/orders?pageNumber=<?php echo isset($pageNumber) ? $pageNumber : 1; ?>&sort_by=customer_id&order_by=<?php echo isset($controls['sort']) && $controls['sort'] === 'customer_id' && isset($controls['order']) && $controls['order'] === 'asc' ? 'desc' : 'asc'; ?>">Customer</a>
      </div>
      <div class="tableCell tableCell-width-20">
        <a href="/_sales/orders?pageNumber=<?php echo isset($pageNumber) ? $pageNumber : 1; ?>&sort_by=salesperson_id&order_by=<?php echo isset($controls['sort']) && $controls['sort'] === 'salesperson_id' && isset($controls['order']) && $controls['order'] === 'asc' ? 'desc' : 'asc'; ?>">Sales Agent</a>
      </div>
      <div class="tableCell tableCell-width-15">
        <a href="/_sales/orders?pageNumber=<?php echo isset($pageNumber) ? $pageNumber : 1; ?>&sort_by=status&order_by=<?php echo isset($controls['sort']) && $controls['sort'] === 'status' && isset($controls['order']) && $controls['order'] === 'asc' ? 'desc' : 'asc'; ?>">Status</a>
      </div>
      <div class="tableCell tableCell-width-15">Amount</div>
    </div>

    <?php if (isset($orders) && is_array($orders)) {
		foreach ($orders as $order) { ?>

      <div class="tableRow">
        <div class="tableCell"><a href="/_sales/cart/<?=$order->code?>"><?=$order->code?></a></div>
        <div class="tableCell"><?=$order->date_created()?></div>
        <div class="tableCell">
          <?php
            $registerCustomer = new \Register\Customer($order->customer_id);
            $registerOrganization = new \Register\Organization($registerCustomer->organization_id);
          ?>        
          <strong><?=$registerOrganization->name?> </strong><br/>&nbsp;&nbsp;&nbsp;&nbsp;<?=$registerCustomer->first_name?> <?=$registerCustomer->last_name?>
        </div>
        <div class="tableCell">
          <?php if (!empty($order->salesperson_id)) {
            $salesAgent = new \Register\Customer($order->salesperson_id);?>
            <?=$salesAgent->first_name?> <?=$salesAgent->last_name?>
          <?php } ?>
        </div>
        <div class="tableCell"><?=$order->status?></div>
        <div class="tableCell">$<?=number_format($order->total(),2)?></div>
      </div>

    <?php 		} 
	} ?>
    
  </div>

  <!-- Start pagination -->
  <?php if (isset($pagination) && is_object($pagination)) { ?>
  <div class="pagination" id="pagination">
      <?=$pagination->renderPages()?>
  </div>
  <?php } ?>
  <!-- End pagination -->

</form>
