<?php
function salesOrdersQueryParams(array $overrides = []): string {
	$params = [];
	foreach (['new', 'quote', 'cancelled', 'approved', 'accepted', 'complete'] as $key) {
		if (!empty($_REQUEST[$key])) {
			$params[$key] = 1;
		}
	}
	if (!empty($_REQUEST['sort_by'])) {
		$params['sort_by'] = $_REQUEST['sort_by'];
	}
	if (!empty($_REQUEST['order_by'])) {
		$params['order_by'] = $_REQUEST['order_by'];
	}
	$params = array_merge($params, $overrides);

	return http_build_query($params);
}

function salesOrdersSortHref(string $field, array $controls): string {
	$order = (isset($controls['sort']) && $controls['sort'] === $field && ($controls['order'] ?? '') === 'asc') ? 'desc' : 'asc';

	return '/_sales/orders?' . salesOrdersQueryParams([
		'sort_by' => $field,
		'order_by' => $order,
		'pagination_start_id' => 0,
		'btn_search' => 'Search',
	]);
}
?>
<script type="text/javascript">
	function resetPaginationAndSearch() {
		document.getElementById('pagination_start_id').value = '0';
	}
</script>

<?=$page->showAdminPageInfo()?>

<form id="ordersSearch" action="/_sales/orders" method="get" class="monitor-admin-list sales-orders-list">
	<input type="hidden" id="pagination_start_id" name="pagination_start_id" value="<?=htmlspecialchars((string)(isset($pagination) ? $pagination->startId() : ($paginationStart ?? 0)), ENT_QUOTES, 'UTF-8')?>">

	<div class="filter-bar">
		<div class="filter-bar__controls">
			<div class="form-field form-field--checks">
				<span class="form-field__group-label">Status</span>
				<label class="check-field">
					<input type="checkbox" name="new" value="1"<?php if (!empty($_REQUEST['new'])) print ' checked'; ?>>
					New
				</label>
				<label class="check-field">
					<input type="checkbox" name="quote" value="1"<?php if (!empty($_REQUEST['quote'])) print ' checked'; ?>>
					Quote
				</label>
				<label class="check-field">
					<input type="checkbox" name="accepted" value="1"<?php if (!empty($_REQUEST['accepted'])) print ' checked'; ?>>
					Accepted
				</label>
				<label class="check-field">
					<input type="checkbox" name="approved" value="1"<?php if (!empty($_REQUEST['approved'])) print ' checked'; ?>>
					Approved
				</label>
				<label class="check-field">
					<input type="checkbox" name="complete" value="1"<?php if (!empty($_REQUEST['complete'])) print ' checked'; ?>>
					Complete
				</label>
				<label class="check-field">
					<input type="checkbox" name="cancelled" value="1"<?php if (!empty($_REQUEST['cancelled'])) print ' checked'; ?>>
					Cancelled
				</label>
			</div>
		</div>
		<div class="button-group filter-bar__actions">
			<button type="submit" name="btn_search" value="Search" onclick="resetPaginationAndSearch()">Search</button>
			<a class="button btn-secondary" href="/_sales/cart">Create Order</a>
		</div>
	</div>

	<h2>Orders [<?=isset($totalRecords) ? (int)$totalRecords : count($orders ?? [])?>]</h2>
	<table class="responsive-table responsive-table--banded">
		<thead>
			<tr>
				<th scope="col" class="col-w-15 sortableHeader"><a href="<?=htmlspecialchars(salesOrdersSortHref('code', $controls ?? []), ENT_QUOTES, 'UTF-8')?>">Code</a></th>
				<th scope="col" class="col-w-15">Created</th>
				<th scope="col" class="col-w-20 sortableHeader"><a href="<?=htmlspecialchars(salesOrdersSortHref('customer_id', $controls ?? []), ENT_QUOTES, 'UTF-8')?>">Customer</a></th>
				<th scope="col" class="col-w-20 sortableHeader"><a href="<?=htmlspecialchars(salesOrdersSortHref('salesperson_id', $controls ?? []), ENT_QUOTES, 'UTF-8')?>">Sales Agent</a></th>
				<th scope="col" class="col-w-15 sortableHeader"><a href="<?=htmlspecialchars(salesOrdersSortHref('status', $controls ?? []), ENT_QUOTES, 'UTF-8')?>">Status</a></th>
				<th scope="col" class="col-w-15">Amount</th>
			</tr>
		</thead>
		<tbody>
<?php if (isset($orders) && is_array($orders) && count($orders)) {
		foreach ($orders as $order) {
			$registerCustomer = new \Register\Customer($order->customer_id);
			$registerOrganization = new \Register\Organization($registerCustomer->organization_id);
?>
			<tr>
				<td data-label="Code"><a href="/_sales/cart/<?=htmlspecialchars($order->code, ENT_QUOTES, 'UTF-8')?>"><?=htmlspecialchars($order->code)?></a></td>
				<td data-label="Created"><?=htmlspecialchars((string)$order->date_created())?></td>
				<td data-label="Customer">
					<strong><?=htmlspecialchars($registerOrganization->name ?? '')?></strong><br>
					<?=htmlspecialchars(trim($registerCustomer->first_name . ' ' . $registerCustomer->last_name))?>
				</td>
				<td data-label="Sales Agent">
<?php if (!empty($order->salesperson_id)) {
	$salesAgent = new \Register\Customer($order->salesperson_id); ?>
					<?=htmlspecialchars(trim($salesAgent->first_name . ' ' . $salesAgent->last_name))?>
<?php } ?>
				</td>
				<td data-label="Status"><?=htmlspecialchars($order->status)?></td>
				<td data-label="Amount">$<?=number_format($order->total(), 2)?></td>
			</tr>
<?php
		}
	} else { ?>
			<tr>
				<td colspan="6">No orders found</td>
			</tr>
<?php } ?>
		</tbody>
	</table>

<?php if (isset($pagination) && is_object($pagination)) { ?>
	<?=$pagination->renderBar()?>
<?php } ?>
</form>
