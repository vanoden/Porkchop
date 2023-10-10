<?php
$site = new \Site();
$page = $site->page();
$page->requirePrivilege('configure site');
$page->addBreadCrumb("Import Settings", "/_site/import_content");
$page->instructions = "Select the settings you would like to export to a JSON formatted file.";

/** 
 * is checked check for checkboxes
 *
 * @param $name
 */
function isChecked($name = "") {
	if (!empty($name) && isset($_REQUEST['content']) && in_array($name, $_REQUEST['content']))
		return "checked=checked";
}

// content requested to export form submitted
$siteData = new \Site\Data();
if (isset($_REQUEST['content']) && !empty($_REQUEST['content'])) {
	if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
		$page->addError("Invalid Token");
	} else {

		// get JSON data and overwrite flag
		$jsonData = json_decode($_REQUEST['jsonData'], true);
		$overwrite = ($_REQUEST['overwrite'] == 'true') ? true : false;

		// Configurations Selected
		if (in_array('Configurations', $_REQUEST['content'])) {
			if ($jsonData['configurations']) {
				foreach ($jsonData['configurations'] as $configuration) {

					$siteConfiguration = new \Site\Configuration();
					$siteConfigurationValue = $siteConfiguration->getByKey($configuration['key']);

					// if key exists, update (only if overwrite), else add
					if (!empty($siteConfigurationValue)) {
						if ($overwrite) {
							$isUpdated = $siteConfiguration->update(array('value' => $configuration['value']));
							if (!$isUpdated) {
								$page->addError("<strong>Error Updating Site Configuration: </strong>" . $siteConfiguration->getError() . "<br/><strong> Key: </strong>" . $configuration['key'] . " <strong>Name: </strong> " . $configuration['value'] . "<br/>");
							} else {
								$page->appendSuccess("Updated Site Configuration: " . $configuration['key'] . " - " . $configuration['value']);
							}
						} else {
							$page->appendSuccess("Skipped Site Configuration: " . $configuration['key'] . " - " . $configuration['value']);
						}
					} else {
						$addedSiteConfiguration = $siteConfiguration->add(array('key' => $configuration['key'], 'value' => $configuration['value']));
						if (!$addedSiteConfiguration) {
							$page->addError("<strong>Error Adding Site Configuration: </strong>" . $siteConfiguration->getError() . "<br/><strong> Key: </strong>" . $configuration['key'] . " <strong>Name: </strong> " . $configuration['value'] . "<br/>");
						} else {
							$page->appendSuccess("Added Site Configuration: " . $configuration['key'] . " - " . $configuration['value']);
						}
					}
				}
			}
		}

		// Navigation Selected
		if (in_array('Navigation', $_REQUEST['content'])) {
			if ($jsonData['navigation']) {
				foreach ($jsonData['navigation'] as $navigation) {
					$navigationMenu = new \Navigation\Menu();
					$navigationMenu->getByCode($navigation['menuItem']['code']);

					// if key exists, update (only if overwrite), else add
					if (isset($navigationMenu->id) && !empty($navigationMenu->id)) {
						if ($overwrite) {
							$isUpdated = $navigationMenu->update(array('title' => $navigation['menuItem']['title']));
							if (!$isUpdated) {
								$page->addError("<strong>Error Updating Navigation Menu: </strong>" . $navigationMenu->getError() . "<br/><strong> Code: </strong>" . $navigation['menuItem']['code'] . " <strong>Title: </strong> " . $navigation['menuItem']['title'] . "<br/>");
							} else {
								$page->appendSuccess("Updated Navigation Menu: " . $navigation['menuItem']['code'] . " - " . $navigation['menuItem']['title']);
							}
						} else {
							$page->appendSuccess("Skipped Navigation Menu: " . $navigation['menuItem']['code'] . " - " . $navigation['menuItem']['title']);
						}
					} else {
						$addedNavigationMenu = $navigationMenu->add(array('code' => $navigation['menuItem']['code'], 'title' => $navigation['menuItem']['title']));
						if (!$addedNavigationMenu) {
							$page->addError("<strong>Error Adding Navigation Menu: </strong>" . $navigationMenu->getError() . "<br/><strong> Code: </strong>" . $navigation['menuItem']['code'] . " <strong>Title: </strong> " . $navigation['menuItem']['title'] . "<br/>");
						} else {
							$page->appendSuccess("Added Navigation Menu: " . $navigation['menuItem']['code'] . " - " . $navigation['menuItem']['title']);
						}
					}

					// create/update navigation menu items
					foreach ($navigation['navigationItems'] as $navigationItem) {
						$navigationMenuItem = new \Navigation\Item();
						$navigationMenuItem->getByParentIdViewOrderMenuId($navigationItem['parent_id'], $navigationItem['view_order'], $navigationMenu->id);
						$navigationItemData = array(
							'menu_id' => $navigationMenu->id,
							'title' => $navigationItem['title'],
							'target' => $navigationItem['target'],
							'view_order' => $navigationItem['view_order'],
							'alt' => $navigationItem['alt'],
							'description' => $navigationItem['description'],
							'parent_id' => $navigationItem['parent_id'],
							'external' => $navigationItem['external'],
							'ssl' => $navigationItem['ssl']
						);
						if (isset($navigationMenuItem->id) && !empty($navigationMenuItem->id)) {
							if ($overwrite) {
								$isUpdated = $navigationMenuItem->update($navigationItemData);
								if (!$isUpdated) {
									$page->addError("<strong>Error Updating Navigation Menu Item: </strong>" . $navigationMenuItem->getError() . "<br/><strong> Title: </strong>" . $navigationItem['title'] . " <strong>URL: </strong> " . $navigationItem['url'] . "<br/>");
								} else {
									$page->appendSuccess("Updated Navigation Menu Item: " . $navigationItem['title'] . " - " . $navigationItem['target']);
								}
							} else {
								$page->appendSuccess("Skipped Navigation Menu Item: " . $navigationItem['title'] . " - " . $navigationItem['target']);
							}
						} else {
							$addedNavigationMenuItem = $navigationMenuItem->add($navigationItemData);
							if (!$addedNavigationMenuItem) {
								$page->addError("<strong>Error Adding Navigation Menu Item: </strong>" . $navigationMenuItem->getError() . "<br/><strong> Title: </strong>" . $navigationItem['title'] . " <strong>URL: </strong> " . $navigationItem['target'] . "<br/>");
							} else {
								$page->appendSuccess("Added Navigation Menu Item: " . $navigationItem['title'] . " - " . $navigationItem['target']);
							}
						}
					}
				}
			}
		}

		// Terms of Use Selected
		if (in_array('Terms', $_REQUEST['content'])) {

			if ($jsonData['termsOfUse']) {
				foreach ($jsonData['termsOfUse'] as $term) {

					$termsOfUse = new \Site\TermsOfUse();
					$termsOfUse->getByCode($term['termsOfUseItem']['code']);
					$termsOfUseItemData = array(
						'code' => $term['termsOfUseItem']['code'],
						'name' => $term['termsOfUseItem']['name'],
						'description' => $term['termsOfUseItem']['description']
					);

					if (isset($termsOfUse->id) && !empty($termsOfUse->id)) {
						if ($overwrite) {
							$isUpdated = $termsOfUse->update($termsOfUseItemData);
							if (!$isUpdated) {
								$page->addError("<strong>Error Updating Terms of Use: </strong>" . $termsOfUse->getError() . "<br/><strong> Code: </strong>" . $term['termsOfUseItem']['code'] . " <strong>Name: </strong> " . $term['termsOfUseItem']['name'] . "<br/>");
							} else {
								$page->appendSuccess("Updated Terms of Use: " . $term['termsOfUseItem']['code'] . " - " . $term['termsOfUseItem']['name']);
							}
						} else {
							$page->appendSuccess("Skipped Terms of Use: " . $term['termsOfUseItem']['code'] . " - " . $term['termsOfUseItem']['name']);
						}
					} else {
						$addedTermOfUse = $termsOfUse->add($termsOfUseItemData);
						if (!$addedTermOfUse) {
							$page->addError("<strong>Error Adding Terms of Use: </strong>" . $termsOfUse->getError() . "<br/><strong> Code: </strong>" . $term['termsOfUseItem']['code'] . " <strong>Name: </strong> " . $term['termsOfUseItem']['name'] . "<br/>");
						} else {
							$page->appendSuccess("Added Terms of Use: " . $term['termsOfUseItem']['code'] . " - " . $term['termsOfUseItem']['name']);
						}
					}

					// create/update terms of use versions
					foreach ($term['termsOfUseVersions'] as $termsOfUseVersions) {
						foreach ($termsOfUseVersions as $termsOfUseVersion) {

							$termsOfUseVersionItem = new \Site\TermsOfUseVersion();
							$termsOfUseVersionItem->getByTermsOfUseIdVersionNumber($termsOfUse->id, $termsOfUseVersion['version_number']);
							$termsOfUseVersionData = array(
								'tou_id' => $termsOfUse->id,
								'version_number' => $termsOfUseVersion['version_number'],
								'status' => $termsOfUseVersion['status'],
								'content' => $termsOfUseVersion['content']
							);

							if (isset($termsOfUseVersionItem->id) && !empty($termsOfUseVersionItem->id)) {
								if ($overwrite) {
									$isUpdated = $termsOfUseVersionItem->update($termsOfUseVersionData);
									if (!$isUpdated) {
										$page->addError("<strong>Error Updating Terms of Use Version: </strong>" . $termsOfUseVersionItem->getError() . "<br/><strong> Version Number: </strong>" . $termsOfUseVersion['version_number'] . " <strong>Status: </strong> " . $termsOfUseVersion['status'] . "<br/>");
									} else {
										$page->appendSuccess("Updated Terms of Use Version: " . $termsOfUseVersion['version_number'] . " - " . $termsOfUseVersion['status']);
									}
								} else {
									$page->appendSuccess("Skipped Terms of Use Version: " . $termsOfUseVersion['version_number'] . " - " . $termsOfUseVersion['status']);
								}
							} else {
								$addedTermOfUseVersion = $termsOfUseVersionItem->add($termsOfUseVersionData);
								if (!$addedTermOfUseVersion) {
									$page->addError("<strong>Error Adding Terms of Use Version: </strong>" . $termsOfUseVersionItem->getError() . "<br/><strong> Version Number: </strong>" . $termsOfUseVersion['version_number'] . " <strong>Status: </strong> " . $termsOfUseVersion['status'] . "<br/>");
								} else {
									$page->appendSuccess("Added Terms of Use Version: " . $termsOfUseVersion['version_number'] . " - " . $termsOfUseVersion['status']);
								}
							}
						}
					}
				}
			}
		}

		// Marketing content Selected
		if (in_array('Marketing', $_REQUEST['content'])) {
			if ($jsonData['marketingContent']) {
				foreach ($jsonData['marketingContent'] as $currentPageImport) {
					foreach ($currentPageImport as $marketingCurrentPageKey => $marketingCurrentPage) {

						// page_pages upsert
						if ($marketingCurrentPageKey == 'page') {
							if (isset($marketingCurrentPage['module']) && isset($marketingCurrentPage['view']) && isset($marketingCurrentPage['index']) && !empty($marketingCurrentPage['module']) && !empty($marketingCurrentPage['view']) && !empty($marketingCurrentPage['index'])) {
								$marketingPage = new \Site\Page();
								$marketingPage->getPage($marketingCurrentPage['module'], $marketingCurrentPage['view'], $marketingCurrentPage['index']);
								$marketingPageData = array(
									'module' => $marketingCurrentPage['module'],
									'view' => $marketingCurrentPage['view'],
									'index' => $marketingCurrentPage['index'],
									'style' => $marketingCurrentPage['style'],
									'auth_required' => $marketingCurrentPage['auth_required'],
									'sitemap' => $marketingCurrentPage['sitemap']
								);

								if (isset($marketingPage->id) && !empty($marketingPage->id)) {
									if ($overwrite) {
										$isUpdated = $marketingPage->update($marketingPageData);
										if (!$isUpdated) {
											$page->addError("<strong>Error Updating Marketing Page: </strong>" . $marketingPage->getError() . "<br/><strong> Module: </strong>" . $marketingCurrentPage['module'] . " <strong>View: </strong> " . $marketingCurrentPage['view'] . "<br/>");
										} else {
											$page->appendSuccess("Updated Marketing Page: " . $marketingCurrentPage['module'] . " - " . $marketingCurrentPage['view']);
										}
									} else {
										$page->appendSuccess("Skipped Marketing Page: " . $marketingCurrentPage['module'] . " - " . $marketingCurrentPage['view']);
									}
								} else {
									$addedMarketingPage = $marketingPage->add($marketingPageData);
									if (!$addedMarketingPage) {
										$page->addError("<strong>Error Adding Marketing Page: </strong>" . $marketingPage->getError() . "<br/><strong> Module: </strong>" . $marketingCurrentPage['module'] . " <strong>View: </strong> " . $marketingCurrentPage['view'] . "<br/>");
									} else {
										$page->appendSuccess("Added Marketing Page: " . $marketingCurrentPage['module'] . " - " . $marketingCurrentPage['view']);
									}
								}
							}
						}

						// page_metadata upsert
						if ($marketingCurrentPageKey == 'pageMetaData') {
							foreach ($marketingCurrentPage as $pageMetaData) {
								$marketingPageMetaData = new \Site\Page\MetaData();
								$marketingPageMetaData->getByPageIdKey($marketingPage->id, $pageMetaData['key']);
								$marketingPageMetaDataData = array(
									'page_id' => $marketingPage->id,
									'key' => $pageMetaData['key'],
									'value' => $pageMetaData['value']
								);

								if (isset($marketingPageMetaData->id) && !empty($marketingPageMetaData->id)) {
									if ($overwrite) {
										$isUpdated = $marketingPageMetaData->update($marketingPageMetaDataData);
										if (!$isUpdated) {
											$page->addError("<strong>Error Updating Marketing Page Meta Data: </strong>" . $marketingPageMetaData->getError() . "<br/><strong> Key: </strong>" . $pageMetaData['key'] . " <strong>Value: </strong> " . $pageMetaData['value'] . "<br/>");
										} else {
											$page->appendSuccess("Updated Marketing Page Meta Data: " . $pageMetaData['key'] . " - " . $pageMetaData['value']);
										}
									} else {
										$page->appendSuccess("Skipped Marketing Page Meta Data: " . $pageMetaData['key'] . " - " . $pageMetaData['value']);
									}
								} else {
									$addedMarketingPageMetaData = $marketingPageMetaData->addByParameters($marketingPageMetaDataData);
									if (!$addedMarketingPageMetaData) {
										$page->addError("<strong>Error Adding Marketing Page Meta Data: </strong>" . $marketingPageMetaData->getError() . "<br/><strong> Key: </strong>" . $pageMetaData['key'] . " <strong>Value: </strong> " . $pageMetaData['value'] . "<br/>");
									} else {
										$page->appendSuccess("Added Marketing Page Meta Data: " . $pageMetaData['key'] . " - " . $pageMetaData['value']);
									}
								}
							}
						}

						// content_messages upsert
						if ($marketingCurrentPageKey == 'contentBlocks') {

							foreach ($marketingCurrentPage as $contentBlock) {

								if (isset($contentBlock['company_id']) && isset($contentBlock['target']) && isset($contentBlock['deleted'])) {

									$marketingContentBlock = new \Content\Message();
									$marketingContentBlock->getByCompanyIdTargetDeleted($contentBlock['company_id'], $contentBlock['target'], $contentBlock['deleted']);

									$contentBlockData = array(
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
									);

									if ($marketingContentBlock->id) {
										if ($overwrite) {
											$isUpdated = $marketingContentBlock->update($contentBlockData);
											if (!$isUpdated) {
												$page->addError("<strong>Error Updating Marketing Content Block: </strong>" . $marketingContentBlock->getError() . "<br/><strong> Title: </strong>" . $contentBlock['title'] . " <strong>Content: </strong> " . $contentBlock['content'] . "<br/>");
											} else {
												$page->appendSuccess("Updated Marketing Content Block: " . $contentBlock['title'] . " - " . $contentBlock['content']);
											}
										} else {
											$page->appendSuccess("Skipped Marketing Content Block: " . $contentBlock['title'] . " - " . $contentBlock['content']);
										}
									} else {
										$addedMarketingContentBlock = $marketingContentBlock->add($contentBlockData);
										if (!$addedMarketingContentBlock) {
											$page->addError("<strong>Error Adding Marketing Content Block: </strong>" . $marketingContentBlock->getError() . "<br/><strong> Title: </strong>" . $contentBlock['title'] . " <strong>Content: </strong> " . $contentBlock['content'] . "<br/>");
										} else {
											$page->appendSuccess("Added Marketing Content Block: " . $contentBlock['title'] . " - " . $contentBlock['content']);
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
