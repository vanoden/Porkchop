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
			$overwrite = $_REQUEST['overwrite'];

		    // Configurations Selected
		    if (in_array('Configurations', $_REQUEST['content'])) {
				if ($jsonData['configurations']) {
					foreach ($jsonData['configurations'] as $configuration) {
						$siteConfiguration = new \Site\Configuration();
						$siteConfiguration->get( $configuration['key'] );
						if ($overwrite && isset($siteConfiguration->id)) $siteConfiguration->delete();					
						$siteConfiguration->add( 
							array( 'key' => $configuration['key'], 
							'value' => $configuration['value'] )
						);
					}
				}
		    }    

    		// Navigation Selected
		    if (in_array('Navigation', $_REQUEST['content'])) {
				if ($jsonData['navigation']) {
					foreach ($jsonData['navigation'] as $navigation) {
						
						$navigationMenu = new \Navigation\Menu();
						$navigationMenu->getByCode( $navigation['menuItem']['code'] );
						$navigationMenu->add( array( 'code' => $navigation['menuItem']['code'], 'title' => $navigation['menuItem']['title'] ));

						foreach ( $navigation['navigationItems'] as $navigationItem ) {
							$navigationMenuItem = new \Navigation\Item();
							$navigationMenuItem->getByTarget( $navigationItem['target'] );
							if ($overwrite && isset($navigationMenuItem->id)) $navigationMenuItem->delete();
							$navigationMenuItem->add( 
								array( 
									'menu_id' => $navigationMenu->id, 
									'title' => $navigationItem['title'], 
									'target' => $navigationItem['target'], 
									'view_order' => $navigationItem['view_order'], 
									'alt' => $navigationItem['alt'], 
									'description' => $navigationItem['description'], 
									'parent_id' => $navigationItem['parent_id'],
									'external' => $navigationItem['external'], 
									'ssl' => $navigationItem['ssl'] 
								)
							);
						}

						// delete parent menu item after no children left
						if ($overwrite && isset($navigationMenu->id)) $navigationMenu->delete();
					}
				}
		    }

    	    // Terms of Use Selected
		    if (in_array('Terms', $_REQUEST['content'])) {

				if ($jsonData['termsOfUse']) {
					foreach ($jsonData['termsOfUse'] as $term) {

						$termsOfUse = new \Site\TermsOfUse();
						$termsOfUse->getByCode( $term['termsOfUseItem']['code'] );
						$termsOfUse->add(
							array(
								'code' => $term['termsOfUseItem']['code'],
								'name' => $term['termsOfUseItem']['name'],
								'description' => $term['termsOfUseItem']['description']
							)
						);
						foreach ( $term['termsOfUseVersions'] as $termsOfUseVersion ) {
							$termsOfUseVersionItem = new \Site\TermsOfUseVersion();
							$termsOfUseVersionItem->get( $term['id'] );
							if ($overwrite && isset($termsOfUseVersionItem->id)) $termsOfUseVersionItem->delete();
							$termsOfUseVersionItem->add(
								array(
									'tou_id' => $termsOfUse->id,
									'status' => $termsOfUseVersion['status'],
									'content' => $termsOfUseVersion['content']
								)
							);
						}

						// delete parent menu item after no children left
						if ($overwrite && isset($termsOfUse->id)) $termsOfUse->delete();
					}
				}
		    }

            // Marketing content Selected
		    if (in_array('Marketing', $_REQUEST['content'])) {

				if ($jsonData['marketingContent']) {

					// add pages
					foreach ($jsonData['marketingContent'] as $page) {

						$marketingPage = new \Site\Page();
						$marketingPage->getPage( $page['page']['module'], $page['page']['view'], $page['page']['index'] );
						$marketingPage->addByParameters(
							array(
								'module' => $page['page']['module'],
								'view' => $page['page']['view'],
								'index' => $page['page']['index'],
								'style' => $page['page']['style'],
								'auth_required' => $page['page']['auth_required'],
								'sitemap' => $page['page']['sitemap']
							)
						);
						
						// add page meta data
						foreach( $page['pageMetaData'] as $pageMetaData ) {
							$marketingPageMetaData = new \Site\Page\MetaData();
							$marketingPageMetaData->getWithKey( $pageMetaData['key'] );
							if ($overwrite && isset($marketingPageMetaData->id)) $marketingPageMetaData->delete();

							$marketingPageMetaData->addByParameters(
								array(
									'page_id' => $marketingPage->id,
									'key' => $pageMetaData['key'],
									'value' => $pageMetaData['value']
								)
							);
						}

						// add page content block(s)
						foreach ( $page['contentBlocks'] as $contentBlock ) {
							
							$marketingContentBlock = new \Content\Message();
							$marketingContentBlock->get( $contentBlock['target'] );
							if ($overwrite && isset($marketingContentBlock->id)) $marketingContentBlock->delete();

							$marketingContentBlock->add(
								array(
									'company_id' => $contentBlock['company_id'],
									'target' => $contentBlock['target'],
									'view_order' => $contentBlock['view_order'],
									'active' => $contentBlock['active'],
									'deleted' => $contentBlock['deleted'],
									'title' => $contentBlock['title'],
									'menu_id' => $contentBlock['menu_id'],
									'name' => $contentBlock['name'],
									'content' => $contentBlock['content'],
									'cached' => $contentBlock['cached']
								)
							);
						}

						// delete parent page item after no children left
						if ($overwrite && isset($marketingPage->id)) $marketingPage->delete();
					}
		    }
		}
	}
	}