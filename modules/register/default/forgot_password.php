<section class="section-pad-sm">
	<h1>Forgot Password</h1>
<?php	if ($page->errorCount() > 0) { ?>
	<ul class="pageMessage">
		<li class="pageMessage--error"><?=$page->errorString()?></li>
	</ul>
<?php	} ?>
	<ul class="pageMessage">
		<li class="pageMessage--info">Enter the email address for your account. We will send a link to reset your password.</li>
	</ul>
	<form id="reg_form" name="loginForm" method="post" action="/_register/forgot_password">
		<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
		<div class="section-grid grid-col-4">
			<div class="form-field">
				<label for="email_address">Email address</label>
				<input id="email_address" type="email" name="email_address" required autofocus value="<?=htmlspecialchars($_REQUEST['email_address'] ?? '')?>">
			</div>
		</div>
		<div class="button-group">
			<button type="submit" name="method" value="submit">Get Password</button>
		</div>
		<div class="g-recaptcha" id="g-recaptcha" data-sitekey="<?=htmlspecialchars($GLOBALS['_config']->captcha->public_key ?? '')?>"></div>
	</form>
</section>
