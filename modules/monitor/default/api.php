		<span class="title">Porkchop Monitor API Version <?=$_package["version"]?></span>
		<div id="apiScroller">
			<form method="post" action="<?=PATH?>/_monitor/api" name="ping">
			<input type="hidden" name="method" value="ping">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">ping</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="schemaVersion">
			<input type="hidden" name="method" value="schemaVersion">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">schemaVersion</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="getAsset">
			<input type="hidden" name="method" value="getAsset">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">getAsset</span>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="addAsset">
			<input type="hidden" name="method" value="addAsset">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">addAsset</span>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">product_code</span>
					<input type="text" name="product_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">name</span>
					<input type="text" name="name" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="updateAsset">
			<input type="hidden" name="method" value="updateAsset">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">updateAsset</div>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">product_code</span>
					<input type="text" name="product_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">name</span>
					<input type="text" name="name" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="findAssets">
			<input type="hidden" name="method" value="findAssets">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">findAssets</div>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">product_code</span>
					<input type="text" name="product_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">name</span>
					<input type="text" name="name" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="addSensor">
			<input type="hidden" name="method" value="addSensor">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">addSensor</div>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">asset_code</span>
					<input type="text" name="asset_code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="updateSensor">
			<input type="hidden" name="method" value="updateSensor">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">updateSensor</div>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">asset_code</span>
					<input type="text" name="asset_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">units</span>
					<input type="text" name="units" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="getSensor">
			<input type="hidden" name="method" value="getSensor">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">getSensor</div>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">asset_code</span>
					<input type="text" name="asset_code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="findSensors">
			<input type="hidden" name="method" value="findSensors">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">findSensors</div>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">asset_code</span>
					<input type="text" name="asset_code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="addReading">
			<input type="hidden" name="method" value="addReading">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">addReading</div>
				<div class="apiParameter">
					<span class="label apiLabel">sensor_code</span>
					<input type="text" name="sensor_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">asset_code</span>
					<input type="text" name="asset_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">date_reading</span>
					<input type="text" name="date_reading" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">value</span>
					<input type="text" name="value" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="findReadings">
			<input type="hidden" name="method" value="findReadings">
			<div class="method">
				<div class="h3 apiMethodTitle">findReadings</div>
				<div class="apiParameter">
					<span class="label apiLabel">sensor_code</span>
					<input type="text" name="sensor_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">asset_code</span>
					<input type="text" name="asset_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">date_start</span>
					<input type="text" name="date_start" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">date_end</span>
					<input type="text" name="date_end" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="addCollection">
			<input type="hidden" name="method" value="addCollection">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">addCollection</div>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">date_start</span>
					<input type="text" name="date_start" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">date_end</span>
					<input type="text" name="date_end" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">status</span>
					<input type="text" name="status" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="setCollectionMetadata">
			<input type="hidden" name="method" value="setCollectionMetadata">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">setCollectionMetadata</div>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">label</span>
					<input type="text" name="label" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">value</span>
					<input type="text" name="value" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="getCollectionMetadata">
			<input type="hidden" name="method" value="getCollectionMetadata">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">getCollectionMetadata</div>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">label</span>
					<input type="text" name="label" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="updateCollection">
			<input type="hidden" name="method" value="updateCollection">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">updateCollection</div>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">organization_id</span>
					<input type="text" name="organization_id" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">date_start</span>
					<input type="text" name="date_start" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">date_end</span>
					<input type="text" name="date_end" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">status</span>
					<input type="text" name="status" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="getCollection">
			<input type="hidden" name="method" value="getCollection">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">getCollection</div>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="findCollections">
			<input type="hidden" name="method" value="findCollections">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">findCollections</div>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">date_start</span>
					<input type="text" name="date_start" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">date_end</span>
					<input type="text" name="date_end" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">status</span>
					<input type="text" name="status" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="setCollectionMetadata">
			<input type="hidden" name="method" value="setCollectionMetadata">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">setCollectionMetadata</div>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">label</span>
					<input type="text" name="label" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">value</span>
					<input type="text" name="value" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="findCollectionSensors">
			<input type="hidden" name="method" value="findCollectionSensors">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">findCollectionSensors</div>
				<div class="apiParameter">
					<span class="label apiLabel">collection_code</span>
					<input type="text" name="collection_code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="collectionReadings">
			<input type="hidden" name="method" value="collectionReadings">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">collectionReadings</div>
				<div class="apiParameter">
					<span class="label apiLabel">collection_id</span>
					<input type="text" name="collection_id" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">collection_code</span>
					<input type="text" name="collection_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">asset_code</span>
					<input type="text" name="asset_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">sensor_code</span>
					<input type="text" name="sensor_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">date_start</span>
					<input type="text" name="date_start" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">date_end</span>
					<input type="text" name="date_end" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">_timestamp</span>
					<input type="text" name="_timestamp" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="addMessage">
			<input type="hidden" name="method" value="addMessage">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">addMessage</div>
				<div class="apiParameter">
					<span class="label apiLabel">asset_code</span>
					<input type="text" name="asset_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">sensor_code</span>
					<input type="text" name="sensor_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">collection_code</span>
					<input type="text" name="collection_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">date_recorded</span>
					<input type="text" name="date_recorded" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">message</span>
					<input type="text" name="message" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">level</span>
					<input type="text" name="level" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="findMessages">
			<input type="hidden" name="method" value="findMessages">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">findMessages</div>
				<div class="apiParameter">
					<span class="label apiLabel">asset_code</span>
					<input type="text" name="asset_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">sensor_code</span>
					<input type="text" name="sensor_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">collection_code</span>
					<input type="text" name="collection_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">date_recorded</span>
					<input type="text" name="date_recorded" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">_limit</span>
					<input type="text" name="_limit" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_monitor/api" name="dygraphData">
			<input type="hidden" name="method" value="dygraphData">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">dygraphData</div>
				<div class="apiParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
		</div>