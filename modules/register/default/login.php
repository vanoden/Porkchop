<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script type="text/javascript" src="/includes/register-login.js"></script>

<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>
<?php	} ?>    

<h1 class="pageSect_full">Log In to Your Account</h1>
<ul class="pageMessage infoText">
  <li>This site is for authorized use by employees and customers of <r7 object="company" property="name"/>. No other use is permitted.</li>
</ul>

<!-- <section id="reg_form" onkeypress="return loginSubmitEnter(event)" class="body"> -->
	<form id="reg_form" onkeypress="return loginSubmitEnter(event)" name="loginForm" method="post" action="<?=PATH?>/_register/login">
		<input type="hidden" name="login_target" value="<?=$target?>" />
		<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
		<div id="register_form">
			<ul class="form-grid four-col">
				<li>
					<label for="dateReceived">Login</label>
					<input type="text" id="login" name="login" autofocus/></li>
				<li>
					<label for="dateReceived">Password</label>
					<input type="password" name="password">
				</li>
				<?php	if ($CAPTCHA_GO) { ?>
				    <li class="g-recaptcha" data-sitekey="<?=$captcha_public_key?>"></li>
				<?php	}	?>
			</ul>
      <div class="button-group">
        <button href="#" onclick="document.loginForm.submit();">Sign In</button>
        <a href="/_register/forgot_password" class="button btn-secondary">Recover Password</a>
        <a href="<?=PATH?>/_register/new_customer" class="button btn-secondary">Register Now</a>
      </div>
		</div>
	</form>
<!-- </section> -->
