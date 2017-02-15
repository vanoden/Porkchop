<script type="text/javascript" src="/js/dygraph-combined.js"></script>
<script type="text/javascript" src="/js/monitor.dashboard.js"></script>
<script language="javascript" type="text/javascript" src="/jsCalendar/datetimepicker.js"></script>
<style>
	select.value {
		font-size: 10px;
		width: 140px;
		border: 0px;
		margin-top: 2px;
	}
</style>
<form name="consoleform" action="/_monitor/console" method="post">
<input type="hidden" id="code" name="code" value="<?=$collection->code?>"/>
<input type="hidden" id="collection_id" name="collection_id" value="<?=$collection->id?>"/>
<input type="hidden" name="method" value="" />
<div id="sensors" class="widget_container">
	<span id="sensors_header" class="widget_header">Sensors</span>
	<div id="sensor_scroll"></div>
	<div class="sensor_add" style="text-align: center; padding-top: 4px;">
		<input id="sensor_add_btn" type="button" name="sensor_add_button" value="Add Sensor" onclick="sensorEntry()"/>
	</div>
</div>
<div id="graph" class="widget_container">
	<span id="graph_header" class="widget_header">Graph</span>
	<div id="graphBackground"></div>
	<div id="graphContainer"></div>
	<div id="graphLegend"></div>
	<div id="logo"></div>
</div>
<div id="control" class="widget_container">
	<span id="control_header" class="widget_header">Collection Control</span>
	<div id="messages_container">
		<span class="detail_container_header">Messages</span>
		<textarea id="collection_messages" style="width: 98%; clear: both; height: 145px; border: 0px;">No new messages</textarea>
	</div>
	<div id="controls_container" class="text-align: center">
		<input type="button" id="btn_home" value="Home" onclick="goHome()" />
		<input type="button" id="btn_refresh" value="Refresh" onclick="reloadPage()" />
		<input type="button" id="btn_export" value="Export" onclick="exportData()" />
	</div>
