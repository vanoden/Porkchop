		<span class="title">Porkchop Navigation API</span>
		<div id="apiScroller">
			<div class="h3 apiMethodTitle">Request</div>
			<pre id="requestContent" style="text-align: left; width: 550px; height: 100px; overflow: auto; font-size: 11px; border: 1px dashed blue;"><?=print_r($_REQUEST)?></pre>
			<form method="post" action="<?=PATH?>/_navigation/api" id="ping">
			<input type="hidden" name="method" value="ping">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">ping</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_navigation/api" name="schemaVersion">
			<input type="hidden" name="method" value="schemaVersion">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">schemaVersion</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_navigation/api" id="findMenus">
			<input type="hidden" name="method" value="findMenus">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">findMenus</div>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_navigation/api" id="getMenu">
			<input type="hidden" name="method" value="getMenu">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">getMenu</div>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_navigation/api" id="addMenu">
			<input type="hidden" name="method" value="addMenu">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">addMenu</div>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
					<span class="label apiLabel">title</span>
					<input type="text" name="title" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_navigation/api" id="findItems">
			<input type="hidden" name="method" value="findItems">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">findItems</div>
				<div class="apiParameter">
					<span class="label apiLabel">menu_code</span>
					<input type="text" name="menu_code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_navigation/api" id="addItem">
			<input type="hidden" name="method" value="addItem">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">addItem</div>
				<div class="apiParameter">
					<span class="label apiLabel">menu_code</span>
					<input type="text" name="menu_code" class="value input apiInput"/>
					<span class="label apiLabel">title</span>
					<input type="text" name="title" class="value input apiInput"/>
					<span class="label apiLabel">target</span>
					<input type="text" name="target" class="value input apiInput"/>
					<span class="label apiLabel">alt</span>
					<input type="text" name="alt" class="value input apiInput"/>
					<span class="label apiLabel">description</span>
					<input type="text" name="description" class="value input apiInput"/>
					<span class="label apiLabel">view_order</span>
					<input type="text" name="view_order" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_navigation/api" id="updateItem">
			<input type="hidden" name="method" value="updateItem">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">updateItem</div>
				<div class="apiParameter">
					<span class="label apiLabel">id</span>
					<input type="text" name="id" class="value input apiInput"/>
					<span class="label apiLabel">title</span>
					<input type="text" name="title" class="value input apiInput"/>
					<span class="label apiLabel">target</span>
					<input type="text" name="target" class="value input apiInput"/>
					<span class="label apiLabel">alt</span>
					<input type="text" name="alt" class="value input apiInput"/>
					<span class="label apiLabel">description</span>
					<input type="text" name="description" class="value input apiInput"/>
					<span class="label apiLabel">view_order</span>
					<input type="text" name="view_order" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
		</div>