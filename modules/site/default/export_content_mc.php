<?php
	$site = new \Site();
    $page = $site->page();
	$page->requirePrivilege('configure site');
	$page->addBreadCrumb("Export Settings","/_site/export_content");
	$page->instructions = "Select the settings you would like to export to a JSON formatted file.";
	
	$request = new \HTTP\Request();
	$can_proceed = true;
	
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
	$content = $_REQUEST['content'] ?? null;
	if (is_array($content) && !empty($content)) {
		$csrfToken = $_REQUEST['csrfToken'] ?? null;
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($csrfToken)) {
			$page->addError("Invalid Token");
			$can_proceed = false;
		} else {
		    // Configurations Selected
		    if (in_array('Configurations', $content)) {
                $siteConfigurations = new \Site\ConfigurationList();
                $configurations = $siteConfigurations->find();
                if ($siteConfigurations->error()) {
                	$page->addError($siteConfigurations->error());
                	$can_proceed = false;
                }
                $siteData->setConfigurations($configurations); 
		    }    

    		// Navigation Selected
		    if (in_array('Navigation', $content)) {
                $menuList = new \Site\Navigation\MenuList();
                $menus = $menuList->find();
                if ($menuList->error()) {
                	$page->addError($menuList->error());
                	$can_proceed = false;
                }
                
                // get sub menu items for JSON data
                $navigationItemList = new \Site\Navigation\ItemList();
                foreach ($menus as $menu) $navigationItems[] = $navigationItemList->find(array('menu_id'=>$menu->id));
                $siteData->setNavigationItems(array('menus' => $menus, 'navigationItems' => $navigationItems));
		    }

    	    // Terms of Use Selected
		    if (in_array('Terms', $content)) {
                $termsOfUseList = new \Site\TermsOfUseList();
                $termsOfUse = $termsOfUseList->find();
                if ($termsOfUseList->error()) {
                	$page->addError($termsOfUseList->error());
                	$can_proceed = false;
                }
                
                // get sub terms of use items for JSON data
                $termsOfUseVersionList = new \Site\TermsOfUseVersionList();
                $termsOfUseVersionListItems = array();
                foreach ($termsOfUse as $termOfUseVersion) 
                    $termsOfUseVersionListItems["tou_id_".$termOfUseVersion->id] = $termsOfUseVersionList->find(array('tou_id'=>$termOfUseVersion->id));
                $siteData->setTermsOfUseItems(array('termsOfUse' => $termsOfUse, 'termsOfUseVersions' => $termsOfUseVersionListItems));
		    }

            // Marketing content Selected
		    if (in_array('Marketing', $content)) {
                // get pages
                $pagelist = new \Site\PageList();
                $pages = $pagelist->find(array('module'=>'content', 'view'=>'index'));
                
                // NOTE: Page metadata functionality appears to be disabled or missing
                // The class \Site\Page\MetadataList or \Site\Page\MetaDataList does not exist
                $pageMetaData = array();

                // get all the HTML content blocks for pages
                $contentBlocks = array();
                foreach ($pages as $page) {
                	$message = new \Content\Message();
                	$message->get($page->index);
                	$contentBlocks[] = $message;
                } 

                $siteData->setMarketingContent(array('pages' => $pages, 'pageMetaData' => $pageMetaData, 'contentBlocks' => $contentBlocks));
		    }
		}
	}
