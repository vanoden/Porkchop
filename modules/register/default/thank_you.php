

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

<div style="text-align:center">
    <p>
        <h3>
            Thank you for registering with <?=$company->name?>
        </h3>    
    	<h6>You will <strong>receive an email</strong> soon to confirm your email address is valid.</h6>
    	<h6>Please check your <strong>spam / other</strong> mail folder if you have <u>trouble finding it in your inbox</u>.</h6>
    </p>
</div>