	<div id="scroller" style="width: 600px; height: 500px; overflow: auto; margin-left: 50px;">
		<div class="apiMethod">Request</div>
		<pre id="requestContent" style="text-align: left; width: 550px; height: 100px; overflow: auto; font-size: 11px; border: 1px dashed blue;"><?=print_r($_REQUEST)?></pre>
		<form method="post" action="<?=PATH?>/_product/api" name="ping">
		<input type="hidden" name="method" value="ping">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">ping</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_product/api" name="addProduct">
		<input type="hidden" name="method" value="addProduct">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addProduct</div>
			<div class="apiParameter">
				<span class="label apiLabel">code</span>
				<input type="text" name="code" class="value input apiInput"/>
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
				<span class="label apiLabel">description</span>
				<input type="text" name="description" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">status</span>
				<input type="text" name="status" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_product/api" name="updateProduct">
		<input type="hidden" name="method" value="updateProduct">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">updateProduct</div>
			<div class="apiParameter">
				<span class="label apiLabel">code</span>
				<input type="text" name="code" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">description</span>
				<input type="text" name="description" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_product/api" name="findProducts">
		<input type="hidden" name="method" value="findProducts">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findProducts</div>
			<div class="apiParameter">
				<span class="label apiLabel">code</span>
				<input type="text" name="code" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">description</span>
				<input type="text" name="description" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_product/api" name="getProduct">
		<input type="hidden" name="method" value="getProduct">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">getProduct</div>
			<div class="apiParameter">
				<span class="label apiLabel">code</span>
				<input type="text" name="code" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_product/api" name="addRelationship">
		<input type="hidden" name="method" value="addRelationship">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addRelationship</div>
			<div class="apiParameter">
				<span class="label apiLabel">parent_code</span>
				<input type="text" name="parent_code" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">child_code</span>
				<input type="text" name="child_code" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_product/api" name="getRelationship">
		<input type="hidden" name="method" value="getRelationship">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">getRelationship</div>
			<div class="apiParameter">
				<span class="label apiLabel">parent_code</span>
				<input type="text" name="parent_code" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">child_code</span>
				<input type="text" name="child_code" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_product/api" name="findRelationships">
		<input type="hidden" name="method" value="findRelationships">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findRelationships</div>
			<div class="apiParameter">
				<span class="label apiLabel">parent_code</span>
				<input type="text" name="parent_code" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">child_code</span>
				<input type="text" name="child_code" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">parent_id</span>
				<input type="text" name="parent_id" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">child_id</span>
				<input type="text" name="child_id" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_product/api" name="addGroup">
		<input type="hidden" name="method" value="addGroup">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addGroup</div>
			<div class="apiParameter">
				<span class="label apiLabel">code</span>
				<input type="text" name="code" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_product/api" name="updateGroup">
		<input type="hidden" name="method" value="updateGroup">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">updateGroup</div>
			<div class="apiParameter">
				<span class="label apiLabel">code</span>
				<input type="text" name="code" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="asset_code" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_product/api" name="findGroups">
		<input type="hidden" name="method" value="findGroups">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findGroups</div>
			<div class="apiParameter">
				<span class="label apiLabel">code</span>
				<input type="text" name="code" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_product/api" name="addGroupProduct">
		<input type="hidden" name="method" value="addGroupProduct">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addGroupProduct</div>
			<div class="apiParameter">
				<span class="label apiLabel">group_code</span>
				<input type="text" name="group_code" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">product_code</span>
				<input type="text" name="product_code" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_product/api" name="findGroupProducts">
		<input type="hidden" name="method" value="findGroupProducts">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findGroupProducts</div>
			<div class="apiParameter">
				<span class="label apiLabel">group_code</span>
				<input type="text" name="sensor_code" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_product/api" name="addProductMeta">
		<input type="hidden" name="method" value="addProductMeta">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addProductMeta</div>
			<div class="apiParameter">
				<span class="label apiLabel">code</span>
				<input type="text" name="code" class="value input apiInput"/>
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
		<form method="post" action="<?=PATH?>/_product/api" name="addProductImage">
		<input type="hidden" name="method" value="addProductImage">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addProductImage</div>
			<div class="apiParameter">
				<span class="label apiLabel">product_code</span>
				<input type="text" name="product_code" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">image_code</span>
				<input type="text" name="image_code" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">label</span>
				<input type="text" name="label" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
	</div>