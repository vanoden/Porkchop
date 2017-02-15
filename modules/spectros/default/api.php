		<span class="title">Porkchop Spectros API Version <?=$_package["version"]?></span>
		<div id="scroller" style="width: 600px; height: 500px; overflow: auto; margin-left: 50px;">
			<form method="post" action="/_spectros/api" name="ping">
			<input type="hidden" name="method" value="ping">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">ping</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="/_spectros/api" name="findCalibrationVerificationCredits">
			<input type="hidden" name="method" value="findCalibrationVerificationCredits">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">findCalibrationVerificationCredits</span>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">organization</span>
					<input type="text" name="organization" class="value input apiInput"/>
				</div>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">status</span>
					<input type="text" name="status" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="/_spectros/api" name="getCalibrationVerificationCredits">
			<input type="hidden" name="method" value="getCalibrationVerificationCredits">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">getCalibrationVerificationCredits</span>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">organization</span>
					<input type="text" name="organization" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="/_spectros/api" name="addCalibrationVerificationCredits">
			<input type="hidden" name="method" value="addCalibrationVerificationCredits">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">addCalibrationVerificationCredits</span>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">organization</span>
					<input type="text" name="organization" class="value input apiInput"/>
				</div>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">quantity</span>
					<input type="text" name="quantity" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="/_spectros/api" name="consumeCalibrationVerificationCredit">
			<input type="hidden" name="method" value="consumeCalibrationVerificationCredit">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">consumeCalibrationVerificationCredit</span>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">organization</span>
					<input type="text" name="organization" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="/_spectros/api" name="addCalibrationVerification">
			<input type="hidden" name="method" value="addCalibrationVerification">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">addCalibrationVerification</span>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">asset</span>
					<input type="text" name="asset" class="value input apiInput"/>
				</div>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">date_calibration</span>
					<input type="text" name="date_calibration" class="value input apiInput"/>
				</div>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">standard_manufacturer</span>
					<input type="text" name="standard_manufacturer" class="value input apiInput"/>
				</div>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">standard_concentration</span>
					<input type="text" name="standard_concentration" class="value input apiInput"/>
				</div>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">standard_expires</span>
					<input type="text" name="standard_expires" class="value input apiInput"/>
				</div>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">monitor_reading</span>
					<input type="text" name="monitor_reading" class="value input apiInput"/>
				</div>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">cylinder_number</span>
					<input type="text" name="cylinder_number" class="value input apiInput"/>
				</div>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">detector_voltage</span>
					<input type="text" name="detector_voltage" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="/_spectros/api" name="getCalibrationVerification">
			<input type="hidden" name="method" value="getCalibrationVerification">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">getCalibrationVerification</span>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="/_spectros/api" name="nextCalibrationVerification">
			<input type="hidden" name="method" value="nextCalibrationVerification">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">nextCalibrationVerification</span>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">asset</span>
					<input type="text" name="asset" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="/_spectros/api" name="confirmCalibrationVerification">
			<input type="hidden" name="method" value="confirmCalibrationVerification">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">confirmCalibrationVerification</span>
				<div class="apiParameter apiParameterRequired">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="/_spectros/api" name="findCalibrationVerifications">
			<input type="hidden" name="method" value="findCalibrationVerifications">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">findCalibrationVerifications</span>
				<div class="apiParameter">
					<span class="label apiLabel">asset_code</span>
					<input type="text" name="code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">organization_code</span>
					<input type="text" name="product_code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="/_spectros/api" name="getCollectionCT">
			<input type="hidden" name="method" value="getCollectionCT">
			<div class="apiMethod">
				<span class="h3 apiMethodTitle">getCollectionCT</span>
				<div class="apiParameter">
					<span class="label apiLabel">collection_code</span>
					<input type="text" name="collection_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">monitor_code</span>
					<input type="text" name="monitor_code" class="value input apiInput"/>
				</div>
				<div class="apiParameter">
					<span class="label apiLabel">sensor_code</span>
					<input type="text" name="sensor_code" class="value input apiInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
		</div>