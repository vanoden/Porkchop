<form method="post" action="ldap_test">
<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
<div class="label">Username</div>
<input type="text" name="username" class="value" />
<div class="label">Password</div>
<input type="text" name="password" class="value" />
<input type="submit" name="btn_submit" value="Login" />
</form>