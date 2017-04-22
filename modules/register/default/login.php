<script language="Javascript">
	function loginSubmitEnter(e)
	{
		var code = (e.keyCode ? e.keyCode : e.which);
		if (code == 13) {
			document.loginForm.submit();
		}
	}
</script>
<div id="reg_form" onkeypress="return loginSubmitEnter(event)" class="body" style="width: 600px">
	<form name="loginForm" method="post" action="<?=PATH?>/_register/login">
		<input type="hidden" name="login_target" value="<?=$target?>" />
		<div id="register_form">
			<div class="title">Site Login</div>
<? if ($GLOBALS['_page']->error) { ?>
			<div class="form_error registerLoginError"><?=$GLOBALS['_page']->error?></div>
<? } ?>
			<div id="register_important">
				<p class="value form_instruction" style="width: 100%; clear: both; display: block; padding: 0px;"><span class="form_important">Important</span><br>This site is for authorized use by employees and customers of <r7 object="company" property="name"/>. No other use is permitted.</p>
			</div>
			<div id="register_login_container">
				<div id="register_username">
					<span class="label labelRegisterLogin">Login</span>
					<input type="text" class="value input valueRegisterLogin" id="login" name="login" autofocus/>
				</div>
				<div id="register_password">
					<span class="label labelRegisterPassword">Password</span>
					<input class="value input valueRegisterPassword" type="password" name="password">
				</div>
			</div>
			<div id="register_submit">
				<a class="button buttonRegisterLogin" href="#" onclick="document.loginForm.submit();">Login</a>
			    <a class="button" id="recover_password_link" href="/_register/forgot_password">Recover Password</a>
			    <a class="button" id="registration_link" href="<?=PATH?>/_contact/form">Sign Up</a>
			</div>
		</div>
	</form>
</div>
