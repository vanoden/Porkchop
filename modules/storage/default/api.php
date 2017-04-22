		<span class="title">Porkchop Storage API Version <?=$_package["version"]?></span>
		<div id="apiScroller">
			<form method="post" action="<?=PATH?>/_storage/api" name="ping">
			<input type="hidden" name="method" value="ping">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">ping</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_storage/api" name="schemaVersion">
			<input type="hidden" name="method" value="schemaVersion">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">schemaVersion</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_storage/api" name="addRepository">
			<input type="hidden" name="method" value="addRepository">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">addRepository</span>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">name</span>
					<input type="text" name="name" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">type</span>
					<input type="text" name="type" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">status</span>
					<input type="text" name="status" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_storage/api" name="updateRepository">
			<input type="hidden" name="method" value="updateRepository">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">updateRepository</span>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">code</span>
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
					<span class="label apiLabel">status</span>
					<input type="text" name="status" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_storage/api" name="setRepositoryMetadata">
			<input type="hidden" name="method" value="setRepositoryMetadata">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">setRepositoryMetadata</span>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">key</span>
					<input type="text" name="key" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">value</span>
					<input type="text" name="value" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_storage/api" name="findRepositories">
			<input type="hidden" name="method" value="findRepositories">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">findRepositories</span>
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
					<span class="label apiLabel">status</span>
					<input type="text" name="status" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_storage/api" name="addFile" enctype="multipart/form-data">
			<input type="hidden" name="method" value="addFile">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">addFile</span>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">repository_code</span>
					<input type="text" name="repository_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">name</span>
					<input type="text" name="name" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">path</span>
					<input type="text" name="path" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">mime_type</span>
					<input type="text" name="mime_type" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">status</span>
					<input type="text" name="status" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">file</span>
					<input type="file" name="file" class="value input apiInput apiFileInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_storage/api" name="updateFile">
			<input type="hidden" name="method" value="updateFile">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">updateFile</span>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">name</span>
					<input type="text" name="name" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">mime-type</span>
					<input type="text" name="mime-type" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">status</span>
					<input type="text" name="status" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_storage/api" name="deleteFile">
			<input type="hidden" name="method" value="deleteFile">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">deleteFile</span>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_storage/api" name="findFiles">
			<input type="hidden" name="method" value="findFiles">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">findFiles</span>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">repository_code</span>
					<input type="text" name="repository_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">name</span>
					<input type="text" name="name" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">mime-type</span>
					<input type="text" name="mime-type" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">status</span>
					<input type="text" name="status" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_storage/api" name="downloadFile">
			<input type="hidden" name="method" value="downloadFile">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">downloadFile</span>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
		</div>