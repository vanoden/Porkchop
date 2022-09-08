<script src="https://www.google.com/recaptcha/api.js"></script>


<h2>Forgot Password</h2>

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
	<ul class="connectBorder infoText">
		<li>Enter the email associated with your account. If that address exists in our system, instructions will be sent to it to reset your password.</li>
	</ul>
</section>

<section class="table-group">
   <form action="forgot_password" method="POST">
      <input type="hidden" name="action" value="submit">
      <ul class="form-grid three-col copy_2">
			  <li><label for="name">Your account email:</label><span class="value"><input id="autofocus" type="TEXT" name="email_address" SIZE="50" class="input"></span></li>
      </ul>
      <div class="g-recaptcha" data-sitekey="6LeTdfgSAAAAAPZ5Fb-J6R_X9GctCVy8l2MrUCuO" style="margin-top: 30px;"></div>
      <input type="hidden" name="captcha_auth" value="<?=$_GET['captcha_auth']?>">
      <div><input type="submit" name="btn_submit" value="Get Password" class="button"></div>
   </form>
</section>