</div>
<div id="details" class="widget_container">
	<span id="details_header" class="widget_header">Collection Details</span>
	<div id="collection_container" style="width: 100%;clear: both">
		<span class="detail_container_header">Collection</span>
		<div id="coll_name_container" class="collection_form_container">
			<span class="label dashboardDetailLabel">Name</span>
			<input type="text" id="name" class="value input collectionField" value="<?=$collection->metadata('name')?>" onMouseOver="collectionFieldOver(this)" onMouseOut="collectionFieldOut(this)" onFocus="collectionFieldFocus(this)" onBlur="collectionFieldBlur(this)" onChange="updateCollectionField(this)" />
		</div>
		<div id="commodity_container" class="collection_form_container">
			<span class="label dashboardDetailLabel">Commodity</span>
			<input type="text" id="commodity" class="value input collectionField" value="<?=$collection->metadata('commodity')?>" onMouseOver="collectionFieldOver(this)" onMouseOut="collectionFieldOut(this)" onFocus="collectionFieldFocus(this)" onBlur="collectionFieldBlur(this)" onChange="updateCollectionField(this)" />
		</div>
		<div id="commodity_container" class="collection_form_container">
			<span class="label dashboardDetailLabel">Fumigant</span>
			<input type="text" id="fumigant" class="value input collectionField" value="<?=$collection->metadata('fumigant')?>" onMouseOver="collectionFieldOver(this)" onMouseOut="collectionFieldOut(this)" onFocus="collectionFieldFocus(this)" onBlur="collectionFieldBlur(this)" onChange="updateCollectionField(this)" />
		</div>
	</div>
	<div id="customer_container" style="width: 100%; clear: both">
		<span class="detail_container_header">Customer</span>
		<div id="cust_name_container" class="collection_form_container">
			<span class="label dashboardDetailLabel">Name</span>
			<input type="text" id="customer" class="value input collectionField" value="<?=$collection->metadata('customer')?>" onMouseOver="collectionFieldOver(this)" onMouseOut="collectionFieldOut(this)" onFocus="collectionFieldFocus(this)" onBlur="collectionFieldBlur(this)" onChange="updateCollectionField(this)" />
		</div>
		<div id="location_container" class="collection_form_container">
			<span class="label dashboardDetailLabel">Location</span>
			<input type="text" id="location" class="value input collectionField" value="<?=$collection->metadata('location')?>" onMouseOver="collectionFieldOver(this)" onMouseOut="collectionFieldOut(this)" onFocus="collectionFieldFocus(this)" onBlur="collectionFieldBlur(this)" onChange="updateCollectionField(this)" />
		</div>
		<div id="contact_container" class="collection_form_container">
			<span class="label dashboardDetailLabel">Contact</span>
			<input type="text" id="contact" class="value input collectionField" value="<?=$collection->metadata('contact')?>" onMouseOver="collectionFieldOver(this)" onMouseOut="collectionFieldOut(this)" onFocus="collectionFieldFocus(this)" onBlur="collectionFieldBlur(this)" onChange="updateCollectionField(this)" />
		</div>
	</div>
	<div id="status_container" style="width: 100%; clear: both">
		<span class="detail_container_header">Date/Time</span>
		<div id="current_status_container" class="collection_form_container">
			<span class="label dashboardDetailLabel" style="display: block; float: left">Timezone</span>
			<span class="value dashboardDetailValue" style="display: block; float: left"><?=$GLOBALS['_SESSION_']->timezone?></span>
		</div>
		<div id="start_container" class="collection_form_container">
			<span class="label dashboardDetailLabel">Started</span>
			<input type="text" id="date_start" class="value input collectionField dateCollectionField" value="<?=date('m/d/Y H:i',$collection->date_start)?>" onMouseOver="collectionFieldOver(this)" onMouseOut="collectionFieldOut(this)" onFocus="collectionFieldFocus(this)" onBlur="collectionFieldBlur(this)" onChange="updateCollectionField(this)" /></a>
		</div>
		<div id="end_container" class="collection_form_container">
			<span class="label dashboardDetailLabel">Ends</span>
			<input type="text" id="date_end" class="value input collectionField dateCollectionField" value="<?=date('m/d/Y H:i',$collection->date_end)?>" onMouseOver="collectionFieldOver(this)" onMouseOut="collectionFieldOut(this)" onFocus="collectionFieldFocus(this)" onBlur="collectionFieldBlur(this)" onChange="updateCollectionField(this)" /></a>
		</div>
	</div>
	<div id="condition_container" style="width: 100%; clear: both">
		<span class="detail_container_header">Conditions</span>
		<div id="tempurature_container" class="collection_form_container">
			<span class="label dashboardDetailLabel">Temperature</span>
			<input type="text" id="temperature" class="value input collectionField" value="<?=$collection->temperature?>" onMouseOver="collectionFieldOver(this)" onMouseOut="collectionFieldOut(this)" onFocus="collectionFieldFocus(this)" onBlur="collectionFieldBlur(this)" onChange="updateCollectionField(this)" />
		</div>
		<div id="temp_units_container" class="collection_form_container">
			<span class="label dashboardDetailLabel">Units</span>
			<input type="text" id="temp_units" class="value input collectionField" value="<?=$collection->temp_units?>" onMouseOver="collectionFieldOver(this)" onMouseOut="collectionFieldOut(this)" onFocus="collectionFieldFocus(this)" onBlur="collectionFieldBlur(this)" onChange="updateCollectionField(this)" />
		</div>
	</div>
	<div id="concentration_container" style="width: 100%; clear: both">
		<span class="detail_container_header">Concentration</span>
		<div id="conc_min_container" class="collection_form_container">
			<span class="label dashboardDetailLabel">Min CT</span>
			<input type="text" id="concentration" class="value input collectionField" value="<?=$collection->concentration?>" onMouseOver="collectionFieldOver(this)" onMouseOut="collectionFieldOut(this)" onFocus="collectionFieldFocus(this)" onBlur="collectionFieldBlur(this)" onChange="updateCollectionField(this)" />
		</div>
		<div id="conc_units_container" class="collection_form_container">
			<span class="label dashboardDetailLabel">Units</span>
			<input type="text" id="conc_units" class="value input collectionField" value="<?=$collection->conc_units?>" onMouseOver="collectionFieldOver(this)" onMouseOut="collectionFieldOut(this)" onFocus="collectionFieldFocus(this)" onBlur="collectionFieldBlur(this)" onChange="updateCollectionField(this)" />
		</div>
	</div>
</div>
<div id="sensorSelect" style="visibility: hidden; width: 305px; height: 200px; position: absolute; top: 100px; left: 100px; border: 1px solid black; background-color: white; margin: 5px; padding: 5px;">
	<div style="width: 300px;">Enter the following information to begin polling this sensor</div>
	<div style="width: 300px;">
		<div>Select Asset</div>
		<div><select id="newAsset"></select></div>
	</div>
	<div style="width: 300px;">
		<div>Enter Sensor</div>
		<div><input id="newSensor" style="width: 100px;" /></div>
	</div>
	<div style="text-align: center; width: 200px; height: 15px;">
		<input type="button" onclick="addSensor()" value="Add Sensor">
		<input type="button" onclick="cancelAdd()" value="Cancel">
	</div>
</div>
</form>