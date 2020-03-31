	<script language="Javascript">
        function goMethodUrl(url) {
            location.href = '/_company/api/&amp;method='+url;
            return true;
        }
	</script>
	<div id="main">
		<div id="scroller" style="width: 600px; height: 500px; overflow: auto; margin-left: 50px;">
			<div class="methodTitle">Request</div>
			<pre id="requestContent" style="text-align: left; width: 550px; height: 100px; overflow: auto; font-size: 11px; border: 1px dashed blue;"><?=print_r($_REQUEST)?></pre>
			<form method="post" action="/_compani/api">
			<input type="hidden" name="method" value="ping">
			<div class="method">
				<div class="methodTitle">ping</div>
				<div class="methodFooter"><input type="submit" name="btn_submit" value="Submit" class="methodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="/_company/api">
			<input type="hidden" name="method" value="getCompany">
			<div class="method">
				<div class="methodTitle">getCompany</div>
				<div class="methodParameter">
					<div class="methodLabel">id</div>
					<div class="methodValue"><input type="text" name="id" class="methodInput"/></div>
				</div>
				<div class="methodFooter"><input type="submit" name="btn_submit" value="Submit" class="methodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="/_company/api">
			<input type="hidden" name="method" value="updateCompany">
			<div class="method">
				<div class="methodTitle">updateCompany</div>
				<div class="methodParameter">
					<div class="methodLabel">id</div>
					<div class="methodValue"><input type="text" name="id" class="methodInput"/></div>
				</div>
				<div class="methodParameter">
					<div class="methodLabel">name</div>
					<div class="methodValue"><input type="text" name="name" class="methodInput"/></div>
				</div>
				<div class="methodFooter"><input type="submit" name="btn_submit" value="Submit" class="methodSubmit"/></div>
			</div>
			</form>
		</div>
	</div>
