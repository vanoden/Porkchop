<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="/js/monitor.js"></script>
<script src="/js/register.js"></script>
<script type="text/javascript">

	// make sure the serial number is valid
	function checkSerial() {
		var productInput = document.getElementById('productId');
		var productID = productInput.options[productInput.selectedIndex].value;

		var serialInput = document.getElementById('serialNumber');
		var serialNumberMessage = document.getElementById('serialNumberMessage');
		var serialNumberMessageOK = document.getElementById('serialNumberMessageOK');

		if (serialInput.value.length < 1) return true;
		var code = serialInput.value;
		var asset = Object.create(Asset);

		if (asset.get(code)) {
			if (asset.product.id == productID) {
				serialInput.style.border = 'solid 2px green';
				serialNumberMessage.style.display = 'none';
				serialNumberMessageOK.innerHTML = 'Serial number has been found, thank you for providing!';
				serialNumberMessageOK.style.display = 'block';
				return true;
			}
			else {
				serialInput.style.border = 'solid 2px red';
				serialNumberMessage.innerHTML = 'Product not found with that serial number';
				serialNumberMessage.style.display = 'block';
				serialNumberMessageOK.style.display = 'none';
				return false;
			}
		}
		else {
			serialInput.style.border = 'solid 2px red';
			serialNumberMessage.innerHTML = 'Serial number not found in our system';
			serialNumberMessage.style.display = 'block';
			serialNumberMessageOK.style.display = 'none';
			return false;
		}
	}
	
	// date picker with max date being current day
    window.onload = function() {
       $("#purchased").datepicker({ maxDate: '0' });
       <?php if($page->serialError) { ?>
           $("#serialNumber").css('background-color','#FFBCC3');
       <?php
       }?>
    }
</script>
<h1>Warranty Registration</h1>
<?php
if (!$page->error) {
?>
    <span class="form_instruction">Welcome! Fill out all required information your device to our product warrenty.</span>
<?php
}
?>
<form name="register" action="/_support/register_product" method="POST" autocomplete="off">
	<?php	if ($page->error) { ?>
	    <div class="form_error"><?=$page->error?></div>
	<?php	} ?>
	<div id="registerProductSubmit">
        <h3>Select your Product</h3>
         <div id="product_details">
            <span class="label" style="display: block"><i class="fa fa-cog" aria-hidden="true"></i> Product:</span>
            <select id="productId" name="productId" class="value input collectionField" style="display: block" onchange="document.getElementById('serialNumberMessage').style.display = 'none';">
               <?php	foreach ($productsAvailable as $product) { ?>
                    <option value="<?=$product->id?>"<?php if ($product->id == $selectedProduct) print " selected"; ?>><?=$product->code?> - <?=$product->description?></option>
               <?php	} ?>
            </select>
            <span class="label"><i class="fa fa-barcode" aria-hidden="true"></i> Serial #</span>
            <input type="text" id="serialNumber" class="long-field" name="serialNumber" placeholder="Serial Number" onchange="checkSerial()" value="<?=$serialNumber?>">
            <div id="serialNumberMessage" style="color:red; display:none;">Serial number not found in our system<br/><br/></div>
            <div id="serialNumberMessageOK" style="color:green; display:none;">Serial number has been found, thank you for providing!<br/><br/></div>
         </div>
         <div>
            <i class="fa fa-calendar" aria-hidden="true"></i>
            <span class="label">Date Purchased</span>
            <input type="text" id="purchased" name="purchased" placeholder="MM/DD/YYYY" value="<?=$purchased?>">
            <i class="fa fa-truck" aria-hidden="true"></i> Distributor
            <input type="text" id="distributor" name="distributor" placeholder="e.g. univar / airmet.com" value="<?=$distributor?>">
         </div>
	    <div style="padding-top: 10px;">
	        <?php
    	    if (!$page->success) {
	        ?>
    		    <input type="submit" name="btnSubmit" class="button" value="Submit" style="padding:10px; min-width: 120px;" />
		    <?php
		    } else {
		    ?>
    		    <div class="form_success"><?=$page->success?></div>
		    <?php
		    }
		    ?>
	    </div>
	</div>
</form>
