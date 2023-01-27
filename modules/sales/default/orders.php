<div class="breadcrumbs">
    <a href="/_sales/orders">Sales</a> &gt; Orders
</div>
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
<div class="topnav">
  <a class="active" href="/_sales/orders"><i class="fa fa-money"></i> Sales</a>  
  <div class="search-container">
    <form action="/_sales/orders" method="get">
      <input type="text" value="<?=isset($_REQUEST['search']) ? preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['search']) : ''?>" placeholder="Search.." name="search" style="background-color:white;"/>
      <button type="submit"><i class="fa fa-search"></i></button>
    </form>
  </div>
</div>
<br/>
<h2 style="display: inline-block;">Orders
    <?=isset($page->isSearchResults) ? "[Matched Orders: ". count($orders)."]" : "";?>
</h2>
<?php
 if (!isset($page->isSearchResults)) {
?>
    <a class="button more" href="/_sales/cart">New Order</a>
<?php
}
?>
 
 <form action="/_sales/orders" method="post">
    <div style="padding-top:10px;">
        <input type="checkbox" name="new" value="1"<?php if ($_REQUEST['new']) print " checked"; ?> />New
        <input type="checkbox" name="quote" value="1"<?php if ($_REQUEST['quote']) print " checked"; ?> />Quote
        <input type="checkbox" name="cancelled" value="1"<?php if ($_REQUEST['cancelled']) print " checked"; ?> />Cancelled
        <input type="checkbox" name="approved" value="1"<?php if ($_REQUEST['approved']) print " checked"; ?> />Approved
        <input type="checkbox" name="accepted" value="1"<?php if ($_REQUEST['accepted']) print " checked"; ?> />Accepted
        <input type="checkbox" name="complete" value="1"<?php if ($_REQUEST['complete']) print " checked"; ?>/>Complete
	</div>
    <div class="form_footer" style="text-align: left; width: 100%">
	    <input type="submit" name="btn_submit" class="button" value="Apply Filter" />
    </div>

<!--	START First Table -->
	<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 25%;">Code</div>
		<div class="tableCell" style="width: 25%;">Customer</div>
		<div class="tableCell" style="width: 25%;">Sales Agent</div>
		<div class="tableCell" style="width: 25%;">Status</div>
	</div>
<?php
	foreach ($orders as $order) {
?>
	<div class="tableRow">
		<div class="tableCell">
			<a href="/_sales/cart/<?=$order->code?>"><?=$order->code?></a>
		</div>
		<div class="tableCell">
            <?php
                
                $registerCustomer = new \Register\Customer($order->customer_id);
                $registerOrganization = new \Register\Organization($registerCustomer->organization_id);
            ?>        
            <strong><?=$registerOrganization->name?> </strong><br/>&nbsp;&nbsp;&nbsp;&nbsp;<?=$registerCustomer->first_name?> <?=$registerCustomer->last_name?>
		</div>
		<div class="tableCell">
            <?php
                if (!empty($order->salesperson_id)) {
                    $salesAgent = new \Register\Customer($order->salesperson_id);
                ?>
                    <?=$salesAgent->first_name?> <?=$salesAgent->last_name?>
                <?php
                }
            ?> 
		</div>
		<div class="tableCell">
            <?=$order->status?>
		</div>
	</div>
<?php	} ?>
</div>
<!--	END First Table -->
</form>
