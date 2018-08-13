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
	<h1 class="registerHeading">Enter the email associated with your account</h1>
	<p class="form_instruction">You will receive and email with a link to reset your password.</p>
	<div class="copy_2"><INPUT id="autofocus" TYPE="TEXT" NAME="email_address" SIZE="50" class="input"></div>
	<div><INPUT TYPE="SUBMIT" NAME="btn_submit" VALUE="Get Password" class="button"></div>
	</FORM>
	<?	} ?>
</div>