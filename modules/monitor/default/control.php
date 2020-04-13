<div class="pageBody pageMonitorBody pageMonitorControlBody">
    <form name="controlForm" method="post">
<?php  for ($zone = 1; $zone <= 8; $zone ++) { ?>
    <div id="zone<?=$zone?>" class="zoneBlock">
        <input type="hidden" name="zone_id<?=$zone?>">
        <div class="zoneHeader">
            <div class="label titleLabel">
                Zone <?=sprintf("%02d",$zone)?>
            </div>
            <div id="controlButtonBox<?=$zone?>" class="controlButton">
                <input type="button" id="controlButton<?=$zone?>" class="startButton" onclick="updateZone()">
            </div>
            <div id="controlTitle<?=$zone?>" class="controlTitle">
                <input type="text" name="controlTitle<?=$zone?>" class="input controlTitleInput">
            </div>
        </div>
        <div class="zoneBody">
            <div class="zoneCurrent">
                <div class="label currentTitle">Current</div>
                <div id="current<?=$zone?>" class="value currentValue">0.00</div>
                <div class="units currentUnits">ppm</div>
            </div>
            <div class="zoneLast">
                <div class="lastMin">
                    <div class="label lastMinLabel">Min</div>
                    <div id="min<?=$zone?>" class="value minValue">0.00</div>
                </div>
                <div class="lastMax">
                    <div class="label lastMaxLabel">Max</div>
                    <div id="max<?=$zone?>" class="value maxValue">0.00</div>
                </div>
                <div class="lastAvg">
                    <div class="label lastAvgLabel">Avg</div>
                    <div id="avg<?=$zone?>" class="value avgValue">0.00</div>
                </div>
            </div>
            <div class="zoneTime">
                <div class="label timeLabel">Run Time</div>
                <div id="time<?=$zone?>" class="value timeValue"></div>
                <div id="">
                    <input type="button" id="settings<?=$zone?>" class="settingsButton" value="SETTINGS" onclick="showSettings()"
                </div>
            </div>
        </div>
    </div>
<?php  } ?>
</form>
</div>
