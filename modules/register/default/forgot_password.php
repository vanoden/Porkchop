<script src="https://www.google.com/recaptcha/api.js"></script>
<div class="body">
   <?php	 if ($page->errorCount() > 0) { ?>
       <div class="form_error"><?=$page->errorString()?></div>
   <?php	 } ?> 
   <!-- Main Body -->
   <form action="forgot_password" method="POST">
      <input type="hidden" name="action" value="submit">
      <h1 class="registerHeading">Enter the email associated with your account</h1>
      <p class="form_instruction">You will receive and email with a link to reset your password.</p>
      <div class="copy_2">
        <input id="autofocus" type="TEXT" name="email_address" SIZE="50" class="input">
      </div>
      <div class="g-recaptcha" data-sitekey="6LeTdfgSAAAAAPZ5Fb-J6R_X9GctCVy8l2MrUCuO"></div>
      <input type="hidden" name="captcha_auth" value="<?=$_GET['captcha_auth']?>">
      <div>
        <input type="submit" name="btn_submit" value="Get Password" class="button">
      </div>
   </form>
</div>
