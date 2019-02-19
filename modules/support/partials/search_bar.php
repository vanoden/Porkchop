<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<style>
    * {box-sizing: border-box;}

    body {
      margin: 0;
      font-family: Arial, Helvetica, sans-serif;
    }
    
    pre {
        font-size: 10px;
    }
    
    a.button.blue-background {
        background-color:blue;
    }
    
    a.button.blue-background:hover {
        background-color:#0085ad;
    }
    
    a.button.red-background {
        background-color:red;
    }
    
    a.button.red-background:hover {
        background-color:red;
    }
    
    a.button.green-background {
        background-color:green;
    }
    
    a.button.green-background:hover {
        background-color:#22b95e;
    }
    
    a.black {
        color: black;
        text-decoration: none;
    }
    
    a.black:hover {
        color: black;
    }

    .topnav {
      overflow: hidden;
      background-color: #e9e9e9;
    }

    .topnav a {
      float: left;
      display: block;
      color: black;
      text-align: center;
      padding: 14px 16px;
      text-decoration: none;
      font-size: 17px;
    }

    .topnav a:hover {
      background-color: #ddd;
      color: black;
    }

    .topnav a.active {
      background-color: #0f3345;
      color: white;
    }

    .topnav .search-container {
      float: right;
    }

    .topnav input[type=text] {
      padding: 6px;
      margin-top: 8px;
      font-size: 17px;
      border: none;
    }

    .topnav .search-container button {
      float: right;
      padding: 6px 10px;
      margin-top: 8px;
      margin-right: 16px;
      background: #ddd;
      font-size: 17px;
      border: none;
      cursor: pointer;
    }

    .topnav .search-container button:hover {
      background: #ccc;
    }

    @media screen and (max-width: 600px) {
      .topnav .search-container {
        float: none;
      }
      .topnav a, .topnav input[type=text], .topnav .search-container button {
        float: none;
        display: block;
        text-align: left;
        width: 100%;
        margin: 0;
        padding: 14px;
      }
      .topnav input[type=text] {
        border: 1px solid #ccc;  
      }
    }
    
    .menu-sub-nav {
        padding-left: 35px;
    }
    
    nav ul li a.sub-current {
        background: rgba(33, 255, 142, 0.25);
    }
    
    #menu > li:nth-child(13):hover {
        background: rgba(255,255,255,0);
    }
    
    nav ul li a.menu-sub-nav:hover {
        background: rgba(255,255,255,0.25);
    }
    
    #menu a[href="/_engineering/home"]:hover {
        background: rgba(255,255,255,0.25);
    }
    
</style>
<script>
    // TODO let's consolidate all this together, this is a local hack for the requirment now
    $(document).ready(function (){

        // add current class to the engineering section and add the sub-menu and add the "cog" icon
        var sideNav = $('#menu a[href="/_support/requests"]');
        sideNav.html('<i class="fa fa-phone"></i> Support');
        sideNav.addClass('current');
        sideNav.after( "<div id='_engineering-sub-nav-container'><li><a id='engineering-sub-nav-tasks' class='menu-sub-nav' href='/_support/request_new'><i class='fa fa-plus' aria-hidden='true'></i> New Request</a></li><li><a id='engineering-sub-nav-reports' class='menu-sub-nav' href='/_support/requests?btn_all=true'><i class='fa fa-list-ol' aria-hidden='true'></i> All Requests</a></li><li><a id='engineering-sub-nav-releases' class='menu-sub-nav' href='/_support/request_items'><i class='fa fa-check-square' aria-hidden='true'></i> Tickets</a></li><li><a id='engineering-sub-nav-projects' class='menu-sub-nav' href='/_support/admin_actions'><i class='fa fa-check-square-o' aria-hidden='true'></i> Actions</a></li><li></div>" );

        // add sub-current class to current sub menu page selected
        var sideNavSubMenu = $('#menu a[href="/_support/' + window.location.href.split('/').pop() + '"]');
        sideNavSubMenu.addClass('sub-current'); 
    });
</script>
<?	if ($_REQUEST['btn_all']) { ?>
<a class="button more" href="/_support/requests">Open Requests</a>
<?	} else { ?>
<a class="button more" href="/_support/requests?btn_all=true">All Requests</a>
<?	} ?>
&nbsp;| 
<a class="button more green-background" href="/_support/request_items">Requests &gt; Tickets</a>
&nbsp;| 
<a class="button more blue-background" href="/_support/admin_actions">Tickets &gt; Actions</a>
<a class="button more red-background" style="float: right; margin-top: 12px; font-size: 16px;" href="/_support/request_new">New Request</a>  
<div class="topnav">
  <a class="active" href="/_support/requests"><i class="fa fa-phone" aria-hidden="true"></i> Support</a>
  <div class="search-container">
    <form action="/_support/search" method="get">
      <input type="text" value="<?=preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['search'])?>" placeholder="Search.." name="search" style="background-color:white;"/>
      <button type="submit"><i class="fa fa-search"></i></button>
    </form>
  </div>
</div>
<br/>
