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

<style>
.thank-you-container {
	grid-column: 2/-2;
	display: grid;
	grid-template-columns: subgrid;
	text-align: center;
	margin: 2rem 0;
}

.thank-you-content {
	grid-column: span 6;
	display: flex;
	flex-direction: column;
	align-items: center;
	text-align: center;
	margin-top: 2rem;
}

.thank-you-content h2 {
	text-align: center;
	margin-bottom: 1rem;
}

.thank-you-content p {
	margin: 0.5rem 0;
	text-align: center;
}
</style>

<section class="thank-you-container">
	<div class="thank-you-content">
		<h2 class="pageTitle">Thank you for registering with <?=$company->name?></h2>
		<p>You will receive an email soon to confirm your email address is valid.</p>
		<p><strong>Important:</strong> Our emails are sent from <strong><?=$send_from?></strong>. To ensure you receive all important notifications, please:</p>
		<ul style="text-align: left; display: inline-block; margin: 1rem 0;">
			<li>Add <strong>no-reply@spectrosinstruments.com</strong> to your email address book or contacts</li>
			<li>Check your spam, junk, or bulk mail folders if you don't see the email in your inbox right away</li>
		</ul>
	</div>
</section>
