<script language="Javascript">
	function loginSubmitEnter(e) {
		var code = (e.keyCode ? e.keyCode : e.which);
		if (code == 13) document.loginForm.submit();
	}
</script>
<div id="reg_form" onkeypress="return loginSubmitEnter(event)" class="body">
	<form name="loginForm" method="post" action="<?=PATH?>/_register/login">
		<input type="hidden" name="login_target" value="<?=$target?>" />
		<div id="register_form">		
            <?php if ($page->errorCount() > 0) { ?>
			    <div class="form_error registerLoginError"><?=$page->errorString()?></div>
            <?php } ?>
			<div id="register_important">
				<p class="value form_instruction">This site is for authorized use by employees and customers of <r7 object="company" property="name"/>. No other use is permitted.</p>
			</div>
			<div id="register_login_container">
				<div id="register_username">
					<span class="label labelRegisterLogin">User Name</span>
					<input type="text" class="value input valueRegisterLogin" id="login" name="login" autofocus/>
				</div>
				<div id="register_password">
					<span class="label labelRegisterPassword">Password</span>
					<input class="value input valueRegisterPassword" type="password" name="password">
				</div>			
				<?php
				if ((isset($_SESSION['isRemovedAccount']) && $_SESSION['isRemovedAccount'] == 1) || $_SESSION['failedAttemptCount'] > 2) {
				?>
    				<div class="g-recaptcha" data-sitekey="6LeTdfgSAAAAAPZ5Fb-J6R_X9GctCVy8l2MrUCuO"></div>
				<?php
				}
				?>
			</div>
			<div id="register_submit">
				<a class="button buttonRegisterLogin" href="#" onclick="document.loginForm.submit();">Sign In</a>
			    <a class="button secondary" id="recover_password_link" href="/_register/forgot_password">Recover Password</a>
			    <a class="button secondary" id="registration_link" href="<?=PATH?>/_register/new_customer">Register Now</a>
			</div>
		</div>
	</form>
	</div>
