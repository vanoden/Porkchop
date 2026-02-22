<?php
	// Account / "My Account" menu (user dropdown). Populated by core/upgrade.php via populateMenus().
	$menus["myaccount"] = array(
		"title"	=> "My Account",
		"items"	=> array(
			array(
				"title"		=> "View Messages",
				"target"	=> "/_site/messages",
				"alt"		=> "View Your Messages",
				"view_order"	=> 5,
				"description"	=> "View your messages"
			),
			array(
				"title"		=> "My Account",
				"target"	=> "/_register/account",
				"alt"		=> "Manage Your Account",
				"view_order"	=> 30,
				"description"	=> "View/edit your account, password and contact settings"
			),
			array(
				"title"		=> "Administration",
				"target"	=> "/_spectros/admin_home",
				"alt"		=> "Administration",
				"view_order"	=> 80,
				"description"	=> "Administration"
			),
			array(
				"title"		=> "Logout",
				"target"	=> "/_register/logout",
				"alt"		=> "Logout of the Portal",
				"view_order"	=> 100,
				"description"	=> "Logout of the Portal"
			)
		)
	);
