		<table>
		<?	if ($page->errorCount() > 0) { ?>
		<tr><td align="center" class="form_error"><?=$page->errorString()?></td></tr>
		<?	}
			else
			{
		?>
		<form action="/_register/unsubscribe" method="POST">
		<input type="hidden" name="todo" value="update">
		<input type="hidden" name="eeid" value="<?=md5($r7_session["email_id"])?>">
		<tr><td align="left" class="heading_2">Unsubscribe <?=$unsubscribe_name?></td>
		<tr><td align="left" class="copy_2"><?=get_message(109)?></td></tr>
		<tr><td align="left" class="copy_2">
				<input type="radio" name="opt_in" value="1">No, it's ok to send occasional announcements.<br>
				<input type="radio" name="opt_in" value="0">Yes, please remove me from your list.</td>
		</tr>
		<tr><td align="center" class="copy_2"><input type="submit" name="btn_submit" value="Submit" class="button"></td></tr>
		<tr><td align="center"><a href="/">Not Sure? Visit our Web Site and Find Out More!</a></td></tr>
		</form>
		<?	} ?>
		<table>
