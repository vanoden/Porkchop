<?
	if (! $GLOBALS['_SESSION_']->customer->has_role('monitor admin')){
		print "<span class=\"form_error\">You are not authorized for this view!</span>";
		return;
	}
?>			<script language="Javascript">
				function goNewMonitor()
				{
					var form = document.getElementById('filterForm');
					form.action = '/_monitor/admin_details';
					form.submit();
					return true;
				}
			</script>
			<table class="body" style="width: 700px">
			<tr><td class="title" colspan="3">Filter</td></tr>
			<tr><td class="label">Serial Number</td>
				<td class="label">Model</td>
				<td class="label">Organization</td>
			</tr>
			<form id="filterForm" name="filterForm" method="get" action="/_monitor/admin_assets">
			<tr><td class="value"><input type="text" name="code" value="<?=$_REQUEST['code']?>" class="value input" autofocus/></td>
				<td class="value">
					<select name="product_id" class="value input">
						<option value="">All</option>
			<?	foreach ($products as $product) { ?>
						<option value="<?=$product->id?>"<? if (isset($_REQUEST['product_id']) && $product->id == $_REQUEST['product_id']) print " selected";?>><?=$product->code?></option>
			<?	} ?>
					</select>
				</td>
				<td class="value">
					<select name="organization_id" class="value input">
						<option value="">All</option>
			<?	foreach ($organizations as $organization) { ?>
						<option value="<?=$organization['id']?>"<? if (key_exists('organization_id',$_REQUEST) && $organization['id'] == $_REQUEST['organization_id']) print " selected";?>><?=$organization['name']?></option>
			<?	} ?>
					</select>
				</td>
			</tr>
			<tr><td colspan="3" class="form_footer">
					<input type="submit" name="btn_submit" value="Search" class="button" />
					<input type="button" name="btn_new" value="Add" class="button" onclick="goNewMonitor()" />
				</td>
			</tr>
			</form>
			</table>
			<br/>
			<table class="body" style="width: 700px">
			<tr><td class="title" colspan="3">Monitors [<?=count($assets)?>]</td></tr>
			<tr><td class="label">Serial</td>
				<td class="label">Model</td>
				<td class="label">Organization</td>
			</tr>
			<?	foreach ($assets as $asset) { ?>
			<tr><td class="value <?=$greenbar?>"><a href="/_monitor/admin_details/<?=$asset->code?>/<?=$asset->product->code?>" class="value"><?=$asset->code?></a></td>
				<td class="value <?=$greenbar?>"><?=$asset->product->code?></td>
				<td class="value <?=$greenbar?>"><?=$asset->organization->name?></td>
			</tr>
			<?
					if ($greenbar) $greenbar = '';
					else $greenbar = 'greenbar';
				} ?>
			</table>
