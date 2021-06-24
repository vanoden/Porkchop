	<div id="scroller" style="width: 600px; height: 500px; overflow: auto; margin-left: 50px;">
		<div class="apiMethod">Request</div>
		<pre id="requestContent" style="text-align: left; width: 550px; height: 100px; overflow: auto; font-size: 11px; border: 1px dashed blue;"><?=print_r($_REQUEST)?></pre>
		<form method="post" action="<?=PATH?>/_shipping/api" name="ping">
		<input type="hidden" name="apiMethod" value="ping">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">ping</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
<!-- Vendor Calls -->
		<form method="post" action="<?=PATH?>/_shipping/api" name="findVendors">
		<input type="hidden" name="method" value="findVendors">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findVendors</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_shipping/api" name="addVendor">
		<input type="hidden" name="method" value="addVendor">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addVendor</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">account_number</span>
				<input type="text" name="account_number" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_shipping/api" name="updateVendor">
		<input type="hidden" name="method" value="updateVendor">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">updateVendor</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">account_number</span>
				<input type="text" name="account_number" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_shipping/api" name="getVendor">
		<input type="hidden" name="method" value="getVendor">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">getVendor</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>

<!-- Shipment Calls -->
		<form method="post" action="<?=PATH?>/_shipping/api" name="addShipment">
		<input type="hidden" name="method" value="addShipment">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addShipment</div>
			<div class="apiParameter">
				<span class="label apiLabel">send_customer_id</span>
				<input type="text" name="send_customer_id" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">send_location_id</span>
				<input type="text" name="send_location_id" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">receive_customer_id</span>
				<input type="text" name="receive_customer_id" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">receive_location_id</span>
				<input type="text" name="receive_location_id" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">vendor_id</span>
				<input type="text" name="vendor" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">document_number</span>
				<input type="text" name="document_number" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_shipping/api" name="updateShipment">
		<input type="hidden" name="method" value="updateShipment">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">updateShipment</div>
			<div class="apiParameter">
				<span class="label apiLabel">code</span>
				<input type="text" name="code" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">status</span>
				<input type="text" name="status" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_shipping/api" name="findShipments">
		<input type="hidden" name="method" value="findShipments">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findShipments</div>
			<div class="apiParameter">
				<span class="label apiLabel">code</span>
				<input type="text" name="code" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">customer</span>
				<input type="text" name="customer" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">organization</span>
				<input type="text" name="organization" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">status</span>
				<input type="text" name="status" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_shipping/api" name="getShipment">
		<input type="hidden" name="method" value="getShipment">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">getShipment</div>
			<div class="apiParameter">
				<span class="label apiLabel">code</span>
				<input type="text" name="code" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
	</div>