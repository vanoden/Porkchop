<section>
    <h1 class="pageSect_full">Password Reset</h1>
</section>
<?php	if ($page->success) { ?>
<section id="form-message">
	<ul class="connectBorder progressText">
		<li><?=$page->success?></li>
	</ul>
</section>
<?php	} else { ?>
<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>
<?php }	?>
<?php if (isset($_REQUEST['status']) && $_REQUEST['status'] == "complete") { ?>
<section id="reg_complete" class="body">
	<p class="pageSect_full">Your password has been updated.  Please <a href="/_register/login">log back in</a>.</p>
</section>
<?php return; } ?>
<section id="reg_form" class="body">
	<form name="loginForm" method="post" action="<?=PATH?>/_register/reset_password">
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
<?php
	if (! $GLOBALS['_SESSION_']->superElevated()) { ?>
	<h3>Enter current password</h3>
		<div id="register_confirm">
			<ul class="form-grid">
				<li>
					<label for="currentPass">Provide Current Password</label>
					<input type="password" id="currentPassword" name="currentPassword"/>
				</li>
			</ul>
		</div>
<?php	} ?>
    <h3>Create a new password</h3>
		<div id="register_form">
			<ul class="form-grid">
				<li>
					<label for="dateReceived">New Password</label>
					<input type="password" id="password" name="password" autofocus/></li>
				<li>
					<label for="dateReceived">Confirm Password</label>
					<input type="password" id="password_2" name="password_2"/>
				</li>
			</ul>
			<input type="submit" name="btn_submit" value="Update Password" />
		</div>
	</form>
</section>
<?php } ?>
