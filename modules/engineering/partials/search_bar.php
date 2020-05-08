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
    
    #menu > li:nth-child(12):hover {
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
        var sideNav = $('#menu a[href="/_engineering/home"]');
        sideNav.html('<i class="fa fa-cogs"></i> Engineering');
        sideNav.addClass('current');
        sideNav.after( "<div id='_engineering-sub-nav-container'><li><a id='engineering-sub-nav-tasks' class='menu-sub-nav' href='/_engineering/tasks'><i class='fa fa-tasks' aria-hidden='true'></i> Tasks</a></li><li><a id='engineering-sub-nav-reports' class='menu-sub-nav' href='/_engineering/event_report'><i class='fa fa-line-chart' aria-hidden='true'></i> Reports</a></li><li><a id='engineering-sub-nav-releases' class='menu-sub-nav' href='/_engineering/releases'><i class='fa fa-upload' aria-hidden='true'></i> Releases</a></li><li><a id='engineering-sub-nav-projects' class='menu-sub-nav' href='/_engineering/projects'><i class='fa fa-pie-chart' aria-hidden='true'></i> Projects</a></li><li><a id='engineering-sub-nav-products' class='menu-sub-nav' href='/_engineering/products'><i class='fa fa-truck' aria-hidden='true'></i> Products</a></li></div>" );

        // add sub-current class to current sub menu page selected
        var sideNavSubMenu = $('#menu a[href="/_engineering/' + window.location.href.split('/').pop() + '"]');
        sideNavSubMenu.addClass('sub-current'); 
    });
</script>
<div class="topnav">
  <a class="active" href="/_engineering/home"><i class="fa fa-cogs"></i> Engineering</a>
  <div class="search-container">
    <form action="/_engineering/search" method="get">
      <input type="text" value="<?=isset($_REQUEST['search']) ? preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['search']) : ''?>" placeholder="Search.." name="search" style="background-color:white;"/>
      <button type="submit"><i class="fa fa-search"></i></button>
    </form>
  </div>
</div>
<br/>
