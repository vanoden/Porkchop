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
        if (!empty($name) && isset($_REQUEST['content']) && in_array($name, $_REQUEST['content'])) {
            return "checked=checked";
        }
	}
	
	// content requested to export form submitted
	$siteData = new \Site\Data();
	if (isset($_REQUEST['content']) && !empty($_REQUEST['content'])) {
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
			$page->addError("Invalid Token");
		} else {
    
            // Marketing content
		    if (in_array('Marketing', $_REQUEST['content'])) {    
                $pagelist = new \Site\PageList();
                $pages = $pagelist->find(array('module'=>'content'));
                $siteData->marketingContent = $pages;
                
                // get all the HTML content blocks for pages
                $contentBlocks = array();
                foreach ($pages as $page) {
                	$message = new \Content\Message();
                	$message->get($page->index);
                	$message->name = $page->index;
                	$message->target = $page->index;
                	$contentBlocks[] = $message;
                }
                $siteData->contentBlocks = $contentBlocks;
		    }
		    
		    // Navigation 
		    if (in_array('Navigation', $_REQUEST['content'])) {    
                $menuList = new \Navigation\MenuList();
                $menus = $menuList->find();
                if ($menuList->error()) $page->addError($menuList->error());
                $siteData->navigation = $menus;
                
                // get sub menu items for JSON data
                $navigationItemList = new \Navigation\ItemList();
                foreach ($menus as $menu) {
                    $navigationItems = $navigationItemList->find(array('menu_id'=>$menu->id));
                    $siteData->navigationItems = $navigationItems;
                }
		    }
	
		    // Configurations 
		    if (in_array('Configurations', $_REQUEST['content'])) {
                $siteConfigurations = new \Site\ConfigurationList();
                $configurations = $siteConfigurations->find();
                if ($siteConfigurations->error()) $page->addError($siteConfigurations->error());
                $siteData->configurations = $configurations; 
		    }
		    
		    // Terms of Use 
		    if (in_array('Terms', $_REQUEST['content'])) {
                $termsOfUseList = new \Site\TermsOfUseList();
                $termsOfUse = $termsOfUseList->find();
                if ($termsOfUseList->error()) $page->addError($termsOfUseList->error());
                $siteData->termsOfUse = $termsOfUse;
                
                // get sub terms of use items for JSON data
                $termsOfUseVersionList = new \Site\TermsOfUseVersionList();
                foreach ($termsOfUse as $termOfUseVersion) {
                    $termsOfUseVersionListItems = $termsOfUseVersionList->find(array('tou_id'=>$termOfUseVersion->id));
                    $siteData->termsOfUseItems = $termsOfUseVersionListItems;
                }
		    }
		}
	}
