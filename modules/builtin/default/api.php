		<span class="title">Porkchop Register API Version <?=$_package["version"]?></span>
		<div id="scroller" style="width: 600px; height: 500px; overflow: auto; margin-left: 50px;">
			<div class="h3 apiMethodTitle">Request</div>
			<pre id="requestContent" style="text-align: left; width: 550px; height: 100px; overflow: auto; font-size: 11px; border: 1px dashed blue;"><?=print_r($_REQUEST)?></pre>
			<form method="post" action="<?=PATH?>/_builtin/api" name="ping">
			<input type="hidden" name="method" value="ping">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">ping</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_builtin/api" name="schemaVersion">
			<input type="hidden" name="method" value="schemaVersion">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">schemaVersion</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_builtin/api" name="findLocations">
			<input type="hidden" name="method" value="findLocations">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">findLocations</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">organization_code</span>
					<input type="text" name="organization_code" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
		</div>
	</div>
</div>
</body>
</html>
