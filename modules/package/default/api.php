		<span class="title">Porkchop Package API Version <?=$_package["version"]?></span>
		<div id="apiScroller">
			<form method="post" action="<?=PATH?>/_package/api" name="ping">
			<input type="hidden" name="method" value="ping">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">ping</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_package/api" name="schemaVersion">
			<input type="hidden" name="method" value="schemaVersion">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">schemaVersion</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_package/api" name="addPackage">
			<input type="hidden" name="method" value="addPackage">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">addPackage</span>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">name</span>
					<input type="text" name="name" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">description</span>
					<input type="text" name="description" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">license</span>
					<input type="text" name="license" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">platform</span>
					<input type="text" name="platform" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">repository_code</span>
					<input type="text" name="repository_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">status</span>
					<input type="text" name="status" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_package/api" name="updatePackage">
			<input type="hidden" name="method" value="updatePackage">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">updatePackage</span>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">name</span>
					<input type="text" name="name" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">description</span>
					<input type="text" name="description" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">license</span>
					<input type="text" name="license" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">platform</span>
					<input type="text" name="platform" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">repository_code</span>
					<input type="text" name="repository_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">status</span>
					<input type="text" name="status" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_package/api" name="findPackages">
			<input type="hidden" name="method" value="findPackages">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">findPackages</span>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">name</span>
					<input type="text" name="name" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">platform</span>
					<input type="text" name="platform" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">repository_code</span>
					<input type="text" name="repository_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">status</span>
					<input type="text" name="status" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_package/api" name="addVersion" enctype="multipart/form-data">
			<input type="hidden" name="method" value="addVersion">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">addVersion</span>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">package_code</span>
					<input type="text" name="package_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">major</span>
					<input type="text" name="major" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">minor</span>
					<input type="text" name="minor" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">build</span>
					<input type="text" name="build" class="value input apiInput"/>
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
			<form method="post" action="<?=PATH?>/_package/api" name="updateVersion">
			<input type="hidden" name="method" value="updateVersion">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">updateVersion</span>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">package_code</span>
					<input type="text" name="package_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">major</span>
					<input type="text" name="major" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">minor</span>
					<input type="text" name="minor" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel apiLabelRequired">build</span>
					<input type="text" name="build" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">status</span>
					<input type="text" name="status" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_package/api" name="findVersions">
			<input type="hidden" name="method" value="findVersions">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">findVersions</span>
				<div class="apiParameter">
					<span class="label apiLabel">package_code</span>
					<input type="text" name="package_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">major</span>
					<input type="text" name="major" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">minor</span>
					<input type="text" name="minor" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">build</span>
					<input type="text" name="build" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">status</span>
					<input type="text" name="status" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_package/api" name="downloadVersion">
			<input type="hidden" name="method" value="downloadVersion">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">downloadVersion</span>
				<div class="apiParameter">
					<span class="label apiLabel">package_code</span>
					<input type="text" name="package_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">major</span>
					<input type="text" name="major" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">minor</span>
					<input type="text" name="minor" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">build</span>
					<input type="text" name="build" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_package/api" name="latestVersion">
			<input type="hidden" name="method" value="latestVersion">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">latestVersion</span>
				<div class="apiParameter">
					<span class="label apiLabel">package_code</span>
					<input type="text" name="package_code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_package/api" name="downloadLatestVersion">
			<input type="hidden" name="method" value="downloadLatestVersion">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">downloadLatestVersion</span>
				<div class="apiParameter">
					<span class="label apiLabel">package_code</span>
					<input type="text" name="package_code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
		</div>
