<link rel="stylesheet" type="text/css" href="/html.src/css/support.css">

<script>

   // date picker with max date being current day
   window.onload = function() {
	  var dateStart = document.getElementById('dateStart');
	  var dateEnd = document.getElementById('dateEnd');
	  
	  if (dateStart) {
		  dateStart.type = 'date';
		  dateStart.max = new Date().toISOString().split('T')[0];
	  }
	  
	  if (dateEnd) {
		  dateEnd.type = 'date';
		  dateEnd.max = new Date().toISOString().split('T')[0];
	  }
	  
      window.serialNumber = document.getElementById("serialNumber");
      window.sortByMessage = document.getElementById("sort-by");
      window.statusMessage = document.getElementById("status");
      window.dateStart = document.getElementById("dateStart");
      window.dateEnd = document.getElementById("dateEnd");
      window.statusButton = document.getElementById("status-button");
      window.sortButton = document.getElementById("sort-button");
      window.statusFilter = '';
      window.sortByFilter = '';
      <?php
        if (isset($_REQUEST['sortBy'])) {
      ?>
          window.sortByMessage.innerHTML = 'Sort by: <?=$_REQUEST['sortBy']?>';
          window.sortByFilter = '<?=$_REQUEST['sortBy']?>';
      <?php
      }
        if (isset($_REQUEST['status'])) {
      ?>
          window.statusMessage.innerHTML = 'Status: <?=$_REQUEST['status']?>';
          window.statusFilter = '<?=$_REQUEST['status']?>';
      <?php
      }
      ?>
   }
   
    function filterReport(filterType, value) {
        if (value == 'sort') {
            window.sortByMessage.innerHTML = 'Sort by: ' + filterType;
            window.sortByFilter = filterType;
        }
        if (value == 'status') {
            window.statusMessage.innerHTML = 'Status: ' + filterType;
            window.statusFilter = filterType;
        }
    }

    function newSearch() {
        window.sortByFilter = '';
        window.serialFilter = '';
        window.fromDateFilter = '';
        window.toDateFilter = '';
        window.statusFilter = '';
        window.sortByMessage.innerHTML = 'Sort by';
        window.statusMessage.innerHTML = 'Choose Status';
        window.serialNumber.value = '';
        window.dateStart.value = '';
        window.dateEnd.value = '';
    }

    function search() {
        window.fromDateFilter = window.dateStart.value;
        window.toDateFilter = window.dateEnd.value;
        window.serialFilter = window.serialNumber.value;
        var url = '/_register/support?sortBy=' + window.sortByFilter + '&serial=' + window.serialFilter + '&fromDate=' + window.fromDateFilter + '&toDate=' + window.toDateFilter + '&status=' + window.statusFilter;
        console.log('sortByFilter: ' + window.sortByFilter);
        console.log('serialFilter: ' + window.serialFilter);
        console.log('fromDateFilter: ' + window.fromDateFilter);
        console.log('toDateFilter: ' + window.toDateFilter);
        console.log('statusFilter: ' + window.statusFilter);
        console.log(url);
        window.location = url;
    }
