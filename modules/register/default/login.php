<script language="Javascript">
	function loginSubmitEnter(e) {
		var code = (e.keyCode ? e.keyCode : e.which);
		if (code == 13) document.loginForm.submit();
	}
</script>

	<div id="reg_form" onkeypress="return loginSubmitEnter(event)" class="body">
	<input type="hidden" name="login_target" value="<?=$target?>" />
	<?php if ($page->errorCount() > 0) { ?>
	<div class="form_error registerLoginError"><?=$page->errorString()?></div>
	<?php } ?>
	<form class="login-form"name="loginForm" method="post" action="<?=PATH?>/_register/login">
		<section class="form-group">
			<p class="form_instruction">This site is only for use by authorized employees and customers of <r7 object="company" property="name"/>.</p>
			<ul class="form-fields">
				<li><label for="labelRegisterLogin">User Name</label><input type="text" name="login" class="valueRegisterLogin" id="login" autofocus/></li>
				<li><label for="labelRegisterPassword">Password</label><input type="password" name="password" class="valueRegisterPassword"></li>
			</ul>
			<ul class="form-buttons">
				<li><a role="button" class="button buttonRegisterLogin" href="#" onclick="document.loginForm.submit();">Sign In</a></li>
				<li><a role="button" class="button secondary" id="recover_password_link" href="/_register/forgot_password">Recover Password</a></li>
				<li><a role="button" class="button secondary" id="registration_link" href="<?=PATH?>/_register/new_customer">Register Now</a></li>
			</ul>
		</section>
	</form>
	</div>
