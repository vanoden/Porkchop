<script type="text/javascript">
	function submitForm() {
		if (document.register.password.value.length < 6) {
			alert("Your password is too short.");
			return false;
		}
		if (document.register.password.value != document.register.password_2.value) {
			alert("Your passwords don't match.");
			return false;
		}
		return true;
	}
</script>

<h1>Registration</h1>
<span class="form_instruction">Fill out all required information to apply. You will receive and email once your account has been created.</span>
<form name="register" action="/_register/register" method="POST">
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
	<input type="hidden" name="target" value="<?=$target?>">
	<div class="instruction"><r7_page.message id=100></div>
    <?php	 if ($page->errorCount() > 0) { ?>
        <div class="form_error"><?=$page->errorString()?></div>
    <?php	 } ?>
	<div id="registerFormSubmit">
		<div class="form-group">
			<div id="registerFirstName" class="registerQuestion">
					<span class="label registerLabel registerFirstNameLabel">*First Name:</span>
					<input type="text" class="value registerValue registerFirstNameValue" name="first_name" value="<?=isset($_REQUEST['first_name']) ? htmlspecialchars($_REQUEST['first_name']) : ''?>" />
			</div>
			<div id="registerLastName" class="registerQuestion">
				<span class="label registerLabel registerLastNameLabel">*Last Name:</span>
				<input type="text" class="value registerValue registerLastNameValue" name="last_name" value="<?=isset($_REQUEST['last_name']) ? htmlspecialchars($_REQUEST['last_name']) : ''?>" />
			</div>
		</div>
		
		<div class="form-group">
			<div id="registerLogin" class="registerQuestion">
				<span class="label registerLabel registerLoginLabel">*Login:</span>
				<input type="text" class="value registerValue registerLoginValue" name="login" value="<?=isset($_REQUEST['login']) ? htmlspecialchars($_REQUEST['login']) : ''?>" />
			</div>
			<div id="registerPassword" class="registerQuestion">
				<span class="label registerLabel registerPasswordLabel">*Password:</span>
				<input type="password" class="value registerValue registerPasswordValue" name="password" />
			</div>
			<div id="registerPasswordConfirm" class="registerQuestion">
				<span class="label registerLabel registerPasswordLabel">*Confirm Password:</span>
				<input type="password" class="value registerValue registerPasswordValue" name="password_2" />
			</div>
		</div>
		
		<div class="form-group">
			<div id="registerWorkEmail" class="registerQuestion">
				<span class="label registerLabel registerLoginLabel">*Work Email:</span>
				<input type="email" class="value registerValue registerLoginValue" name="work_email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,63}$" value="<?=isset($_REQUEST['work_email']) ? htmlspecialchars($_REQUEST['work_email']) : ''?>" />
			</div>
			<div id="registerHomeEmail" class="registerQuestion">
				<span class="label registerLabel registerLoginLabel">*Home Email:</span>
				<input type="email" class="value registerValue registerLoginValue" name="home_email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,63}$" value="<?=isset($_REQUEST['home_email']) ? htmlspecialchars($_REQUEST['home_email']) : ''?>" />
			</div>
		</div>
		
			<div id="registerSubmit" class="registerQuestion">
				<input type="submit" name="method" value="Apply" class="button submitButton registerSubmitButton" onclick="return submitForm();" />
				<a class="button secondary" href="/_register/login">Cancel</a>
			</div>
		</div><!-- end registerFormSubmit -->
	</div>
</form>
