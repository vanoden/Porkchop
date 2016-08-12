	<div id="scroller" style="width: 600px; height: 500px; overflow: auto;">
		<div class="apiMethod">Request</div>
		<pre id="requestContent" style="text-align: left; width: 550px; height: 100px; overflow: auto; font-size: 11px; border: 1px dashed blue;"><?=print_r($_REQUEST)?></pre>
		<form method="post" action="<?=PATH?>/_content/api" id="ping">
		<input type="hidden" name="method" value="ping">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">ping</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_content/api" id="parse">
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
		<form method="post" action="<?=PATH?>/_content/api" id="findMessages">
		<input type="hidden" name="method" value="findMessages">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findMessages</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">options</span>
				<input type="text" name="options" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_content/api" id="addMessage">
		<input type="hidden" name="method" value="addMessage">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addMessage</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">target</span>
				<input type="text" name="target" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">title</span>
				<input type="text" name="title" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">content</span>
				<input type="text" name="content" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">custom_1</span>
				<input type="text" name="custom_1" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">custom_2</span>
				<input type="text" name="custom_2" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">custom_3</span>
				<input type="text" name="custom_3" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_content/api" id="updateMessage">
		<input type="hidden" name="method" value="updateMessage">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">updateMessage</div>
			<div class="apiParameter">
				<span class="label apiLabel">id</span>
				<input type="text" name="id" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">target</span>
				<input type="text" name="target" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">content</span>
				<input type="text" name="content" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">custom_1</span>
				<input type="text" name="custom_1" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">custom_2</span>
				<input type="text" name="custom_2" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">custom_3</span>
				<input type="text" name="custom_3" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_content/api" id="getMessage">
		<input type="hidden" name="method" value="getMessage">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">getMessage</div>
			<div class="apiParameter">
				<span class="label apiLabel">target</span>
				<input type="text" name="target" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_content/api" id="purgeMessage">
		<input type="hidden" name="method" value="purgeMessage">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">purgeMessage</div>
			<div class="apiParameter">
				<span class="label apiLabel">target</span>
				<input type="text" name="target" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_content/api" id="findNavigationItems">
		<input type="hidden" name="method" value="findNavigationItems">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findNavigationItems</div>
			<div class="apiParameter">
				<span class="label apiLabel">id</span>
				<input type="text" name="id" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">parent_id</span>
				<input type="text" name="parent_id" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_content/api" id="addMetadata">
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
		<form method="post" action="<?=PATH?>/_content/api" id="updateMetadata">
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
		<form method="post" action="<?=PATH?>/_content/api" id="getMetadata">
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
		<form method="post" action="<?=PATH?>/_content/api" id="findMetadata">
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