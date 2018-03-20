		<span class="title">Porkchop Action API Version <?=$_package["version"]?></span>
		<form method="post" action="/_action/api" name="ping">
		<input type="hidden" name="method" value="ping">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">ping</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="/_action/api">
		<input type="hidden" name="method" value="addActionRequest">
		<div class="apiMethod">
			<span class="h3 apiMethodTitle">addActionRequest</span>
			<div class="apiMethodParameter">
				<span class="apiMethodLabel">code</span>
				<input class="apiMethodValue" type="text" name="code"/>
				<span class="apiMethodLabel">person_code</span>
				<input class="apiMethodValue" type="text" name="person_code"/>
				<span class="apiMethodLabel">description</span>
				<input class="apiMethodValue" type="text" name="description"/>
			</div>
			<div class="methodFooter"><input type="submit" name="btn_submit" value="Submit" class="apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="/_action/api">
		<input type="hidden" name="method" value="getActionRequest">
		<div class="apiMethod">
			<span class="apiMethodTitle">getActionRequest</span>
			<div class="apiMethodParameter">
				<span class="apiMethodLabel">code</span>
				<input type="text" name="code" class="apiMethodInput"/></div>
			</div>
			<div class="methodFooter"><input type="submit" name="btn_submit" value="Submit" class="apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="/_action/api">
		<input type="hidden" name="method" value="findActionRequests">
		<div class="apiMethod">
			<span class="apiMethodTitle">findActionRequests</span>
			<div class="apiMethodParameter">
				<span class="apiMethodLabel">code</span>
				<input type="text" name="code" class="apiMethodInput"/></div>
			</div>
			<div class="methodFooter"><input type="submit" name="btn_submit" value="Submit" class="apiMethodSubmit"/></div>
		</div>
		</form>