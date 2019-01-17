<table class="body" cellpadding="0" cellspacing="0">
<?	if (! in_array('monitor admin',$GLOBALS['_SESSION_']->customer->roles))
	{
		print "<span class=\"form_error\">You are not authorized for this view!</span>";
	}
	else
	{
?>
<tr><td class="value"><a href="/_monitor/admin_assets">Manage Monitors</td></tr>
<tr><td class="value"><a href="/_monitor/admin_credits">Manage Credits</td></tr>
<?	} ?>
</table>
