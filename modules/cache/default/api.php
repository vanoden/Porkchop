	<div id="scroller" style="width: 600px; height: 500px; overflow: auto; margin-left: 50px;">
		<div class="apiMethod">Request</div>
		<pre id="requestContent" style="text-align: left; width: 550px; height: 100px; overflow: auto; font-size: 11px; border: 1px dashed blue;"><?=print_r($_REQUEST)?></pre>
		<form method="post" action="<?=PATH?>/_cache/api" name="ping">
		<input type="hidden" name="apiMethod" value="ping">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">ping</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_cache/api" name="findKeys">
		<input type="hidden" name="method" value="findKeys">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findKeys</div>
			<div class="apiParameter">
				<span class="label apiLabel">object</span>
				<input type="text" name="object" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_cache/api" name="getItem">
		<input type="hidden" name="method" value="getItem">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">getItem</div>
			<div class="apiParameter">
				<span class="label apiLabel">object</span>
				<input type="text" name="object" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">id</span>
				<input type="text" name="id" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_cache/api" name="deleteItem">
		<input type="hidden" name="method" value="deleteItem">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">deleteItem</div>
			<div class="apiParameter">
				<span class="label apiLabel">object</span>
				<input type="text" name="object" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">id</span>
				<input type="text" name="id" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
	</div>
