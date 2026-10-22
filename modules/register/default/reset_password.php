<script type="text/javascript" src="/includes/register-reset-password.js"></script>

<?php if (isset($_REQUEST['status']) && $_REQUEST['status'] == "complete") { ?>
<section class="section-pad-sm">
	<h1>Password updated</h1>
	<ul class="pageMessage">
		<li class="pageMessage--info">Your password has been updated. Please sign in with the new password.</li>
	</ul>
	<div class="button-group">
		<a class="button" href="/_register/login">Back to sign in</a>
	</div>
</section>
<?php return; } ?>

<section class="section-pad-sm">
	<h1>Reset Password</h1>
<?php if ($page->errorCount() > 0) { ?>
	<ul class="pageMessage">
		<li class="pageMessage--error"><?=$page->errorString()?></li>
	</ul>
<?php } ?>
<?php if ($page->success) { ?>
	<ul class="pageMessage">
		<li class="pageMessage--info"><?=$page->success?></li>
	</ul>
<?php } else { ?>
	<ul class="pageMessage">
		<li class="pageMessage--info">Enter your current password, then choose a new one and confirm it.</li>
	</ul>
	<form id="reg_form" name="loginForm" method="post" action="<?=PATH?>/_register/reset_password">
		<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
		<h2>Change your password</h2>
		<ul class="section-grid">
<?php if (! $GLOBALS['_SESSION_']->superElevated()) { ?>
			<li class="form-field">
				<label for="currentPassword">Current password</label>
				<input type="password" id="currentPassword" name="currentPassword" autocomplete="current-password" autofocus>
			</li>
<?php } ?>
			<li class="form-field">
				<label for="password">New password</label>
				<input type="password" id="password" name="password" autocomplete="new-password"<?php if ($GLOBALS['_SESSION_']->superElevated()) { ?> autofocus<?php } ?>>
			</li>
			<li class="form-field">
				<label for="password_2">Confirm new password</label>
				<input type="password" id="password_2" name="password_2" autocomplete="new-password">
			</li>
		</ul>
		<div class="button-group">
			<button type="submit" name="btn_submit" value="Update Password">Update Password</button>
		</div>
	</form>
<?php } ?>
</section>
