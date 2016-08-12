		<span class="title">Porkchop Session API Version <?=$_package["version"]?></span>
		<form method="post" action="/_session/api" name="ping">
		<input type="hidden" name="method" value="ping">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">ping</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="/_session/api">
		<input type="hidden" name="method" value="getSession">
		<div class="apiMethod">
			<span class="h3 apiMethodTitle">getSession</span>
			<div class="apiMethodParameter">
				<span class="apiMethodLabel">code</span>
				<input class="apiMethodValue" type="text" name="code"/>
			</div>
			<div class="methodFooter"><input type="submit" name="btn_submit" value="Submit" class="apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="/_session/api">
		<input type="hidden" name="method" value="addSession">
		<div class="apiMethod">
			<span class="apiMethodTitle">addSession</span>
			<div class="apiMethodParameter">
				<span class="apiMethodLabel">login</span>
				<input type="text" name="code" class="apiMethodInput"/></div>
			</div>
			<div class="apiMethodParameter">
				<span class="apiMethodLabel">password</span>
				<input type="password" name="password" class="apiMethodInput"/></div>
			</div>
			<div class="methodFooter"><input type="submit" name="btn_submit" value="Submit" class="apiMethodSubmit"/></div>
		</div>
		</form>