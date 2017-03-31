	<span class="title">Porkchop Spectros API Version <?=$_package["version"]?></span>
	<div id="apiScroller">
		<div class="apiMethod">Request</div>
		<pre id="requestContent" style="text-align: left; width: 550px; height: 100px; overflow: auto; font-size: 11px; border: 1px dashed blue;"><?=print_r($_REQUEST)?></pre>
		<form method="post" action="<?=PATH?>/_media/api" name="ping">
		<input type="hidden" name="apiMethod" value="ping">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">ping</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
			<form method="post" action="<?=PATH?>/_media/api" name="schemaVersion">
			<input type="hidden" name="method" value="schemaVersion">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">schemaVersion</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
		<form method="post" action="<?=PATH?>/_media/api" name="addMediaItem" enctype="multipart/form-data">
		<input type="hidden" name="method" value="addMediaItem">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">addMediaItem</div>
			<div class="apiParameter">
				<span class="label apiLabel">code</span>
				<input type="text" name="code" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">name</span>
				<input type="text" name="name" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">type</span>
				<input type="text" name="type" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">file</span>
				<input type="file" name="file" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_media/api" name="getMediaItem">
		<input type="hidden" name="method" value="getMediaItem">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">getMediaItem</div>
			<div class="apiParameter">
				<span class="label apiLabel">code</span>
				<input type="text" name="code" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_media/api" name="findMediaItems">
		<input type="hidden" name="method" value="findMediaItems">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">findMediaItems</div>
			<div class="apiParameter">
				<span class="label apiLabel">label</span>
				<input type="text" name="key[0]" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">value</span>
				<input type="text" name="value[0]" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">label</span>
				<input type="text" name="key[1]" class="value input apiInput"/>
			</div>
			<div class="apiParameter">
				<span class="label apiLabel">value</span>
				<input type="text" name="value[1]" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="<?=PATH?>/_media/api" name="downloadMediaFile">
		<input type="hidden" name="method" value="downloadMediaFile">
		<div class="apiMethod">
			<div class="h3 apiMethodTitle">downloadMediaFile</div>
			<div class="apiParameter">
				<span class="label apiLabel">code</span>
				<input type="text" name="code" class="value input apiInput"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
	</div>