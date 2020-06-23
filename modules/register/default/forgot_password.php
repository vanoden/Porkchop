<script src="https://www.google.com/recaptcha/api.js"></script>
<div class="body">
    <?php	 if ($page->errorCount() > 0) { ?>
        <div class="form_error"><?=$page->errorString()?></div>
    <?php	 } ?> 
	<!-- Main Body -->
	<FORM ACTION="forgot_password" METHOD="POST">
	<INPUT TYPE="HIDDEN" NAME="action" VALUE="submit">
	<h1 class="registerHeading">Enter the email associated with your account</h1>
	<p class="form_instruction">You will receive and email with a link to reset your password.</p>
	<div class="copy_2"><INPUT id="autofocus" TYPE="TEXT" NAME="email_address" SIZE="50" class="input"></div>
	<div class="g-recaptcha" data-sitekey="6LeTdfgSAAAAAPZ5Fb-J6R_X9GctCVy8l2MrUCuO"></div>
	<div><INPUT TYPE="SUBMIT" NAME="btn_submit" VALUE="Get Password" class="button"></div>
	</FORM>
</div>
