		<span class="title">Porkchop Benchtools API Version <?=$_package["version"]?></span>
		<div id="scroller" style="width: 600px; height: 500px; overflow: auto; margin-left: 50px;">
			<form method="post" action="/_bench/api" name="ping">
			<input type="hidden" name="method" value="ping">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">ping</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="/_bench/api" name="registerAsset">
			<input type="hidden" name="method" value="registerAsset">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">registerAsset</span>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
		</div>