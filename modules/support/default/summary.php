<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style>
   .ui-autocomplete-loading {
        background: white url("https://jqueryui.com/resources/demos/autocomplete/images/ui-anim_basic_16x16.gif") right center no-repeat;
   }
   .center {
        text-align:center;
   }
</style>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
   // date picker with max date being current day
   window.onload = function() {
	  $("#dateStart").datepicker({
		   onSelect: function(dateText, inst) {
			   var minDate = document.getElementById('min_date');
			   minDate.value = dateText;
		   }, 
		   maxDate: '0'
	   });
	   $("#dateStart").datepicker("setDate", new Date('<?=$_REQUEST['dateStart']?>'));
	   
	  $("#dateEnd").datepicker({
		   onSelect: function(dateText, inst) {
			   var maxDate = document.getElementById('max_date');
			   maxDate.value = dateText;
		   }, 
		   maxDate: '0'
	   });
	   $("#dateEnd").datepicker("setDate", new Date('<?=$_REQUEST['dateEnd']?>'));
   }
</script>
<div style="width: 756px;">
	<div class="breadcrumbs">
		Support Home 
	</div>
</div>
<h2 style="display: inline-block;"><i class='fa fa-list-ol' aria-hidden='true'></i> Support Requests [Summary Report]</h2>
<?php include(MODULES.'/support/partials/search_bar.php'); ?>
<form action="/_support/summary" method="post" autocomplete="off">
  <input id="min_date" type="hidden" name="min_date" readonly />
  <input id="max_date" type="hidden" name="min_date" readonly />
  <table>
	 <tr>
		<th><span class="label"><i class="fa fa-calendar-check-o" aria-hidden="true"></i> Start Date</th>
		<th><span class="label"><i class="fa fa-calendar-check-o" aria-hidden="true"></i> End Date</th>
		<th><span class="label"><i class="fa fa-filter" aria-hidden="true"></i> Status</span></th>
	 </tr>
	 <tr>
		<td><input type="text" id="dateStart" name="dateStart" class="value input" value="<?=$_REQUEST['dateStart']?>" /></td>
		<td><input type="text" id="dateEnd" name="dateEnd" class="value input" value="<?=$_REQUEST['dateEnd']?>" /></td>
		<td style="width: 50%;">
		   <?php foreach ($request->validStatus as $possibleStatus) { ?>
		   <input type="checkbox" name="<?=$possibleStatus?>" value="<?=$possibleStatus?>"
			  <?php
				 if ($_REQUEST[$possibleStatus] == $possibleStatus) print " checked"; 
				 ?> /><?=$possibleStatus?>
		   <?php } ?>
		</td>
	 </tr>
  </table>
  <input type="submit" name="btn_submit" class="button" value="Find Requests" style="float:right;" /><br/><br/>
</form>
<table>
    <tr>
        <th>Code</th>
	    <th>Date Requested</th>
	    <th>Requested By</th>
	    <th>Organization</th>
	    <th>Type</th>
	    <th>Status</th>
    </tr>
    <?php	
        foreach ($requests as $request) { 
            $productInfo = $request->items();
            try {
                $supportRequestItem = new Support\Request\Item($productInfo[0]->request->id);
            } catch (Exception $e) {}
    ?>
        <tr>
            <td><a href="/_support/request_detail/<?=$request->code?>"><?=$request->code?></a></td>
	        <td><?=$request->date_request?></td>
	        <td><strong><?=$request->customer->first_name?> <?=$request->customer->last_name?></strong></td>
	        <td><span style="color:blue;"><?=$request->customer->organization->name?></span></td>
	        <td><?=$request->type?></td>
	        <td><?=$request->status?></td>
        </tr>        
        <tr>
            <td>&nbsp;</td>
	        <td>&nbsp;</td>
	        <td>&nbsp;</td>
	        <td>
	            <div style="padding: 10px;">
    	            <strong>Product:</strong> <?=$productInfo[0]->product->name?> [<?=$productInfo[0]->product->code?> / QTY: <?=$supportRequestItem->quantity?>]<br/>
	                <strong>Serial:</strong> <?=$supportRequestItem->serial_number?>
	                <i><?=$productInfo[0]->product->description?></i><br/>
	                <div style="max-width: 500px; color: brown;"><strong>ISSUE: </strong><?=$supportRequestItem->description?></div>
	            </div>
	        </td>
	        <td>&nbsp;</td>
	        <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
	        <td>&nbsp;</td>
	        <td>&nbsp;</td>
            <td>&nbsp;</td>
	        <td>&nbsp;</td>
	        <td>&nbsp;</td>
        </tr>
        <tr style="border: solid 1px #000;">
            <td>&nbsp;</td>
	        <td>&nbsp;</td>
	        <td>&nbsp;</td>
            <td>&nbsp;</td>
	        <td>&nbsp;</td>
	        <td>&nbsp;</td>
        </tr>
        
    <?	} ?>
</table>
<?php
if (empty($requests) && $firstSearch) {
?>
    <h3 class="center">Select Criteria above to search for requests</h3>
<?php
} else if (empty($requests) && !$firstSearch) {
?>
    <h3 class="center">No Results</h3>
<?php
}
?>
