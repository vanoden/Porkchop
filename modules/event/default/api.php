		<span class="title">Porkchop Event API Version <?=$_package["version"]?></span>
		<form method="post" action="/_monitor/api" name="ping">
		<input type="hidden" name="method" value="ping">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">ping</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="/_event/api" name="addActionEvent">
		<input type="hidden" name="method" value="addActionEvent">
		<div class="apiMethod">
			<span class="h3 apiMethodTitle">addActionEvent</span>
			<div class="apiParameter">
				<span class="label apiLabel">code</span>
				<input type="text" name="code" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">description</span>
				<input type="text" name="description" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>