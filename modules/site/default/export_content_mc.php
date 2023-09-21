<?php
	$site = new \Site();
    $page = $site->page();
	$page->requirePrivilege('configure site');
	$page->addBreadCrumb("Export Settings","/_site/export_content");
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

		    // Configurations Selected
		    if (in_array('Configurations', $_REQUEST['content'])) {
                $siteConfigurations = new \Site\ConfigurationList();
                $configurations = $siteConfigurations->find();
                if ($siteConfigurations->error()) $page->addError($siteConfigurations->error());
                $siteData->setConfigurations($configurations); 
		    }    

    		// Navigation Selected
		    if (in_array('Navigation', $_REQUEST['content'])) {
                $menuList = new \Navigation\MenuList();
                $menus = $menuList->find();
                if ($menuList->error()) $page->addError($menuList->error());
                
                // get sub menu items for JSON data
                $navigationItemList = new \Navigation\ItemList();
                foreach ($menus as $menu) $navigationItems[] = $navigationItemList->find(array('menu_id'=>$menu->id));
                $siteData->setNavigationItems(array('menus' => $menus, 'navigationItems' => $navigationItems));
		    }

    	    // Terms of Use Selected
		    if (in_array('Terms', $_REQUEST['content'])) {
                $termsOfUseList = new \Site\TermsOfUseList();
                $termsOfUse = $termsOfUseList->find();
                if ($termsOfUseList->error()) $page->addError($termsOfUseList->error());
                
                // get sub terms of use items for JSON data
                $termsOfUseVersionList = new \Site\TermsOfUseVersionList();
                foreach ($termsOfUse as $termOfUseVersion) 
                    $termsOfUseVersionListItems = $termsOfUseVersionList->find(array('tou_id'=>$termOfUseVersion->tou_id));
                $siteData->setTermsOfUseItems(array('termsOfUse' => $termsOfUse, 'termsOfUseVersions' => $termsOfUseVersionListItems));
		    }

            // Marketing content Selected
		    if (in_array('Marketing', $_REQUEST['content'])) {

                // get pages
                $pagelist = new \Site\PageList();
                $pages = $pagelist->find(array('module'=>'content', 'view'=>'index'));
                
                // get page metadata
                $pageMetaDataList = new \Site\Page\MetadataList();
                $pageMetaData = $pageMetaDataList->find();

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
