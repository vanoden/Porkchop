<div class="body">
	<?PHP
		# Display Error Message If Any
		if ($GLOBALS['_page']->error)
		{
	?>
	<div class="form_error"><?=$GLOBALS['_page']->error?></div>
	<?PHP
		}
		if ($GLOBALS['_page']->success)
		{
	?>
	<div class="form_success"><?=$GLOBALS['_page']->success?></div>
	<?
		}
		else
		{
	?>
	<!-- Main Body -->
	<FORM ACTION="forgot_password" METHOD="POST">
	<INPUT TYPE="HIDDEN" NAME="action" VALUE="submit">
	<div class="heading_2 registerHeading">Please enter your email address in the field below.</div>
	<div class="heading_2 registerCopy">We will send you an email with your login and a new password.</div>
	<div class="copy_2"><INPUT id="autofocus" TYPE="TEXT" NAME="email_address" SIZE="50" class="input"></div>
	<div><INPUT TYPE="SUBMIT" NAME="btn_submit" VALUE="Get Password"></div>
	</FORM>
	<?	} ?>
</div>