

<script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>

<script>

    // expand text to read the whole message
    function expandText(messageId) {
        document.getElementById('message-text-' + messageId).style.height = "150px";
        document.getElementById('message-text-read-more-' + messageId).style.display = 'none';
    }
    
    // mark the message individually as acknowledged
    function acknowledge(messageId) {
        $.post( "/_site/api", {    
            'method': "acknowledgeSiteMessage", 
            'csrfToken': '<?=$GLOBALS['_SESSION_']->getCSRFToken()?>',
            'message_id': messageId
        }, function( data ) {
            document.getElementById('message-title-' + messageId).classList.remove('bold');
            document.getElementById('message-subject-' + messageId).classList.remove('bold');
            document.getElementById('message-date-' + messageId).classList.remove('bold');         
        });
        location.reload(true);
    }
    
    // toggle all messages for acknowledged option
    var isSelectAll = false;
    function selectAll() {
        var messages = document.getElementsByClassName("row info-row");
        for (var i = 0; i < messages.length; i++) {
            if (!isSelectAll) {
                messages.item(i).classList.add('selected');
            } else {
                messages.item(i).classList.remove('selected');
            }
        }
        isSelectAll = !isSelectAll;
        document.getElementById('mark-acknowledged').disabled = !isSelectAll;
    }
    
    // acknowledge all messages at once on button click
    function acknowledgeAll() {
        $.post( "/_site/api", { 
            method: "acknowledgeSiteMessageByUserId", 
            'user_created': '<?=$GLOBALS['_SESSION_']->customer->id?>', 
            'csrfToken': '<?=$GLOBALS['_SESSION_']->getCSRFToken()?>',
            'btn_submit' : 'Submit' 
        
        }, function( data ) {
            var messages = document.getElementsByClassName("row info-row");
            for (var i = 0; i < messages.length; i++) acknowledged(messages[i].id.replace('row-', ''));
            selectAll();
            document.getElementById('selectAll').checked = false;
        });
    }
</script>

<?=$page->showBreadCrumbs()?>
<?=$page->showTitle()?>
<?=$page->showMessages()?>

<div class="row row-full-column">
    <form method="post" id="filterForm">
    <div class="flex-2">
        <span class="value">Acknowledged</span>&nbsp;<input type="checkbox" name="seeAcknowledged"<?php if ($params['acknowledged'] && $params['acknowledged'] == 'read') print " checked";?> onchange="document.getElementById('filterForm').submit()"/>
        <input type="hidden" name="filter" value="Filter" />
    </div>
    </form>
</div>

<div class="margin-50">
    <?php
    $currentYear = '';
    foreach ($siteMessages as $siteMessage) {

        // insert that the message has been viewed
        $sender = new \Register\Customer($siteMessage->user_created);
        $messageDelivery = $siteMessageDelivery->getDelivery($siteMessage->id,$GLOBALS['_SESSION_']->customer->id);
        if (empty($messageDelivery)) $siteMessageDelivery->add(array('message_id' => $siteMessage->id,'user_id' => $GLOBALS['_SESSION_']->customer->id, 'date_viewed' => date('Y-m-d H:i:s')));
        $currentYearCheck = date('Y', strtotime($siteMessage->date_created));
        if ($currentYear != $currentYearCheck) {
        $currentYear = $currentYearCheck;	
    ?>
      <div class="row row-full-column">
        <div class="column column-flex full-column">
          <div class="list-column column-year">
            <?=$currentYearCheck?>
          </div>
        </div>
      </div>
    <?php
        }
    ?>
      <div id="row-<?=$siteMessage->id?>" class="row row-flex info-row">
        <div class="column column-left">
          <div class="list-column">
            <div class="flex-2">
                <div id="message-date-<?=$siteMessage->id?>" class="message-date <?=($siteMessageDelivery->acknowledged()) ? '' : 'bold'?>"><?=date('m/d/Y', strtotime($siteMessage->date_created));?></div>
            </div>
            <div class="flex-1"></div>   
          </div>
          <div class="list-column">
            <div>
                <span class="message-sender"><?=$sender->full_name()?></span>
                <div>
                    <?php if (! $siteMessageDelivery->acknowledged()) { ?>
				        <input type="button" class="button" name="btn_ack[<?=$siteMessageDelivery->id?>]" value="Acknowledge" onclick="acknowledge('<?=$siteMessageDelivery->message_id?>');" />
                    <?php } ?>
				</div>
            </div>
            <div class="flex-2">
                <div id="message-title-<?=$siteMessage->id?>" class="message-title <?=($siteMessageDelivery->acknowledged()) ? '' : 'bold'?>"></div>
            </div>
            <div class="flex-1"></div>
          </div>
        </div>
        <div class="column column-right">
          <div class="messages-column">
            <div id="message-subject-<?=$siteMessage->id?>" class="message-subject <?=($siteMessageDelivery->acknowledged()) ? '' : 'bold'?>"></div>
            <div class="message-subject">
                <div id="message-subject-<?=$siteMessage->id?>" class="message-content"><?=$siteMessage->subject?></div>
            </div>
            <div class="message-text">
                <div id="message-text-<?=$siteMessage->id?>" class="message-content"><?= strip_tags($siteMessage->content) ?></div>
            </div>
          </div>
        </div>
      </div>
      <?php
        }
    ?>
</div>
