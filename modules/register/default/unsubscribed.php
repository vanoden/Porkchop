<section class="unsubscribe-confirm">
<?php if (!empty($GLOBALS['_page']->error)) { ?>
	<p class="unsubscribe-confirm__message unsubscribe-confirm__message--error"><?=htmlspecialchars($GLOBALS['_page']->error)?></p>
<?php } else { ?>
	<h1>Unsubscribe Successful</h1>
	<p class="unsubscribe-confirm__message">We will no longer send email newsletters or updates. If you would like to receive newsletters in the future, just <a href="/_register/login">login</a> and change your account settings. Thank you.</p>
<?php } ?>
</section>
