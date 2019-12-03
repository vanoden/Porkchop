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
		<!-- Products -->
		<form method="post" action="<?=PATH?>/_build/api" name="addProduct">
		<input type="hidden" name="method" value="addProduct">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addProduct</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">workspace</span>
				<input type="text" name="workspace" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">major_version</span>
				<input type="text" name="major_version" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">minor_version</span>
				<input type="text" name="minor_version" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_build/api" name="updateProduct">
		<input type="hidden" name="method" value="updateProduct">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">updateProduct</div>
			<div class="apiParameter">
				<span class="label apiLabel">workspace</span>
				<input type="text" name="workspace" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">major_version</span>
				<input type="text" name="major_version" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">minor_version</span>
				<input type="text" name="minor_version" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_build/api" name="findProducts">
		<input type="hidden" name="method" value="findProducts">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findProducts</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_build/api" name="getProduct">
		<input type="hidden" name="method" value="getProduct">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">getProduct</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<!-- Versions -->
		<form method="post" action="<?=PATH?>/_build/api" name="addVersion">
		<input type="hidden" name="method" value="addVersion">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addVersion</div>
			<div class="apiParameter">
				<span class="label apiLabel">product</span>
				<input type="text" name="product" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">major_number</span>
				<input type="text" name="major_number" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">minor_number</span>
				<input type="text" name="minor_number" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">user</span>
				<input type="text" name="user" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">number</span>
				<input type="text" name="number" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">status</span>
				<input type="text" name="status" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">tarball</span>
				<input type="text" name="tarball" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">message</span>
				<input type="text" name="message" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_build/api" name="findVersions">
		<input type="hidden" name="method" value="findVersions">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findVersions</div>
			<div class="apiParameter">
				<span class="label apiLabel">product</span>
				<input type="text" name="product" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">status</span>
				<input type="text" name="status" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<!-- Repositories -->
		<form method="post" action="<?=PATH?>/_build/api" name="addRepository">
		<input type="hidden" name="method" value="addRepository">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addRepository</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="url" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_build/api" name="updateRepository">
		<input type="hidden" name="method" value="updateRepository">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">updateRepository</div>
			<div class="apiParameter">
				<span class="label apiLabel">url</span>
				<input type="text" name="url" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_build/api" name="findRepositories">
		<input type="hidden" name="method" value="findRepositories">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findRepositories</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_build/api" name="getRepository">
		<input type="hidden" name="method" value="getRepository">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">getRepository</div>
			<div class="apiParameter">
				<span class="label apiLabel">url</span>
				<input type="text" name="url" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
	</div>