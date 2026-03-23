<?= $page->showSubHeading() ?>

<label for="subnet_address">Subnet Address</label>
<input type="text" id="subnet_address" name="subnet_address" value="<?= long2ip($subnet->address) ?>" required>

<label for="subnet_size">Subnet Size</label>
<input type="number" id="subnet_size" name="subnet_size" value="<?= $subnet->size ?>" required>

<label for="subnet_type">Subnet Type</label>
<select id="subnet_type" name="subnet_type" required>
	<option value="ipv4" <?= $subnet->type == 'ipv4' ? 'selected' : '' ?>>IPv4</option>
	<option value="ipv6" <?= $subnet->type == 'ipv6' ? 'selected' : '' ?>>IPv6</option>
</select>

<label for="subnet_risk_level">Risk Level</label>
<input type="number" id="subnet_risk_level" name="subnet_risk_level" value="<?= $subnet->risk_level ?>" required>

<label for="subnet_managed">Managed</label>
<select id="subnet_managed" name="subnet_managed" required>
	<option value="AUTO" <?= $subnet->managed == 'AUTO' ? 'selected' : '' ?>>AUTO</option>
	<option value="MANUAL" <?= $subnet->managed == 'MANUAL' ? 'selected' : '' ?>>MANUAL</option>
</select>

<label for="subnet_date_added">Date Added</label>
<span id="subnet_date_added" name="subnet_date_added"> <?= $subnet->date_added ? date('Y-m-d\TH:i:s', strtotime($subnet->date_added)) : '' ?></span>

<label for="subnet_date_last_seen">Date Last Seen</label>
<span id="subnet_date_last_seen" name="subnet_date_last_seen"> <?= $subnet->date_last_seen ? date('Y-m-d\TH:i:s', strtotime($subnet->date_last_seen)) : '' ?></span>

<label for="subnet_uri_last_seen">URI Last Seen</label>
<span id="subnet_uri_last_seen" name="subnet_uri_last_seen"> <?= $subnet->uri_last_seen ? $subnet->uri_last_seen : '' ?></span>

<?php if ($subnet->id) { ?>
<label for="session_user_agent">User Agent</label>
<span id="session_user_agent" name="session_user_agent"> <?= $subnet->session() ? $subnet->session()->user_agent : 'N/A' ?></span>

<label for="hit_count">Hit Count</label>
<span id="hit_count" name="hit_count"> <?= $subnet->session() ? $subnet->session()->hitCount() : 'N/A' ?></span>
<div class="tableBody bandedRows">
	<div class="tableRow header">
		<div class="tableCell">Date</div>
		<div class="tableCell">Remote IP</div>
		<div class="tableCell">Script</div>
		<div class="tableCell">Query String</div>
	</div>
	<?php foreach ($hits as $hit) { ?>
		<div class="tableRow">
			<div class="tableCell"><?= $hit->hit_date ?></div>
			<div class="tableCell"><?= $hit->remote_ip ?></div>
			<div class="tableCell"><?= $hit->script ?></div>
			<div class="tableCell"><?= $hit->query_string ?></div>
		</div>
	<?php } ?>
	</div>
<?php } ?>