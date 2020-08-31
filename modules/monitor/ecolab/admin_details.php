<?php	
    if (! $GLOBALS['_SESSION_']->customer->has_role('monitor admin')) {
		print "<span class=\"form_error\">You are not authorized for this view!</span>";
		return;
	}
?>
    <script language="Javascript">
    
        function goUrl(url) {
            location.href = url;
            return true;
        }
        
        function goMethodUrl(url) {
            location.href = '/_monitor/api/&amp;method='+url;
            return true;
        }
        
		function goAddTask() {
			location.href = '/_support/request_new_monitor?type=monitor&product_id=<?=$asset->product->id?>&code=<?=$asset->code?>';
		}
		
		function goAllTasks() {
			location.href = '/_support/request_items?product_id=<?=$asset->product->id?>&serial_number=<?=$asset->code?>';
		}
		
		function goCalibrationVerification() {
			document.mainform.action = '/_monitor/calibrate';
			document.mainform.code.value = '<?=$asset->code?>';
			document.mainform.submit();
		}
		
		function submitForm(assetID) {
			document.mainform.action = '/_monitor/details';
			document.mainform.id.value=assetID;
			document.mainform.submit();
			return true;
		}
    </script>
			<h2>Monitor Details</h2>
			<form name="mainform" method="post">
			<input type="hidden" name="method" value="submit" />
            <?php	if ($page->errorCount()) { ?>
			    <div class="form_error" style="width: 700px;"><?=$page->errorString()?></div>
            <?php	} ?>
            <?php	if ($page->success) { ?>
			            <div class="form_success" style="width: 700px;"><?=$page->success?></div>
            <?php	} ?>
      <div class="tableBody">
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
        <div class="tableRowHeader">
					<div class="tableCell">Serial Number</div>
					<div class="tableCell">Model</div>
					<div class="tableCell">Organization</div>
				</div>
            	<div class="tableRow">
					<div class="tableCell"><input type="text" name="asset_code" class="value input <?=isset($disabled_new) ? $disabled_new : ''?>" value="<?=$asset_code?>" /></div>
					<div class="tableCell">
						<select name="product_id" class="value input <?=$disabled?>">
							<option value="">Select</option>
                            <?php	foreach ($products as $product) { ?>
							    <option value="<?=$product->id?>"<?php if ($product_id == $product->id) print " selected";?>><?=$product->code?></option>
                            <?php	} ?>
						</select>
					</div>
					<div class="tableCell">
						<select name="organization_id" class="value input <?=$disabled?>">
							<option value="">Select</option>
                            <?php	foreach ($organizations as $organization) { ?>
							    <option value="<?=$organization->id?>"<?php if ($asset->organization->id == $organization->id) print " selected";?>><?=$organization->name?></option>
                            <?php	} ?>
						</select>
					</div>
				</div>
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
            	<div class="tableRowHeader">
					<div class="tableCell">Software Version</div>
					<div class="tableCell">Display</div>
					<div class="tableCell">Date Shipped</div>
				</div>
            	<div class="tableRow">
					<div class="tableCell"><input type="text" name="software_version" class="value input" value="<?=$asset->metadata('software_version')?>" /></div>
					<div class="tableCell"><input type="text" name="display_type" class="value input" value="<?=$asset->metadata('display_type')?>" /></div>
					<div class="tableCell"><input type="text" name="date_shipped" class="value input" value="<?=$asset->metadata('date_shipped')?>" /></div>
				</div>
				
            	<div class="tableRowHeader">
					<div class="tableCell">Cellular Information</div>
					<div class="tableCell">Value</div>
					<div class="tableCell">Add/Update</div>
				</div>
                <?php
                if (!$allCellularPopulated) {
                ?>	
                    <div class="tableRow">
                        <div class="tableCell">

                                <select name="cellularMetaDataAddType" class="value input" />
                                    <option value="">Select</option>
                                    <?php foreach ($asset->cellularMetaData as $cellularMetaData) { 
                                        if (!in_array($cellularMetaData, array_keys($allAssetMetaData))) {
                                    ?>			      
		                              <option value="<?=$cellularMetaData?>"><?=$cellularMetaData?></option>
	                              <?php
                                    }
		                        } ?>
                                </select>

                        </div>
                        <div class="tableCell">
	                        <input type="text" name="cellularMetaDataAddValue" class="value input" value="">
                        </div>
                        <div class="tableCell">
                            <input type="submit" name="btn_add_cellular" class="button" value="Add" />
                        </div>
                    </div>
                <?php
                }
				// update / delete for any existing cellularMetaData fields already present
				foreach ($allAssetMetaData as $assetMetaDataKey => $assetMetaDataValue) {
				    if (in_array($assetMetaDataKey, $asset->cellularMetaData)) {
				?>
                	<div class="tableRow">
					    <div class="tableCell">
                            <select name="cellularMetaDataEditType<?=$assetMetaDataKey?>" class="value input" disabled="disabled"/>
                                <option value="">Select</option>
                                <?php foreach ($asset->cellularMetaData as $cellularMetaData) { ?>
                                    <option value="<?=$cellularMetaData?>" <?=($assetMetaDataKey == $cellularMetaData) ? 'selected="selected"' : ''?>><?=$cellularMetaData?></option>
                                <?php } ?>
                            </select>
					    </div>
					    <div class="tableCell">
        					<input type="text" name="cellularMetaDataEditValue_<?=$assetMetaDataKey?>" class="value input" value="<?=$assetMetaDataValue?>">
					    </div>
					    <div class="tableCell">
					        <input type="submit" name="btn_update_cellular_<?=$assetMetaDataKey?>" class="button" style="font-size: 10px;" value="Update" />    
					        <input type="submit" name="btn_delete_cellular_<?=$assetMetaDataKey?>" class="button" style="font-size: 10px;" value="Delete" />
					    </div>
				    </div>
				<?php
				    }
				}
				?>
			</div>
		
        <?php if (!empty($monitorJobs)) { ?>
			<div class="tableTitle">
				<div class="tableCell">
					<div class="title tableTitleLeft">Recent Monitor Jobs</div>
				</div>
			</div>
          <div class="tableBody">
			<div class="tableRowHeader">
				<div class="tableCell">Job Name</div>
				<div class="tableCell">Job Code</div>
				<div class="tableCell">Status</div>
				<div class="tableCell">Start Time</div>
				<div class="tableCell">End Time</div>
			</div>
        <?php	foreach ($monitorJobs as $job) { ?>
	        <div class="tableRow">
		        <div class="tableCell"><?=$job->metadata('name')?></div>
		        <div class="tableCell"><a href="/_monitor/dashboard/<?=$job->code?>"><?=$job->code?></a></div>
		        <div class="tableCell"><?=$job->status?></div>
		        <div class="tableCell"><?=$job->date_start?></div>
		        <div class="tableCell"><?=$job->date_end?></div>
	        </div>
        <?php	} ?>
	</div>
<?php } ?>
<?php	if ($asset->id) { ?>
			<div class="tableTitle">
				<div class="tableCell">
					<div class="title tableTitleLeft">Last Communication</div>
				</div>
				<div class="tableCell">
					<div class="tableTitleRight">
						<input type="submit" name="btn_update" class="button" value="Apply" />
					</div>
				</div>
			</div>
			<div class="tableBody">
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
				<div class="tableRowHeader">
					<div class="tableCell">Date Hit</div>
					<div class="tableCell">IP Address</div>
					<div class="tableCell">URI</div>
					<div class="tableCell">Method</div>
					<div class="tableCell">Agent</div>
					<div class="tableCell">Status</div>
				</div>
                <?php  if ($communication->timestamp > 0) {
                    $timearray = $GLOBALS['_SESSION_']->localtime($communication->timestamp);
                    $request_time = sprintf("%d/%d/%04d %02d:%02d:%02d",$timearray['month'],$timearray['day'],$timearray['year'],$timearray['hour'],$timearray['minute'],$timearray['second']); 
                ?>
				<div class="tableRow">
					<div class="tableCell" nowrap><?=$request_time?></div>
					<div class="tableCell"><?=$request->client_ip?></div>
					<div class="tableCell"><?=$request->uri?></div>
					<div class="tableCell"><?=$request->post->method?></div>
					<div class="tableCell"><?=$request->user_agent?></div>
					<div class="tableCell"><?=$communication->result?></div>
				</div>
            <?php  } else { ?>
				<div class="tablerow">
					<div class="tableCell" style="width: 100%">None recorded</div>
				</div>
            <?php  } ?>
			</div>
			<div class="tableTitle">
				<div class="tableCell">
					<div class="title tableTitleLeft">Asset Sensors</div>
				</div>
				<div class="tableCell">
					<div class="tableTitleRight">
						<input type="submit" name="btn_update" class="button" value="Apply" />
					</div>
				</div>
			</div>
			<div class="tableBody">
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
				<div class="tableRowHeader">
					<div class="tableCell">Code</div>
					<div class="tableCell">Model</div>
					<div class="tableCell">Units</div>
					<div class="tableCell">System</div>
					<div class="tableCell">Last Value</div>
					<div class="tableCell">Last Read</div>
				</div>
                <?php		foreach ($sensors as $sensor) {
				    $reading = $sensor->lastReading();
                ?>
				<div class="tableRow">
					<div class="tableCell"><input type="text" name="sensor_code[<?=$sensor->id?>]" class="value input" value="<?=$sensor->code?>" <?=$disabled?> /></div>
					<div class="tableCell"><select name="model_id[<?=$sensor->id?>]" class="value input" />
						<option value="">Select</option>
                    <?php		foreach ($models as $model) { ?>
						<option value="<?=$model->id?>"<?php if ($model->id == $sensor->model_id) print " selected"; ?>><?=$model->code?></option>
                    <?php		} ?>
						</select>
					</div>
					<div class="tableCell"><?=$sensor->model->units?></div>
					<div class="tableCell"><input type="checkbox" name="system[<?=$sensor->id?>]" value="1"<?php if ($sensor->system() ) print " checked"; ?> /></div>
					<div class="tableCell"><?=$reading->value?></div>
					<div class="tableCell"><?php if (isset($reading->timestamp)) print date('m/d/Y H:i:s',$reading->timestamp);?></div>
				</div>
                <?php		} ?>
				<div class="tableCell"><input type="text" name="sensor_code[0]" class="value input" value="" <?=$disabled?> /></div>
				<div class="tableCell"><select name="model_id[0]" class="value input" />
						<option value="">Select</option>
                        <?php		foreach ($models as $model) { ?>
						    <option value="<?=$model->id?>"><?=$model->code?></option>
                        <?php		} ?>
						</select>
					</div>
				<div class="tableCell">&nbsp;</div>
				<div class="tableCell"><input type="checkbox" name="system[0]" value="1" /></div>
			</div>
			<div class="tableTitle">
				<div class="tableCell">
					<div class="title tableTitleLeft">Asset Tickets</div>
				</div>
				<div class="tableCell">
					<div class="tableTitleRight">
						<input type="button" name="btn_add_task" class="button secondary" value="Add Ticket" onclick="goAddTask()" />
						<input type="button" name="btn_all_tasks" class="button secondary" value="All Tickets" onclick="goAllTasks()" />
					</div>
				</div>
			</div>
			<div class="tableBody">
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
				<div class="tableRowHeader">
					<div class="tableCell">#</div>
					<div class="tableCell">Date Requested</div>
					<div class="tableCell">By</div>
					<div class="tableCell">Status</div>
					<div class="tableCell">Assignee</div>
				</div>
                <?php	foreach ($tickets as $ticket) { ?>
				    <div class="tableRow">
					    <div class="tableCell"><a href="/_support/request_item/<?=$ticket->id?>"><?=$ticket->ticketNumber()?></a></div>
					    <div class="tableCell"><?=$ticket->request->date_request?></div>
					    <div class="tableCell"><?=$ticket->request->customer->full_name()?></div>
					    <div class="tableCell"><?=$ticket->status?></div>
					    <div class="tableCell"><?=$ticket->assigned->full_name()?></div>
				    </div>
                <?php	} ?>
			</div>
			<div class="tableTitle">
				<div class="tableCell">
					<div class="title tableTitleLeft">Calibration History</div>
				</div>
				<div class="tableCell">
					<div class="tableTitleRight">
						<input type="button" name="btn_add_task" class="button secondary" value="Record Calibration" onclick="goCalibrationVerification()" />
						<input type="submit" name="btn_update" class="button" value="Apply" />
					</div>
				</div>
			</div>
			<div class="tableBody">
				<div class="tableRowHeader">
					<div class="tableCell">Date Request</div>
					<div class="tableCell">By</div>
				</div>
			</div>
			<div class="tableBodyWrapper">
				<div class="tableBodyScrolled">
                <?php		foreach ($calibrations as $calibration) {
			                $calibrator = new \Register\Customer($calibration->customer->id);
                ?>
					<div class="tableRow">
						<div class="tableCell"><?=$calibration->date_request?></div>
						<div class="tableCell"><?=$calibrator->first_name." ".$calibrator->last_name." of ".$calibrator->organization->name?></div>
					</div>
                <?php		} ?>
				</div>
			</div>
			<div class="tableBody">
				<div class="tableRowFooter">
					<div class="tableCell button-bar"><input type="submit" name="btn_submit" class="button" value="Update Asset"/></div>
                <?php	
                    } else {
                ?>
			<div class="tableBody">
				<div class="tableRowFooter">
					<div class="tableCell"><input type="submit" name="btn_submit" class="button" value="Add Asset"/></div>
                <?php	} ?>
				</div>
			</div>
			<div class="tableTitle">
				<div class="tableCell">
					<div class="title tableTitleLeft">Messages</div>
				</div>
				<div class="tableCell">
					<div class="tableTitleRight">
						<input type="submit" name="btn_update" class="button" value="Apply" />
					</div>
				</div>
			</div>
			<div class="tableBody">
				<div class="tableRowHeader">
					<div class="tableCell">Date</div>
					<div class="tableCell">Level</div>
					<div class="tableCell">Message</div>
				</div>
                <?php		foreach ($messages as $message) { ?>
				    <div class="tableRow">
					    <div class="tableCell"><?=$message->date_recorded?></div>
					    <div class="tableCell"><?=$message->level?></div>
					    <div class="tableCell"><?=$message->message?></div>
				    </div>
                <?php		} ?>
			</div>
			</form>
