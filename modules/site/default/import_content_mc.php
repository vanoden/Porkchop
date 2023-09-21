<?php
	$site = new \Site();
    $page = $site->page();
	$page->requirePrivilege('configure site');
	$page->addBreadCrumb("Import Settings","/_site/import_content");
	$page->instructions = "Select the settings you would like to export to a JSON formatted file.";
	
	/** 
	 * is checked check for checkboxes
	 *
	 * @param $name
	 */
	function isChecked ($name="") {
        if (!empty($name) && isset($_REQUEST['content']) && in_array($name, $_REQUEST['content'])) return "checked=checked";
	}
	
	// content requested to export form submitted
	$siteData = new \Site\Data();
	if (isset($_REQUEST['content']) && !empty($_REQUEST['content'])) {
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
			$page->addError("Invalid Token");
		} else {
			
			$jsonData = json_decode($_REQUEST['jsonData'], true);

		    // Configurations Selected
		    if (in_array('Configurations', $_REQUEST['content'])) {
				if ($jsonData['configurations']) {
					foreach ($jsonData['configurations'] as $configuration) {
						print_r($configuration);

					/**
						Array
						(
							[key] => engineering_attachments
							[value] => 6233820da65ef
							[id] => 0
							[_cached] => 
						)
					 */
					}
				}

		    }    

    		// Navigation Selected
		    if (in_array('Navigation', $_REQUEST['content'])) {
				if ($jsonData['navigation']) {
					foreach ($jsonData['navigation'] as $navigation) {
						print_r($navigation);

							/**
								Array
								(
									[menuItem] => Array
										(
											[code] => main
											[title] => Chris Navigation
											[id] => 3
											[_cached] => 
										)

									[navigationItems] => Array
										(
											[0] => Array
												(
													[menu_id] => 3
													[title] => test
													[target] => /_content/kevin3
													[view_order] => 0
													[alt] => 
													[description] => 
													[parent_id] => 0
													[external] => 
													[ssl] => 
													[id] => 43
													[_cached] => 
												)

											[1] => Array
												(
													[menu_id] => 3
													[title] => child link
													[target] => /_content/kevin3
													[view_order] => 1
													[alt] => child
													[description] => asdf
													[parent_id] => 43
													[external] => 
													[ssl] => 
													[id] => 44
													[_cached] => 
												)
										)
								) 
							*/


					}
				}
		    }

    	    // Terms of Use Selected
		    if (in_array('Terms', $_REQUEST['content'])) {

				if ($jsonData['termsOfUse']) {
					foreach ($jsonData['termsOfUse'] as $term) {
						print_r($term);

							/**
							 
							Array
							(
								[termsOfUseItem] => Array
									(
										[code] => 0987654321fedcba
										[name] => Term of Use 2
										[description] => This is the second term of use.
										[id] => 2
										[_cached] => 0
									)

								[termsOfUseVersions] => Array
									(
										[0] => Array
											(
												[status] => NEW
												[content] => It has survived not only five centuries, but also the leap into electronic typesetting,
												[tou_id] => 2
												[id] => 3
												[_cached] => 1
											)

										[1] => Array
											(
												[status] => NEW
												[content] => <p>It has survived not only five centuries, but also the leap into electronic typesetting, treste</p>
												[tou_id] => 2
												[id] => 6
												[_cached] => 1
											)

										[2] => Array
											(
												[status] => NEW
												[content] => <p>dfdsfdsa</p>
												[tou_id] => 2
												[id] => 7
												[_cached] => 1
											)

										[3] => Array
											(
												[status] => RETRACTED
												[content] => remaining essentially unchanged.
												[tou_id] => 2
												[id] => 4
												[_cached] => 1
											)

									)

							)


							*/


					}
				}

		    }

            // Marketing content Selected
		    if (in_array('Marketing', $_REQUEST['content'])) {

		    }
		}
	}
