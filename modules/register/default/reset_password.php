<style>
    ul {
        list-style-type: none;
        padding: 0;
        margin:0;
    }
</style>

<?php	if ($page->success) { ?>
<section id="form-message">
	<ul class="connectBorder progressText">
		<li><?=$page->success?></li>
	</ul>
</section>
<?php	} else { ?>
<section id="form-message">
	<ul class="connectBorder infoText">
		<li>This site is for authorized use by employees and customers of <r7 object="company" property="name"/>. No other use is permitted.</li>
	</ul>
</section>

<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>
<?php }	?>

<section id="reg_form"class="body">
	<form name="loginForm" method="post" action="<?=PATH?>/_register/reset_password">
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
			<button type="submit">Update Password</button>
		</div>
	</form>
</section>
<?php } ?>
