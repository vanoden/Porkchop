<link rel="stylesheet" href="css/admin.css">
<?=$page->showMessages();?>
<style>
    fieldset {
        max-width: 500px;
    }
    #sales_cart_form {
        
    }
    form input[type=number] {
        max-width: 75px;
    }
    .tableCell {
       white-space: nowrap; 
    }
    .continue_button {
        width: 200px;
        height: 40px;   
    }
    .small-font {
        font-size:10px;
        display:inline;
    }
</style>
<script>
    function scrollToBottom (id) {
       var div = document.getElementById(id);
       div.scrollTop = div.scrollHeight - div.clientHeight;
    }
    document.addEventListener("DOMContentLoaded", function() {
      scrollToBottom('sales_cart_form')
    });

    function updateTotal(product) {
        var price = document.getElementById('price-' + product);
        var qty = document.getElementById('qty-' + product);
        var total = document.getElementById('total-' + product);
        total.value = (price.value * qty.value);
        
        var quoteTotalPrice = 0;
        var quoteTotal = document.getElementById('quote-total');
        <?php
            foreach ($itemsInOrder as $itemCode) {
            if (empty($itemCode)) continue;
        ?> 
            price = document.getElementById('price-<?=$itemCode?>');
            qty = document.getElementById('qty-<?=$itemCode?>');
            quoteTotalPrice = parseInt(quoteTotalPrice) + (parseInt(price.value) * parseInt(qty.value));
        <?php
            }
        ?>
        quoteTotal.value = quoteTotalPrice;
    }
</script>
<br/>

<h3>Sales Quote</h3>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<div class="small-font">[<a href="/_sales/orders">Return to Orders</a>]</div>
<hr/><br/>

