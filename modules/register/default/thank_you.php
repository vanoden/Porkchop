<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>

<?php	} else if ($page->success) { ?>
<section id="form-message">
	<ul class="connectBorder progressText">
		<li><?=$page->success?></li>
	</ul>
</section>
<?php	} ?>

<h2 class="pageTitle">Thank you for registering with <?=$company->name?></h2>
<p>You will receive an email soon to confirm your email address is valid. Please check your spam / other mail folder if you have trouble finding it in your inbox.</p>