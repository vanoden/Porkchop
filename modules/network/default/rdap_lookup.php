<?= $page->showSubHeading() ?>

<label for="subnet_name">Subnet Name:</label>
<span id="subnet_name" name="subnet_name"><?= $rdap->name() ?></span>

<label for="subnet_handle">Subnet Handle:</label>
<span id="subnet_handle" name="subnet_handle"><?= $rdap->handle() ?></span>

<label for="subnet_type">Subnet Type:</label>
<span id="subnet_type" name="subnet_type"><?= $rdap->type() ?></span>

<label for="subnet_start_address">Start Address:</label>
<span id="subnet_start_address" name="subnet_start_address"><?= $rdap->result['startAddress'] ?? 'N/A' ?></span>

<label for="subnet_end_address">End Address:</label>
<span id="subnet_end_address" name="subnet_end_address"><?= $rdap->result['endAddress'] ?? 'N/A' ?></span>

<label for="full_rdap_result">Full RDAP Result:</label>
<pre id="full_rdap_result" name="full_rdap_result"><?= print_r($rdap->result, true) ?></pre>