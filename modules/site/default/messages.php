<script>

    // expand text to read the whole message
    function expandText(messageId) {
        var messageText = document.getElementById('message-text-' + messageId);
        var expandBtn = document.getElementById('message-text-read-more-' + messageId);
        
        messageText.style.maxHeight = "none";
        messageText.style.overflow = "visible";
        expandBtn.style.display = 'none';
    }
    
    // mark the message individually as acknowledged
    function acknowledge(messageId) {
        var params = 'method=acknowledgeSiteMessage&csrfToken=<?=$GLOBALS['_SESSION_']->getCSRFToken()?>&message_id=' + messageId;
        
        AJAXUtils.post('/_site/api', params, function(data) {
            var messageCard = document.getElementById('row-' + messageId);
            messageCard.classList.remove('message-unread');
            messageCard.classList.add('message-read');
            
            // Update the button to show read status
            var messageActions = messageCard.querySelector('.message-actions');
            messageActions.innerHTML = '<span class="status-badge status-read">Read</span>';
        }, function(status) {
            console.error('Error acknowledging message:', status);
        });
    }
    
    // toggle all messages for acknowledged option
    var isSelectAll = false;
    function selectAll() {
        var messages = document.getElementsByClassName("message-card");
        var selectBtn = document.getElementById('selectAll');
        var markBtn = document.getElementById('mark-acknowledged');
        
        for (var i = 0; i < messages.length; i++) {
            if (!isSelectAll) {
                messages.item(i).classList.add('selected');
            } else {
                messages.item(i).classList.remove('selected');
            }
        }
        isSelectAll = !isSelectAll;
        markBtn.disabled = !isSelectAll;
        selectBtn.querySelector('.btn-text').textContent = isSelectAll ? 'Deselect All' : 'Select All';
    }
    
    // acknowledge all messages at once on button click
    function acknowledgeAll() {
        var params = 'method=acknowledgeSiteMessageByUserId&user_created=<?=$GLOBALS['_SESSION_']->customer->id?>&csrfToken=<?=$GLOBALS['_SESSION_']->getCSRFToken()?>&btn_submit=Submit';
        
        AJAXUtils.post('/_site/api', params, function(data) {
            var messages = document.getElementsByClassName("message-card");
            for (var i = 0; i < messages.length; i++) {
                var messageId = messages[i].id.replace('row-', '');
                acknowledge(messageId);
            }
            selectAll();
        }, function(status) {
            console.error('Error acknowledging all messages:', status);
        });
    }
</script>

<?=$page->showBreadCrumbs()?>
<?=$page->showTitle()?>
<?=$page->showMessages()?>

<div class="messages-header">
    <div class="messages-controls">
        <div class="messages-actions">
            <button type="button" class="btn btn-secondary" onclick="selectAll()" id="selectAll">
                <span class="btn-text">Select All</span>
            </button>
            <button type="button" class="btn btn-primary" onclick="acknowledgeAll()" id="mark-acknowledged" disabled>
                <span class="btn-text">Mark as Read</span>
            </button>
        </div>
        <form method="post" id="filterForm" class="filter-form">
            <div class="filter-group">
                <label class="filter-label">
                    <input type="checkbox" name="seeAcknowledged"<?php if ($params['acknowledged'] && $params['acknowledged'] == 'read') print " checked";?> onchange="document.getElementById('filterForm').submit()"/>
                    <span class="checkmark"></span>
                    Show acknowledged messages
                </label>
                <input type="hidden" name="filter" value="Filter" />
            </div>
        </form>
    </div>
</div>

<div class="messages-container">
    <?php
    // Check if there are any messages
    if (empty($siteMessages)) {
        $showUnreadOnly = !isset($_REQUEST['seeAcknowledged']) || !$_REQUEST['seeAcknowledged'];
        if ($showUnreadOnly) {
            // No unread messages
            echo '<div class="no-messages">';
            echo '<h3>No unread messages</h3>';
            echo '<p>You\'re all caught up! There are no unread messages at this time.</p>';
            echo '</div>';
        } else {
            // No acknowledged messages
            echo '<div class="no-messages">';
            echo '<h3>No acknowledged messages</h3>';
            echo '<p>There are no acknowledged messages to display.</p>';
            echo '</div>';
        }
    } else {
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
      <div class="year-divider">
        <div class="year-text"><?=$currentYearCheck?></div>
      </div>
    <?php
        }
    ?>
      <div id="row-<?=$siteMessage->id?>" class="message-card <?=($siteMessageDelivery->acknowledged()) ? 'message-read' : 'message-unread'?>">
        <div class="message-header">
          <div class="message-meta">
            <div class="message-date" id="message-date-<?=$siteMessage->id?>">
              <?=date('M j, Y', strtotime($siteMessage->date_created));?>
            </div>
            <div class="message-sender">
              <span class="sender-name"><?=$sender->full_name()?></span>
            </div>
          </div>
          <div class="message-actions">
            <?php if (! $siteMessageDelivery->acknowledged()) { ?>
              <button type="button" class="btn btn-sm btn-outline" onclick="acknowledge('<?=$siteMessageDelivery->message_id?>')">
                Mark as Read
              </button>
            <?php } else { ?>
              <span class="status-badge status-read">Read</span>
            <?php } ?>
          </div>
        </div>
        <div class="message-content">
          <h3 class="message-subject" id="message-subject-<?=$siteMessage->id?>">
            <?=$siteMessage->subject?>
          </h3>
          <div class="message-text" id="message-text-<?=$siteMessage->id?>">
            <?= strip_tags($siteMessage->content) ?>
            <?php if (strlen(strip_tags($siteMessage->content)) > 200) { ?>
              <button class="expand-btn" onclick="expandText(<?=$siteMessage->id?>)" id="message-text-read-more-<?=$siteMessage->id?>">
                Read more...
              </button>
            <?php } ?>
          </div>
        </div>
      </div>
      <?php
        }
    }
    ?>
</div>
