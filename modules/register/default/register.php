<script type="text/javascript">
	function submitForm()
	{
		if (document.register.password.value.length < 6)
		{
			alert("Your password is too short.");
			return false;
		}

		if (document.register.password.value != document.register.password_2.value)
		{
			alert("Your passwords don't match.");
			return false;
		}

		return true;
	}
</script>
<span class="title">Registration</span>
<span class="form_instruction">Fill out this form to register</span>
<form name="register" action="/_register/register" method="POST">
	<input type="hidden" name="target" value="<?=$target?>">
	<div class="instruction"><r7_page.message id=100></div>
	<?	if ($GLOBALS['_page']->error) { ?>
	<div class="form_error"><?=$GLOBALS['_page']->error?></div>
	<?	} ?>
	<div id="registerFormSubmit">
		<div id="registerFirstName" class="registerQuestion">
			<span class="label registerLabel registerFirstNameLabel">*First Name:</span>
			<input type="text" class="value registerValue registerFirstNameValue" name="first_name" value="<?=$_REQUEST['first_name']?>" />
		</div>
		<div id="registerLastName" class="registerQuestion">
			<span class="label registerLabel registerLastNameLabel">*Last Name:</span>
			<input type="text" class="value registerValue registerLastNameValue" name="last_name" value="<?=$_REQUEST['last_name']?>" />
		</div>
		<div id="registerLogin" class="registerQuestion">
			<span class="label registerLabel registerLoginLabel">*Login:</span>
			<input type="text" class="value registerValue registerLoginValue" name="login" value="<?=$_REQUEST['login']?>" />
		</div>
		<div id="registerPassword" class="registerQuestion">
			<span class="label registerLabel registerPasswordLabel">*Password:</span>
			<input type="password" class="value registerValue registerPasswordValue" name="password" />
		</div>
		<div id="registerPasswordConfirm" class="registerQuestion">
			<span class="label registerLabel registerPasswordLabel">*Confirm Password:</span>
			<input type="password" class="value registerValue registerPasswordValue" name="password_2" />
		</div>
		<div id="registerWorkEmail" class="registerQuestion">
			<span class="label registerLabel registerLoginLabel">*Work Email:</span>
			<input type="text" class="value registerValue registerLoginValue" name="work_email" value="<?=$_REQUEST['work_email']?>" />
		</div>
		<div id="registerHomeEmail" class="registerQuestion">
			<span class="label registerLabel registerLoginLabel">*Home Email:</span>
			<input type="text" class="value registerValue registerLoginValue" name="home_email" value="<?=$_REQUEST['home_email']?>" />
		</div>
		<div id="registerSubmit" class="registerQuestion">
			<input type="submit" name="method" value="Apply" class="button submitButton registerSubmitButton" onclick="return submitForm();" />
		</div>
	</div>
</form>
