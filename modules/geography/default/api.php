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
		<form method="post" action="<?=PATH?>/_geography/api" name="addCountry">
		<input type="hidden" name="method" value="addCountry">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addCountry</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="code" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_geography/api" name="updateCountry">
		<input type="hidden" name="method" value="updateCountry">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">updateCountry</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">status</span>
				<input type="text" name="status" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_geography/api" name="getCountry">
		<input type="hidden" name="method" value="getCountry">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">getCountry</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_geography/api" name="findCountries">
		<input type="hidden" name="method" value="findCountries">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findCountries</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_geography/api" name="addProvince">
		<input type="hidden" name="method" value="addProvince">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addProvince</div>
			<div class="apiParameter">
				<span class="label apiLabel">country_id</span>
				<input type="text" name="country_id" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_geography/api" name="updateProvince">
		<input type="hidden" name="method" value="updateProvince">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">updateProvince</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">country_id</span>
				<input type="text" name="country_id" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_geography/api" name="getProvince">
		<input type="hidden" name="method" value="getProvince">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">getProvince</div>
			<div class="apiParameter">
				<span class="label apiLabel">country_id</span>
				<input type="text" name="country_id" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_geography/api" name="findProvinces">
		<input type="hidden" name="method" value="findProvinces">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findProvinces</div>
			<div class="apiParameter">
				<span class="label apiLabel">country_id</span>
				<input type="text" name="country_id" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
	</div>