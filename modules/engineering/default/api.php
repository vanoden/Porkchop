<span class="title">Porkchop Issue API Version <?=$_package["version"]?></span>
<form method="post" action="/_engineering/api" name="ping">
<input type="hidden" name="method" value="ping">
<div class="apiMethod">
	<div class="h3 apiMethodTitle">ping</div>
	<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
</div>
</form>
<form method="post" action="/_engineering/api" name="findProducts">
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
<form method="post" action="/_engineering/api" name="getProduct">
<input type="hidden" name="method" value="getProduct">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">getProduct</span>
	<div class="apiParameter">
		<span class="label apiLabel">code</span>
		<input type="text" name="code" class="value input apiInput"/>
	</div>
	<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
</div>
</form>

<form method="post" action="/_engineering/api" name="addProduct">
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
<form method="post" action="/_engineering/api" name="updateProduct">
<input type="hidden" name="method" value="updateProduct">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">updateProduct</span>
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
<form method="post" action="/_engineering/api" name="findProjects">
<input type="hidden" name="method" value="findProjects">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">findProjects</span>
	<div class="apiParameter">
		<span class="label apiLabel">code</span>
		<input type="text" name="status" class="value input apiInput"/>
	</div>
	<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
</div>
</form>
<form method="post" action="/_engineering/api" name="getProject">
<input type="hidden" name="method" value="getProject">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">getProject</span>
	<div class="apiParameter">
		<span class="label apiLabel">code</span>
		<input type="text" name="code" class="value input apiInput"/>
	</div>
	<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
</div>
</form>
<form method="post" action="/_engineering/api" name="addProject">
<input type="hidden" name="method" value="addProject">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">addProject</span>
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
<form method="post" action="/_engineering/api" name="updateProject">
<input type="hidden" name="method" value="updateProject">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">updateProject</span>
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
<form method="post" action="/_engineering/api" name="addRelease">
<input type="hidden" name="method" value="addRelease">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">addRelease</span>
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
<form method="post" action="/_engineering/api" name="updateRelease">
<input type="hidden" name="method" value="updateRelease">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">updateRelease</span>
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
<form method="post" action="/_engineering/api" name="findReleases">
<input type="hidden" name="method" value="findReleases">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">findReleases</span>
	<div class="apiParameter">
		<span class="label apiLabel">code</span>
		<input type="text" name="status" class="value input apiInput"/>
	</div>
	<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
</div>
</form>
<form method="post" action="/_engineering/api" name="getRelease">
<input type="hidden" name="method" value="getRelease">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">getRelease</span>
	<div class="apiParameter">
		<span class="label apiLabel">code</span>
		<input type="text" name="code" class="value input apiInput"/>
	</div>
	<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
</div>
</form>
<form method="post" action="/_engineering/api" name="findTasks">
<input type="hidden" name="method" value="findTasks">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">findTasks</span>
	<div class="apiParameter">
		<span class="label apiLabel">status</span>
		<input type="text" name="status" class="value input apiInput"/>
	</div>
	<div class="apiParameter">
		<span class="label apiLabel">assigned_to</span>
		<input type="text" name="assigned_to" class="value input apiInput"/>
	</div>
	<div class="apiParameter">
		<span class="label apiLabel">project_code</span>
		<input type="text" name="project_code" class="value input apiInput"/>
	</div>
	<div class="apiParameter">
		<span class="label apiLabel">release_code</span>
		<input type="text" name="release_code" class="value input apiInput"/>
	</div>
	<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
</div>
</form>
<form method="post" action="/_engineering/api" name="addTask">
<input type="hidden" name="method" value="addTask">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">addTask</span>
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
		<span class="label apiLabel">type</span>
		<input type="text" name="type" class="value input apiInput"/>
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
		<span class="label apiLabel">requested_by</span>
		<input type="text" name="requested_by" class="value input apiInput" value="<?= $GLOBALS['_SESSION_']->customer->code?>"/>
	</div>
	<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
</div>
</form>
<form method="post" action="/_engineering/api" name="getTask">
<input type="hidden" name="method" value="getTask">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">getTask</span>
	<div class="apiParameter">
		<span class="label apiLabel">code</span>
		<input type="text" name="code" class="value input apiInput"/>
	</div>
	<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
</div>
</form>
<form method="post" action="/_engineering/api" name="addEvent">
<input type="hidden" name="method" value="addEvent">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">addEvent</span>
	<div class="apiParameter">
		<span class="label apiLabel">code</span>
		<input type="text" name="code" class="value input apiInput"/>
	</div>
	<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
</div>
<form method="post" action="/_engineering/api" name="updateEvent">
<input type="hidden" name="method" value="updateEvent">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">updateEvent</span>
	<div class="apiParameter">
		<span class="label apiLabel">code</span>
		<input type="text" name="code" class="value input apiInput"/>
	</div>
	<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
</div>
</form>
<form method="post" action="/_engineering/api" name="findEvents">
<input type="hidden" name="method" value="findEvents">
<div class="apiMethod">
	<span class="h3 apiMethodTitle">findEvents</span>
	<div class="apiParameter">
		<span class="label apiLabel">task_code</span>
		<input type="text" name="task_code" class="value input apiInput"/>
	</div>
	<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
</div>
</form>