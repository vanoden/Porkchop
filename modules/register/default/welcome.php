	<?PHP
		# Get Customer Info
		$customer_info = get_customer($customer_id);

		# Get Text If Not Nav
		if ($r7_session["page_config"]->welcome_nav < 0)
		{
	?>
	<table class="copy_2">
	<tr><td class="copy_2"><img src="/images/clear.gif" width="175" height="10"></td></tr>
	<tr><td class="copy_2"><?=get_message(preg_replace("/\-/","",$r7_session["page_config"]->welcome_nav))?></td></tr>
	</table>
	<?PHP
		}
		else
		{
	?>
	<table class="copy_2" cellpadding="0" cellspacing="0" border="0">
	<tr><td class="copy_2" colspan="2"><?=get_message(106)?></td></tr>
	<tr><td><img src="/images/clear.gif" width="80" height="10"></td>
		<td><img src="/images/clear.gif" width="400" height="10"></td>
	</tr>
	<?PHP
			# Get Buttons From Database
			$buttons = get_buttons($customer_id,$r7_session["page_config"]->welcome_nav);

			# Loop Through and Display Buttons
			while ($button_info = mysql_fetch_array($buttons))
			{
                $button_info["target"] = stripslashes($button_info["target"]);
				print "<!-- ".$button_info["target"]." -->\n";

                # Substitutions
                $button_info["target"] = preg_replace("/\<customer\.custom_1\>/",$customer_info["custom_1"],$button_info["target"]);

				# Handle External Links
				if ($button_info["external"])
				{
					$nav_bar_target = $button_info["target"];
				}
				elseif ($button_info["ssl"])
				{
					$nav_bar_target = "https://www.".$domain."/".$button_info["target"];
				}
				else
				{
					$nav_bar_target = "/".$button_info["target"];
				}
	?>
	<tr><td class="heading_2" align="left" nowrap>&nbsp;<a href="<?=$nav_bar_target?>" class="heading_2"><?=stripslashes($button_info["title"])?></a>&nbsp</td>
		<td class="copy_2" align="left"><?=stripslashes($button_info["alt"])?></td>
    </tr>
	<tr><td colspan="2"><img src="/images/clear.gif" width="1" height="15"></td></tr>
	<?PHP
			}
	?>
	</table>
	<?PHP
		}
	?>
