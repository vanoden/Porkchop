<style>
    html {
        font-family: sans;
    }

    a {
        text-decoration: none;
    }
    
    a:visited {
        color: blue;
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
        padding-bottom: 15px;
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

  <div class="row full-column-row">
    <div class="column full-column">
      <div class="list-column year-column">
        2022
      </div>
    </div>
  </div>

  <div class="row">
    <div class="column left-column">
      <div class="list-column">
        <div style="flex: 1;"></div>
        <div style="flex: 2;">
            <div class="message-date"><a href="#">02/04/2022</a></div>
        </div>
        <div style="flex: 1;"></div>      
      </div>
      <div class="list-column">
        <div style="flex: 1;">
            <span class="message-icon">[ICON]</span>
        </div>
        <div style="flex: 2;">
            <div class="message-title">System Outage</div>
            <div class="message-sub-title">Power Issues</div>
            <span>[A]</span><span>[B]</span><span>[C]</span>
        </div>
        <div style="flex: 1;"></div>
        <div class="message-title-chevron">&#8250;</div>
      </div>
    </div>
    <div class="column right-column">
      <div class="messages-column">
        <div class="message-subject">There has been a power outage that...</div>
        <div class="message-text">
            Your device has lost power for a period greater than your set threshold. 
            Check your secure portal for more information on how this might affect your...
        </div>
        <div class="message-links-wrapper">
            <span class="read-more-link"><a href="#">Read more &#8964;</a></span>
            <span class="visit-portal-link" style="float:right"><a href="#">Visit your portal &#8250;</a></span>
        </div>
      </div>
    </div>
  </div>
  
  <div class="row">
    <div class="column left-column">
      <div class="list-column">
        <div style="flex: 1;"></div>
        <div style="flex: 2;">
            <div class="message-date"><a href="#">02/04/2022</a></div>
        </div>
        <div style="flex: 1;"></div>      
      </div>
      <div class="list-column">
        <div style="flex: 1;">
            <span class="message-icon">[ICON]</span>
        </div>
        <div style="flex: 2;">
            <div class="message-title">System Outage</div>
            <div class="message-sub-title">Power Issues</div>
            <span>[A]</span><span>[B]</span><span>[C]</span>
        </div>
        <div style="flex: 1;"></div>
        <div class="message-title-chevron">&#8250;</div>
      </div>
    </div>
    <div class="column right-column">
      <div class="messages-column">
        <div class="message-subject">There has been a power outage that...</div>
        <div class="message-text">
            Your device has lost power for a period greater than your set threshold. 
            Check your secure portal for more information on how this might affect your...
        </div>
        <div class="message-links-wrapper">
            <span class="read-more-link"><a href="#">Read more &#8964;</a></span>
            <span class="visit-portal-link" style="float:right"><a href="#">Visit your portal &#8250;</a></span>
        </div>
      </div>
    </div>
  </div>
  
</div>

