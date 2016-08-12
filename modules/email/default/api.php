		<span class="title">Porkchop Spectros API Version <?=$_package["version"]?></span>
		<div id="scroller" style="width: 600px; height: 500px; overflow: auto; margin-left: 50px;">
			<form method="post" action="/_email/api" name="ping">
			<input type="hidden" name="method" value="ping">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">ping</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="/_email/api" name="sendEmail">
			<input type="hidden" name="method" value="sendEmail">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">sendEmail</span>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">from</span>
					<input type="text" name="from" class="value input apiInput"/>
				</div>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">to</span>
					<input type="text" name="to" class="value input apiInput"/>
				</div>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">subject</span>
					<input type="text" name="subject" class="value input apiInput"/>
				</div>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">body</span>
					<input type="text" name="body" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
		</div>