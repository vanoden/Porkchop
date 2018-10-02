	<div id="scroller" style="width: 600px; height: 500px; overflow: auto;">
		<div class="apiMethod">Request</div>
		<pre id="requestContent" style="text-align: left; width: 550px; height: 100px; overflow: auto; font-size: 11px; border: 1px dashed blue;"><?=print_r($_REQUEST)?></pre>
		<form method="post" action="<?=PATH?>/_page/api" id="ping">
		<input type="hidden" name="method" value="ping">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">ping</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_page/api" id="parse">
		<input type="hidden" name="method" value="parse">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">parse</div>
			<div class="apiParameter">
				<span class="label apiLabel">string</span>
				<input type="text" name="string" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_page/api" id="findPages">
		<input type="hidden" name="method" value="findPages">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findPages</div>
			<div class="apiParameter">
				<span class="label apiLabel">target</span>
				<input type="text" name="module" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">module</span>
				<input type="text" name="module" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">view</span>
				<input type="text" name="view" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">index</span>
				<input type="text" name="index" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_page/api" id="addPage">
		<input type="hidden" name="method" value="addPage">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addPage</div>
			<div class="apiParameter">
			<div class="apiParameter">
				<span class="label apiLabel">target</span>
				<input type="text" name="target" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">module</span>
				<input type="text" name="module" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">view</span>
				<input type="text" name="view" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">index</span>
				<input type="text" name="index" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_page/api" id="updatePage">
		<input type="hidden" name="method" value="updatePage">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">updatePage</div>
			<div class="apiParameter">
				<span class="label apiLabel">id</span>
				<input type="text" name="id" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">target</span>
				<input type="text" name="target" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_page/api" id="getPage">
		<input type="hidden" name="method" value="getPage">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">getPage</div>
			<div class="apiParameter">
				<span class="label apiLabel">target</span>
				<input type="text" name="target" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_page/api" id="addMetadata">
		<input type="hidden" name="method" value="addMetadata">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addMetadata</div>
			<div class="apiParameter">
				<span class="label apiLabel">module</span>
				<input type="text" name="module" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">view</span>
				<input type="text" name="view" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">index</span>
				<input type="text" name="index" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">format</span>
				<input type="text" name="format" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">content</span>
				<input type="text" name="content" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_page/api" id="updateMetadata">
		<input type="hidden" name="method" value="updateMetadata">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">updateMetadata</div>
			<div class="apiParameter">
				<span class="label apiLabel">module</span>
				<input type="text" name="module" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">view</span>
				<input type="text" name="view" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">index</span>
				<input type="text" name="index" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">format</span>
				<input type="text" name="format" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">content</span>
				<input type="text" name="content" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_page/api" id="getMetadata">
		<input type="hidden" name="method" value="getMetadata">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">getMetadata</div>
			<div class="apiParameter">
				<span class="label apiLabel">module</span>
				<input type="text" name="module" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">view</span>
				<input type="text" name="view" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">index</span>
				<input type="text" name="index" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_page/api" id="findMetadata">
		<input type="hidden" name="method" value="findMetadata">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findMetadata</div>
			<div class="apiParameter">
				<span class="label apiLabel">module</span>
				<input type="text" name="module" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">view</span>
				<input type="text" name="view" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">index</span>
				<input type="text" name="index" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
	</div>
