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

<section class="thank-you-container">
	<div class="thank-you-content">
		<h2 class="pageTitle">Thank you for registering with <?=$company->name?></h2>
		<p>You will receive an email soon to confirm your email address is valid.</p>
		<p><strong>Important:</strong> Our emails are sent from <strong><?=$send_from?></strong>. To ensure you receive all important notifications, please:</p>
		<ul class="email-tips-inline">
			<li>Add <strong>no-reply@spectrosinstruments.com</strong> to your email address book or contacts</li>
			<li>Check your spam, junk, or bulk mail folders if you don't see the email in your inbox right away</li>
		</ul>
	</div>
</section>
