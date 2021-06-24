<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
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
        var sideNav = $('#support-admin-menu');

        // add sub-current class to current sub menu page selected
        var sideNavSubMenu = $('#menu a[href="/_register/' + window.location.href.split('/').pop() + '"]');
        sideNavSubMenu.addClass('sub-current'); 
    });
</script>
<div class="topnav">
  <a class="active" href="/_register/pending_customers"><i class="fa fa-user" aria-hidden="true"></i> Regististrations</a>
  <div class="search-container">
    <form action="/_register/pending_customers" method="get">
      <input type="text" value="<?=isset($_REQUEST['search']) ? preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['search']) : ''?>" placeholder="Search.." name="search" style="background-color:white;"/>
      <button type="submit"><i class="fa fa-search"></i></button>
    </form>
  </div>
</div>
<br/>