</script>
<main role="main" class="my-support-container flex-shrink-0">
        <h1>My Support</h1>
        <h2 class="requests-title-collapsed">Requests</h2>        
    <div class="container">    
        <div class="row">
            <div class="col-sm-3 requests-title">
              <h2 class="requests-title-grid">Requests</h2>
            </div>
            <div class="col-sm-3 col-6 padding-top sort-result">
                <div class="btn-group">
                  <button id="sort-button" type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span id="sort-by">Sort by</span>
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" onclick="filterReport('date', 'sort')"><span class="pointer">Date</a>
                    <a class="dropdown-item" onclick="filterReport('serial', 'sort')"><span class="pointer">Serial #</a>
                    <a class="dropdown-item" onclick="filterReport('ticket', 'sort')"><span class="pointer">Ticket #</a>
                    <a class="dropdown-item" onclick="filterReport('status', 'sort')"><span class="pointer">Status</a>
                    <a class="dropdown-item" onclick="filterReport('requestor', 'sort')"><span class="pointer">Requestor</a>
                  </div>
                </div>
            </div>
            <div id="filter-result" class="col-sm-3 col-6 padding-top">
              Filter Results <img src="/img/icons/icon_catgy_power_1C.svg" alt="Filter" class="filter-icon support-icon-16x16">
            </div>
            <div class="col-sm-3 new-request-button-container">
                <button id="new-request-top-button" type="button" class="btn btn-danger" onclick="window.location='/_support/request'">New Request</button>
            </div>
            <div class="col-sm-4"></div>
          </div>
        </div>
        <div id="filter-container" class="row">
            <div class="col-sm">
                Serial #:
                <input type="text" id="serialNumber" name="serialNumber" class="value input" value="<?=isset($_REQUEST['serial']) ? $_REQUEST['serial'] : ''?>" />
            </div>
            <div class="col-sm">
                From date: <input type="text" id="dateStart" name="dateStart" class="value input" value="<?=isset($_REQUEST['fromDate']) ? $_REQUEST['fromDate'] : ''?>" />
            </div>
            <div class="col-sm">
                 To date: <input type="text" id="dateEnd" name="dateEnd" class="value input" value="<?=isset($_REQUEST['toDate']) ? $_REQUEST['toDate'] : ''?>" />
            </div>
            <div class="col-sm">
                Status:
                <br/>
                <div class="btn-group">
                  <button id="status-button" type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span id="status">Choose Status</span>
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" onclick="filterReport('NEW', 'status')"><span class="pointer">New</span></a>
                    <a class="dropdown-item" onclick="filterReport('ACTIVE', 'status')"><span class="pointer">Active</span></a>
                    <a class="dropdown-item" onclick="filterReport('PENDING', 'status')"><span class="pointer">Pending</span></a>
                    <a class="dropdown-item" onclick="filterReport('COMPLETE', 'status')"><span class="pointer">Complete</span></a>
                    <a class="dropdown-item" onclick="filterReport('CLOSED', 'status')"><span class="pointer">Closed</span></a>                  
                  </div>
                </div>
            </div>
            <div class="col-sm">
                <span onclick="newSearch()" class="register-support-clear-filters">Clear Filters</span> <img src="/img/_global/icon_error.svg" alt="Clear" class="register-support-clear-icon support-icon-16x16 support-icon-clickable" onclick="newSearch()">
                <div class="register-support-search-container">
                    <button type="button" class="btn btn-info" onclick="search()">Search</button>
                </div>
            </div>
        </div>
         <?php
            if (isset($supportItems) && is_array($supportItems)) {
                foreach($supportItems as $supportItem) {
             ?>
            <div class="row data-container"> 
                <div class="col-sm support-ticket">
                    <img src="/img/icons/flag_on.svg" alt="Ticket" class="product-tag support-icon-16x16"> Ticket #:<?=str_pad($supportItem->id, 5, "0", STR_PAD_LEFT);?>
                    <p><?=ucfirst(strtolower($supportItem->request->type))?></p>
                </div>
                <div class="col-sm">
                    <span class="key-text">Action:</span>
                    <span class="key-value-text">Service</span>
                    <div class="value-divider"></div>
                    <?php
                        if (!empty($supportItem->product->code)) {
                    ?>
                        <span class="key-text">Product:</span>
                        <span class="key-value-text"><?=$supportItem->product->code?></span>   
                    <?php
                    }
                    ?>
                </div>
                <div class="col-sm">
                    <span class="key-text">Requestor:</span>
                    <span class="key-value-text"><?=$GLOBALS ['_SESSION_']->customer->first_name?> <?=$GLOBALS ['_SESSION_']->customer->last_name?></span>
                    <div class="value-divider"></div>
                    <?php
                        if (!empty($supportItem->serial_number)) {
                    ?>
                        <span class="key-text">Serial #:</span>
                        <span class="key-value-text"><?=$supportItem->serial_number?></span>   
                    <?php
                    }
                    ?>
                </div>
                <div class="col-sm">
                    <span class="key-text">Opened:</span>
                    <span class="key-value-text"><?=date("F j, Y, g:i a", strtotime($supportItem->request->date_request));?></span>
                    <div class="value-divider"></div>
                    <span class="key-text">Status:</span> 
                    <span class="key-value-text"><img src="/img/_global/circ-letter.svg" alt="Status" class="status-icon 
                    <?php
                        if ($supportItem->status == 'NEW') {
                            print "new-action";                
                        } else if ($supportItem->status == 'ACTIVE') {
                            print "complete-action";
                        } else {
                            print "active-action";
                        }
                    
                    ?>" class="support-icon-12x12"> 
                        <?=$supportItem->status?>
                    </span>                
                </div>
            </div>
            <?php
            }
        } else {
        ?>
            <p>No Tickets Found.</p>
        <?php
        }
        ?>
    </div>
</main>
<footer id="new-request-footer" class="footer">
  <div class="footer-container">
  <div class="col-sm-12 col-12">
   <div class="new-request-footer">
    <div class="vertical-center">
        <button type="button" class="btn btn-danger" onclick="window.location='/_support/request'">New Request</button>
    </div>
   </div>
  </div>
  </div>
</footer>
