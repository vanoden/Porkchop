<?php
	# Clean Up Page Name
	$page = preg_replace("/[^\w\-\_\.]/","",$page);

	# Get Page ID From Name
	$page_id = get_named_page($page);

	# Get Page Content
	$page_info = get_site_page($page_id);

	$r7_session["page_name"] = $page_info["name"];
	$r7_session["page_custom_1"] = parse_content($page_info["custom_1"]);
	$r7_session["page_custom_2"] = parse_content($page_info["custom_2"]);
	$r7_session["page_custom_3"] = parse_content($page_info["custom_3"]);

	# Get Company Info
	$company_info = get_company($company_id);
