	<div id="scroller" style="width: 600px; height: 500px; overflow: auto; margin-left: 50px;">
		<div class="apiMethod">Request</div>
		<pre id="requestContent" style="text-align: left; width: 550px; height: 100px; overflow: auto; font-size: 11px; border: 1px dashed blue;"><?=print_r($_REQUEST)?></pre>
		<form method="post" action="<?=PATH?>/_product/api" name="ping">
		<input type="hidden" name="apiMethod" value="ping">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">ping</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_support/api" name="addRequest">
		<input type="hidden" name="method" value="addRequest">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addRequest</div>
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
				<span class="label apiLabel">tech</span>
				<input type="text" name="tech" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">status</span>
				<input type="text" name="status" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_support/api" name="updateRequest">
		<input type="hidden" name="method" value="updateRequest">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">updateRequest</div>
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
		<form method="post" action="<?=PATH?>/_support/api" name="findRequests">
		<input type="hidden" name="method" value="findRequests">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findRequests</div>
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
		<form method="post" action="<?=PATH?>/_support/api" name="getRequest">
		<input type="hidden" name="method" value="getRequest">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">getRequest</div>
			<div class="apiParameter">
				<span class="label apiLabel">code</span>
				<input type="text" name="code" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_support/api" name="addEvent">
		<input type="hidden" name="method" value="addEvent">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addEvent</div>
			<div class="apiParameter">
				<span class="label apiLabel">request</span>
				<input type="text" name="request" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">tech</span>
				<input type="text" name="tech" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">comment</span>
				<input type="text" name="comment" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_support/api" name="findEvents">
		<input type="hidden" name="method" value="findEvents">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findEvents</div>
			<div class="apiParameter">
				<span class="label apiLabel">request</span>
				<input type="text" name="parent_code" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
	</div>