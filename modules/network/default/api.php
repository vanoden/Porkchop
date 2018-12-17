	<div id="scroller" style="width: 600px; height: 500px; overflow: auto;">
		<div class="apiMethod">Request</div>
		<pre id="requestContent" style="text-align: left; width: 550px; height: 100px; overflow: auto; font-size: 11px; border: 1px dashed blue;"><?=print_r($_REQUEST)?></pre>
		<form method="post" action="<?=PATH?>/_network/api" id="ping">
		<input type="hidden" name="method" value="ping">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">ping</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_network/api" id="findDomains">
		<input type="hidden" name="method" value="findDomains">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findDomains</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_network/api" id="addDomain">
		<input type="hidden" name="method" value="addDomain">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addDomain</div>
			<div class="apiParameter">
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_network/api" id="findHosts">
		<input type="hidden" name="method" value="findHosts">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findHosts</div>
			<div class="apiParameter">
				<span class="label apiLabel">domain_name</span>
				<input type="text" name="domain_name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">os_name</span>
				<input type="text" name="os_name" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_network/api" id="addHost">
		<input type="hidden" name="method" value="addHost">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addHost</div>
			<div class="apiParameter">
				<span class="label apiLabel">domain_name</span>
				<input type="text" name="domain" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">os_name</span>
				<input type="text" name="os_name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">os_version</span>
				<input type="text" name="os_version" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_network/api" id="getHost">
		<input type="hidden" name="method" value="getHost">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">getHost</div>
			<div class="apiParameter">
				<span class="label apiLabel">domain_name</span>
				<input type="text" name="domain" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_network/api" id="setHostMetadata">
		<input type="hidden" name="method" value="setHostMetadata">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">setHostMetadata</div>
			<div class="apiParameter">
				<span class="label apiLabel">host_name</span>
				<input type="text" name="host_name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">key</span>
				<input type="text" name="key" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">value</span>
				<input type="text" name="value" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_network/api" id="getHostMetadata">
		<input type="hidden" name="method" value="getHostMetadata">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">getHostMetadata</div>
			<div class="apiParameter">
				<span class="label apiLabel">host_name</span>
				<input type="text" name="host_name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">key</span>
				<input type="text" name="key" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_network/api" id="findAdapters">
		<input type="hidden" name="method" value="findAdapters">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findAdapters</div>
			<div class="apiParameter">
				<span class="label apiLabel">domain_name</span>
				<input type="text" name="domain_name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">host_name</span>
				<input type="text" name="host_name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">type</span>
				<input type="text" name="type" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">mac_address</span>
				<input type="text" name="mac_address" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_network/api" id="addAdapter">
		<input type="hidden" name="method" value="addAdapter">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addAdapter</div>
			<div class="apiParameter">
				<span class="label apiLabel">domain_name</span>
				<input type="text" name="domain_name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">host_name</span>
				<input type="text" name="host_name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">type</span>
				<input type="text" name="type" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">mac_address</span>
				<input type="text" name="mac_address" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_network/api" id="getAdapter">
		<input type="hidden" name="method" value="getAdapter">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">getAdapter</div>
			<div class="apiParameter">
				<span class="label apiLabel">domain_name</span>
				<input type="text" name="domain_name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">host_name</span>
				<input type="text" name="host_name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_network/api" id="findAddresses">
		<input type="hidden" name="method" value="findAddresses">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findAddresses</div>
			<div class="apiParameter">
				<span class="label apiLabel">domain_name</span>
				<input type="text" name="domain_name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">host_name</span>
				<input type="text" name="host_name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">adapter_name</span>
				<input type="text" name="adapter_name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">type</span>
				<input type="text" name="type" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_network/api" id="addAddress">
		<input type="hidden" name="method" value="addAddress">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addAddress</div>
			<div class="apiParameter">
				<span class="label apiLabel">domain_name</span>
				<input type="text" name="domain_name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">host_name</span>
				<input type="text" name="host_name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">adapter_name</span>
				<input type="text" name="adapter_name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">address</span>
				<input type="text" name="address" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">prefix</span>
				<input type="text" name="prefix" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">type</span>
				<input type="text" name="type" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
	</div>
