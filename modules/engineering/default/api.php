<span class="title">Porkchop Issue API Version <?=$_package["version"]?></span>
<form method="post" action="/_issue/api" name="ping">
<input type="hidden" name="method" value="ping">
<div class="apiMethod">
	<div class="h3 apiMethodTitle">ping</div>
	<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
</div>
</form>
<form method="post" action="/_issue/api" name="findProducts">
<input type="hidden" name="method" value="findProducts">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">findProducts</span>
	<div class="apiParameter">
		<span class="label apiLabel">code</span>
		<input type="text" name="status" class="value input apiInput"/>
	</div>
	<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
</div>
</form>
<form method="post" action="/_issue/api" name="addProduct">
<input type="hidden" name="method" value="addProduct">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">addProduct</span>
	<div class="apiParameter">
		<span class="label apiLabel">code</span>
		<input type="text" name="code" class="value input apiInput"/>
	</div>
	<div class="apiParameter">
		<span class="label apiLabel">name</span>
		<input type="text" name="name" class="value input apiInput"/>
	</div>
	<div class="apiParameter">
		<span class="label apiLabel">status</span>
		<input type="text" name="status" class="value input apiInput"/>
	</div>
	<div class="apiParameter">
		<span class="label apiLabel">owner_code</span>
		<input type="text" name="owner_code" class="value input apiInput"/>
	</div>
	<div class="apiParameter">
		<span class="label apiLabel">description</span>
		<input type="text" name="description" class="value input apiInput"/>
	</div>
	<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
</div>
</form>
<form method="post" action="/_issue/api" name="findIssues">
<input type="hidden" name="method" value="findIssues">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">findIssues</span>
	<div class="apiParameter">
		<span class="label apiLabel">status</span>
		<input type="text" name="status" class="value input apiInput"/>
	</div>
	<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
</div>
</form>
<form method="post" action="/_issue/api" name="addIssue">
<input type="hidden" name="method" value="addIssue">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">addIssue</span>
	<div class="apiParameter">
		<span class="label apiLabel">title</span>
		<input type="text" name="title" class="value input apiInput"/>
	</div>
	<div class="apiParameter">
		<span class="label apiLabel">description</span>
		<input type="text" name="description" class="value input apiInput"/>
	</div>
	<div class="apiParameter">
		<span class="label apiLabel">status</span>
		<input type="text" name="status" class="value input apiInput"/>
	</div>
	<div class="apiParameter">
		<span class="label apiLabel">priority</span>
		<input type="text" name="priority" class="value input apiInput"/>
	</div>
	<div class="apiParameter">
		<span class="label apiLabel">product_code</span>
		<input type="text" name="product_code" class="value input apiInput"/>
	</div>
	<div class="apiParameter">
		<span class="label apiLabel">user_reported</span>
		<input type="text" name="user_reported" class="value input apiInput" value="<?= $GLOBALS['_SESSION_']->customer->code?>"/>
	</div>
	<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
</div>
</form>