<?php
if (isset($salesOrder->customer_order_number) && !empty($salesOrder->customer_order_number)) {
?>
    <h1>Sales Order# <?=$salesOrder->customer_order_number?></h1>
<?php
}
?>
<?php
if (!isset($_REQUEST['btn_save']) && !isset($_REQUEST['btn_quote']) && !isset($_REQUEST['btn_create'])) {
?>
    <div id="sales_cart_form">
        <form method="post" action="/_sales/cart/<?=!empty($order_code) ? $order_code : ''?>">
          
            <input id="order_code" type="hidden" name="order_code" value="<?=!empty($order_code) ? $order_code : ''?>" />
            <input id="order_id" type="hidden" name="order_id" value="<?=!empty($order_id) ? $order_id : ''?>" />
            <input id="organization_id" type="hidden" name="organization_id" value="<?=isset($organization->id) ? $organization->id : ''?>" />
        
            <?php
                if (!empty($organization_id)) {
            ?>
                <p>Organization: <br/><strong><?=$organization->name?></strong></p>
            <?php
                } else {
            ?>
                <select id="organization_id" name="organization_id">
                    <option value="">Select Customer</option>
                    <?php
                        foreach ($organizations as $organization) {
                            $selected = '';
                            if ($organization->id == $organization_id) $selected = 'selected';
                            print "<option value=\"".$organization->id."\" $selected>".$organization->name."</option>";
                        }
                    ?>
                </select>
            <?php
                }
                // if members then a dropdown of members
                if (count($members)) {
                    if (!empty($member_id)) {
                ?>
                    <p>Organization Member:<br/><strong><?=$registerPerson->first_name . " " . $registerPerson->middle_name . " " . $registerPerson->last_name?></strong></p>
                    <input id="member_id" type="hidden" name="member_id" value="<?=$member_id?>" />      
                <?php
                    } else {
                ?>
                    <select id="member_id" name="member_id">
                        <?php
                            print "<option value=\"\">Select Member</option>";
                            foreach ($members as $member) {
                                $selected = '';
                                if ($member->id == $member_id) $selected = 'selected';
                                print "<option value=\"".$member->id."\" $selected>".$member->first_name . " " . $member->middle_name . " " . $member->last_name ."</option>";
                            }
                        ?>
                    </select>
                <?php
                    }
                }
                
                // if locations then a dropdown of billing location for member selected
                if (isset($locations) && count($locations)) {
                ?>
                <p>Customer Preferred <strong>Billing</strong> Address:</p>
                <select id="billing_location" name="billing_location">
                    <?php
                        $currentLocation = "";
                        foreach ($locations as $location) {
                            $selected = '';
                            if ($billing_location == $location->id) $selected = 'selected';
                            print "<option value=\"".$location->id."\" $selected>" . $location->name . " " . $location->address_1 . " " . $location->address_2 . " " . $location->city . "</option>";
                        }
                   ?>
                 </select>
                   <?php
                }
                
                // if locations then a dropdown of shipping location for member selected
                if (isset($locations) && count($locations)) {
                    ?>
                    <p>Customer Preferred <strong>Shipping</strong> Location:</p>
                    <select id="shipping_location" name="shipping_location">
                        <?php
                            $currentLocation = "";
                            foreach ($locations as $location) {
                                $selected = '';
                                if ($shipping_location == $location->id) $selected = 'selected';
                                print "<option value=\"".$location->id."\" $selected>" . $location->name . " " . $location->address_1 . " " . $location->address_2 . " " . $location->city . "</option>";
                            }
                       ?>
                     </select>

                    <br/><br/>
                    <label for="shipping_vendor">Select Shipment Vendor:</label><br/>
                    <select name="shipping_vendor" id="shipping_vendor">
                        <?php
                        foreach ($shippingVendors as $vendor) {
                        ?>
                            <option value="<?=$vendor?>" <?=($shipping_vendor == $vendor) ? "selected='selected'" : "";?>><?=$vendor?></option>
                        <?php
                        }
                        ?>
                    </select>
                    <br/><br/>
                
                    <!-- START First Table -->
                    <h3>Item(s) in Order</h3>
	                <div class="tableBody min-tablet">
	                <div class="tableRowHeader">
		                <div class="tableCell" style="width: 10%;">Code</div>
		                <div class="tableCell" style="width: 16%;">Title</div>
		                <div class="tableCell" style="width: 35%;">Description</div>
		                <div class="tableCell" style="width: 3%;">QTY</div>
                        <div class="tableCell" style="width: 15%;">Price</div>
                        <div class="tableCell" style="width: 15%;">TOTAL</div>
                        <div class="tableCell" style="width: 5%;"><span style="color: #666;">Delete</span></div>
	                </div>
                    <?php
                        $orderTotal = 0;
                        foreach ($itemsInOrder as $itemCode) {
                            $itemInCart = new \Product\Item();
                            $itemInCart->get($itemCode);
                            if (empty($itemCode)) continue;
                            $salesOrderItem = new \Sales\Order\Item();
                            $salesOrderItem->getByProductIdOrderId($itemInCart->id, $order_id);
                            $total = intval($salesOrderItem->unit_price * $salesOrderItem->quantity);
                            $orderTotal = $orderTotal + $total;
                    ?>    
	                <div class="tableRow">
		                <div class="tableCell">
			                <?=$itemCode?>
		                </div>
		                <div class="tableCell">
			                <?=$itemInCart->name?>
		                </div>
		                <div class="tableCell">
			                <textarea style="max-height: 35px;" name="description-<?=$itemCode?>"><?=!empty($_REQUEST["description-".$itemCode]) ? $_REQUEST["description-".$itemCode] : $salesOrderItem->description?></textarea>
		                </div>
		                <div class="tableCell">
		                    <?php
                		        if ($itemInCart->type == "inventory") {
                		    ?>
                                <select id="qty-<?=$itemCode?>" name="qty-<?=$itemCode?>" id="qty-<?=$itemCode?>" onchange="updateTotal('<?=$itemCode?>');"/>
                                    <?php for ($count = 1; $count <= 50; $count++) { ?>
                                        <option value="<?=$count?>" <?=($salesOrderItem->quantity == $count) ? "selected='selected'" : "";?>><?=$count?></option>
                                    <?php } ?>
                                </select> 
                		    <?php
                		        } else {
		                    ?>
                                <input id="qty-<?=$itemCode?>" type="hidden" value="1" name="qty-<?=$itemCode?>"/>
                                1 
                		    <?php
                		        }
		                    ?>               
		                </div>

		                <div class="tableCell">
			                $ <input id="price-<?=$itemCode?>" type="number" value="<?=!empty($_REQUEST["price-".$itemCode]) ? $_REQUEST["price-".$itemCode] : $salesOrderItem->unit_price?>" name="price-<?=$itemCode?>" step="1" onchange="updateTotal('<?=$itemCode?>');"/>
		                </div>
		                <div class="tableCell">
			                $ <input id="total-<?=$itemCode?>" name="total-<?=$itemCode?>" type="number" step="1" value="<?=$total?>" onchange="updateTotal('<?=$itemCode?>');"/>
		                </div>
		                <div class="tableCell">
			                <button type="submit" value="<?=$itemCode?>" name="btn_remove">&#x2716;</button>
		                </div>
	                </div>
                <?php
                    }
                ?>
	            <div class="tableRowHeader">
		            <div class="tableCell" style="width: 10%;"></div>
		            <div class="tableCell" style="width: 16%;"></div>
		            <div class="tableCell" style="width: 25%;"></div>
		            <div class="tableCell" style="width: 3%;"></div>
                    <div class="tableCell" style="width: 5%;"></div>
                    <div class="tableCell" style="width: 15%;">Total Quote:</div>
                    <div class="tableCell" style="width: 15%;">
                        $ <input id="quote-total" name="quote-total" type="number" step="1" value="<?=!empty($_REQUEST["quote-total"]) ? $_REQUEST["quote-total"] : $orderTotal?>"/>                            
                    
                    </div>
	            </div>	 
	            
                </div>
                <!--	END First Table -->
                <br/><br/>
                <label for="add_items_select"><strong>Add</strong> Item(s) for Order:</label><br/>
                <select name="add_items_select" id="add_items_select">
                    <option value="0">Choose Item</option>
                    <?php
                    foreach ($itemsForOrder as $item) {
                    ?>
                        <option value="<?=$item->code?>"><?=$item->code?> - <?=$item->name?></option>
                    <?php
                    }
                    ?>
                </select>
                <input id="items_in_order" type="hidden" name="items_in_order" value="<?=is_array($itemsInOrder) ? implode(",", $itemsInOrder) : ""?>" />
                <input type="submit" name="btn_add" value="+ Add" />
            <?php
                 }
            ?>
            <br/><br/>
            <input class="continue_button btn-secondary" type="submit" name="btn_submit" value="Continue &raquo;" />

            <?php
            if ($isReadyToQuote) {
            ?>
                <br/><br/><br/><br/>
                <input type="button" class="continue_button" name="btn_save" onclick="window.location.replace('/_sales/orders')" value="&ldca; Save For Later"/>
                <input type="submit" class="continue_button" name="btn_quote" value="&check; Create a Quote"/>
                <input type="submit" class="continue_button" name="btn_create" value="&plus; Create an Order"/>
            <?php
            }
            ?>
            <br/><br/><br/><br/>
            <input type="button" name="btn_reset" value="Start Over" onclick="window.location.replace('/_sales/cart')" />
        </form>
    </div>
<?php
} else {

    // if we're quoting or approving the order update as such
    if (isset($_REQUEST['btn_quote'])) {
    ?>
        <h1>Order has been set to <u>Quote</u>.</h1>
    <?php
    }
    if (isset($_REQUEST['btn_create'])) {
    ?>
        <h1>Order has been set to <u>Approved</u>.</h1>
    <?php
    }
}
?>
