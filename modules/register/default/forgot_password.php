<script src="https://www.google.com/recaptcha/api.js"></script>
<script type="text/javascript" src="/includes/register-forgot-password.js"></script>

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

<section>
<h1 class="pageSect_full">Forgot Password</h1>
	<ul class="connectBorder infoText">
		<li>Enter the email associated with your account. If that address exists in our system, instructions will be sent to it to reset your password. Our emails are sent from <strong>no-reply@spectrosinstruments.com</strong> - please add this address to your email address book and check your spam folder if needed.</li>
	</ul>
</section>

<section class="table-group">
   <form action="forgot_password" method="POST">
      <input type="hidden" name="action" value="submit">
      <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
      <ul class="form-grid three-col copy_2">
			  <li><label for="name">Your account email:</label><span class="value"><input id="autofocus" type="TEXT" name="email_address" SIZE="50" class="input"></span></li>
      </ul>
      <?php
      $rc = $GLOBALS['_config']->register->requireCAPTCHA ?? null;
      $show_captcha_forgot = ($rc && isset($rc->forgot_password)) ? $rc->forgot_password : true;
      if ($show_captcha_forgot) { ?>
      <div class="g-recaptcha register-forgot-password-recaptcha" data-sitekey="<?=$GLOBALS['_config']->captcha->public_key?>"></div>
      <?php } ?>
      <div><input type="submit" name="btn_submit" value="Get Password" class="button"></div>
   </form>
</section>
