<style>
    input[type=button] {
        cursor: pointer;
    }
</style>
<span class="title">Currently Watched Site Counters</span>

<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>

<?php	} else if ($page->success) { ?>
<section id="form-message">
	<ul class="connectBorder progressText">
		<li><?=$page->success?></li>
	</ul>
</section>
<?php	} ?>

<div class="tableBody min-tablet">
    <div class="tableRowHeader">
        <div class="tableCell" style="width: 12%;">Key</div>
        <div class="tableCell" style="width: 12%;">Notes</div>        
        <div class="tableCell" style="width: 12%;">Value</div>
        <div class="tableCell" style="width: 12%;">Action</div>
    </div>
    <?php	
    $count = 0;
    foreach ($countersWatchedList as $completeCounterListItem) {
    ?>
        <div class="tableRow">
            <div class="tableCell">
                <input id="removeKeyExisting-<?=$count?>" type="text" readonly='readonly' value="<?=$completeCounterListItem->key?>"/>
            </div>
            <div class="tableCell">
                <input id="removeNotesExisting-<?=$count?>" type="text" readonly='readonly' value="<?=$completeCounterListItem->notes?>"/>
            </div>
           <div class="tableCell">
                Cached Value: 
                <?php
                    $siteCounter = new \Site\Counter($completeCounterListItem->key);
    	            print $siteCounter->get();
                ?>
            </div>
            <div class="tableCell">
                <input type="button" onclick="populateFormSubmit('remove','removeKeyExisting-<?=$count?>', 'removeNotesExisting-<?=$count?>')" value="Remove"/>
            </div>
        </div>
    <?php	
            $count++;
        } ?>
</div>
<br/><br/><br/>
<hr/>

<h4>Add New Watcher</h4>
<div class="tableBody min-tablet">
    <div class="tableRowHeader">
        <div class="tableCell" style="width: 12%;">Key</div>
        <div class="tableCell" style="width: 12%;">Notes</div>
        <div class="tableCell" style="width: 12%;">&nbsp;</div>        
    </div>
    <div class="tableRow">
        <div class="tableCell">
            <input id="addKey" type="text"/>
        </div>
        <div class="tableCell">
            <input id="addNote" type="text"/>
        </div>
        <div class="tableCell">
            <input onclick="populateFormSubmit('add','addKey', 'addNote')" type="button" value="Add"/>
        </div>
    </div>
</div>
<br/><br/><br/>
<hr/>
<h4>Current System Keys</h4>
<div style="max-height: 300px; overflow:scroll;">
    <div class="tableBody min-tablet">
        <div class="tableRowHeader">
            <div class="tableCell" style="width: 12%;">Existing Keys</div>
            <div class="tableCell" style="width: 12%;">Add Note</div>
            <div class="tableCell" style="width: 12%;">&nbsp;</div>
        </div>
        <?php	
        $count = 0;
        foreach ($completeCounterList as $counterItem) { ?>
            <div class="tableRow">
                <div class="tableCell">
                    <input id="addKeyExisting-<?=$count?>" type="text" value="<?=$counterItem?>"/>
                </div>
                <div class="tableCell">
	                <input id="addNotesExisting-<?=$count?>" type="text" value=""/>
                </div>
                <div class="tableCell">
	                <input type="button" onclick="populateFormSubmit('add','addKeyExisting-<?=$count?>', 'addNotesExisting-<?=$count?>')" value="Watch"/>
                </div>
            </div>
        <?php	
            $count++;
        } ?>
    </div>
</div>
<script>
    function populateFormSubmit (action, keyFieldID, noteFieldID) {
        keyFieldID = document.getElementById(keyFieldID);
        noteFieldID = document.getElementById(noteFieldID);    
        document.getElementById("action").value = action;
        document.getElementById("key").value = keyFieldID.value;
        document.getElementById("notes").value = noteFieldID.value;
        document.getElementById("counter_page_form").submit();
    }
</script>
<form id="counter_page_form" name="counter_page_form" method="POST" action="/_site/counters">
    <input id="action" name="action" type="hidden" value="add"/>
    <input id="key" name="key" type="hidden" value=""/>
    <input id="notes" name="notes" type="hidden" value=""/>
</form>
