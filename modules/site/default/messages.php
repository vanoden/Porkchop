<style>
    a { text-decoration: none; cursor: pointer; color: blue; }
    a:visited { color: blue; }
    a:not([href]):not([tabindex]) { color:blue; }
    
    .info-row.row { padding: 10px; }
    .info-row.row.selected { background-color: #edfaff; }  
    .info-row.row:hover{ background-color: #edfaff; cursor:pointer;  }
    
    .messaging-page-wrapper {  margin: 50px;     }
    .row { display: flex; flex-direction: row; flex-wrap: wrap; width: 100%; margin: 0 0 2rem; border-bottom: 1px solid #dddddd; }
    .full-column-row { margin: 1px; border-bottom: 0px; }
    .column {display: flex; flex-direction: column; flex-basis: 100%; flex: 1; margin: 10px; }
    
    .message-icon { padding: 10px;}
    .list-column { display:inline-flex; }
    .full-column { flex: 100%;}
    .message-title { white-space:nowrap;}
    .message-sub-title { color: blue; white-space:nowrap; }
    .message-date { padding-bottom: 5px; }
	.message-sender { padding-bottom: 5px; }
    .message-subject { font-size: 20px; }
    .message-links-wrapper { margin-top: 10px; }
    .read-more-link { margin-top: 10px; }
    .right-column { flex: 3; position: relative; }
    .left-column { flex: 1; padding: 10px; position: relative; border-left: 4px solid #dddddd; border-right: 1px solid #dddddd; }
    .message-title-chevron { min-width: 20px; text-align: end;font-size: 30px; color: #999;}
    .year-column { font-size: 24px; }
    .message-content { overflow: hidden; text-overflow: ellipsis;  height: 25px; }
    .bold {
        font-weight: bold;
    }
    
    @media only screen and (max-width: 900px) {
      .row {flex-direction: column; }
      .year-column { background-color: black; text-align: center; color:white; flex:unset; }
      .year-column.list-column { display:unset; padding: 10px; }
    }
</style>

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

<div class="row full-column-row">
    <form method="post" id="filterForm">
    <div style="flex: 1;">
        <span class="value">Acknowledged</span>&nbsp;<input type="checkbox" name="seeAcknowledged"<?php if ($params['acknowledged']) print " checked";?> />
        <input type="submit" name="btn_filter" value="Filter" />
    </div>
    </form>
</div>

<div class="messaging-page-wrapper">
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
      <div class="row full-column-row">
        <div class="column full-column">
          <div class="list-column year-column">
            <?=$currentYearCheck?>
          </div>
        </div>
      </div>
    <?php
        }
    ?>
      <div id="row-<?=$siteMessage->id?>" class="row info-row">
        <div class="column left-column">
          <div class="list-column">
            <div style="flex: 2;">
                <div id="message-date-<?=$siteMessage->id?>" class="message-date <?=($siteMessageDelivery->acknowledged()) ? '' : 'bold'?>"><?=date('m/d/Y', strtotime($siteMessage->date_created));?></div>
            </div>
            <div style="flex: 1;"></div>   
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
            <div style="flex: 2;">
                <div id="message-title-<?=$siteMessage->id?>" class="message-title <?=($siteMessageDelivery->acknowledged()) ? '' : 'bold'?>"></div>
            </div>
            <div style="flex: 1;"></div>
          </div>
        </div>
        <div class="column right-column">
          <div class="messages-column">
            <div id="message-subject-<?=$siteMessage->id?>" class="message-subject <?=($siteMessageDelivery->acknowledged()) ? '' : 'bold'?>"></div>
            <div class="message-subject">
                <div id="message-subject-<?=$siteMessage->id?>" class="message-content"><?=$siteMessage->subject?></div>
            </div>
            <div class="message-text">
                <div id="message-text-<?=$siteMessage->id?>" class="message-content"><?=$siteMessage->content?></div>
            </div>
          </div>
        </div>
      </div>
      <?php
        }
    ?>
</div>
