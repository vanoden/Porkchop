		<form method="post" action="/_contact/api" name="ping">
		<input type="hidden" name="method" value="ping">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">ping</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="/_contact/api" name="addEvent">
		<input type="hidden" name="method" value="addEvent">
		<div class="apiMethod">
			<span class="h3 apiMethodTitle">addEvent</span>
			<div class="apiParameter">
				<span class="label apiLabel">status</span>
				<input type="text" name="status" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">content</span>
				<input type="text" name="content" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="/_contact/api" name="findEvents">
		<input type="hidden" name="method" value="findEvents">
		<div class="apiMethod">
			<span class="h3 apiMethodTitle">findEvents</span>
			<div class="apiParameter">
				<span class="label apiLabel">status</span>
				<input type="text" name="code" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>