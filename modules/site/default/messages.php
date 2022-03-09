<style>
    html {
        font-family: sans;
    }

    a {
        text-decoration: none;
        cursor: pointer;
        color: blue;
    }
    
    a:visited {
        color: blue;
    }
    
    a:not([href]):not([tabindex]) {
        color:blue;
    }
    
    .messaging-page-wrapper {
        margin: 50px;    
    }
    .row {
      display: flex;
      flex-direction: row;
      flex-wrap: wrap;
      width: 100%;
      margin: 0 0 2rem;
      border-bottom: 1px solid #dddddd;
    }

    .full-column-row {
        margin: 1px;
        border-bottom: 0px;
    }

    .column {
      display: flex;
      flex-direction: column;
      flex-basis: 100%;
      flex: 1;
      margin: 10px;
    }
    
    .message-icon {
        padding: 10px;
    }
    
    .list-column {
        display:inline-flex    
    }
    
    .full-column {
        flex: 100%;
    }
    
    .message-title {
        font-weight: bold;
        white-space:nowrap;
    }
    
    .message-sub-title {
        color: blue;
        white-space:nowrap;
    }

    .message-date {
        padding-bottom: 5px;
    }

	.message-sender {
		padding-bottom: 5px;
	}
    
    .message-subject {
        font-size: 20px;
    }
    
    .message-links-wrapper {
        margin-top: 10px;
    }
    
    .read-more-link { 
        margin-top: 10px;
    }
    
    .visit-portal-link {

    }
    
    .right-column {
        flex: 3;
        position: relative;
    }
    
    .left-column {
        flex: 1;
        padding: 10px;
        position: relative;    
        border-left: 4px solid #dddddd;
        border-right: 1px solid #dddddd;
    }
    
    .message-title-chevron {
        min-width: 20px;
        text-align: end;
        font-size: 30px;
        color: #999;
    }
    
    .year-column {
        font-size: 24px;
    }
    
    @media only screen and (max-width: 900px) {
      .row {
        flex-direction: column;
      }
      .year-column {
        background-color: black;
        text-align: center;
        color:white;
        flex:unset;
      }
      .year-column.list-column {
        display:unset;
        padding: 10px;
      }
    }
</style>
<body>
<div class="messaging-page-wrapper">
    <?php
    $currentYear = '';
    foreach ($userMessages as $userMessage) {
        $siteMessageMetaDataList = new \Site\SiteMessageMetaDataList();
        $siteMessageMetaDataTitle = array_pop($siteMessageMetaDataList->find(array('item_id'=>$userMessage->id, 'label' => 'title')));
        $siteMessageMetaDataCategory = array_pop($siteMessageMetaDataList->find(array('item_id'=>$userMessage->id, 'label' => 'category')));
        $siteMessageMetaDataSummary = array_pop($siteMessageMetaDataList->find(array('item_id'=>$userMessage->id, 'label' => 'summary')));
        $currentYearCheck = date('Y', strtotime($userMessage->date_created));
        if ($currentYear != $currentYearCheck) {
        $currentYear = $currentYearCheck;
		$sender = new \Register\Customer($userMessage->user_created);
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
      <div class="row">
        <div class="column left-column">
          <div class="list-column">
            <div style="flex: 1;">
                <span class="message-icon">
                    <img src="/img/messages/icon_tools_star_1C.svg" style="width: 20px;">
                </span>
            </div>
            <div style="flex: 2;">
                <div class="message-date"><?=date('m/d/Y', strtotime($userMessage->date_created));?></div>
            </div>
            <div style="flex: 1;"></div>      
          </div>
          <div class="list-column">
            <div style="flex: 1;">
                <div style="margin:10px;"></div>
                <span class="message-sender"><?=$sender->full_name()?></span>
                <span class="message-icon">
                    <img src="/img/messages/icon_tools_check_2C.svg" style="width: 20px;">
                </span>
            </div>
            <div style="flex: 2;">
                <div class="message-title"><?=$siteMessageMetaDataSummary->value?></div>
            </div>
            <div style="flex: 1;"></div>
            <div class="message-title-chevron">&#8250;</div>
          </div>
        </div>
        <div class="column right-column">
          <div class="messages-column">
            <div class="message-subject"><?=$siteMessageMetaDataTitle->value?></div>
            <div class="message-text">
                <?=$userMessage->content?>
            </div>
          </div>
        </div>
      </div>
      <?php
        }
    ?>
</div>
