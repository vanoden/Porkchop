		<table>
		<?	if ($GLOBALS['_page']->error) { ?>
		<tr><td align="center" class="form_error"><?=$GLOBALS['_page']->error?></td></tr>
		<?	}
			else
			{
		?>
		<form action="/_register/unsubscribe" method="POST">
		<input type="hidden" name="unsubscribe_key" value="<?=$unsubscribe_key?>">
		<tr><td align="left" class="heading_2">Unsubscribe Successful</td>
		<tr><td align="left" class="copy_2">We will no longer send email newsletters or updates.  If you'd like to receive
				newsletters in the future, just <a href="/_register/login">login</a> to our site and change your account
				settings.  Thank You.</td></tr>
		</form>
		<?	} ?>
		<table>
