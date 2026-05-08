<?php
$site = new \Site();
$page = $site->page();
$page->requirePrivilege('configure site');
$page->addBreadCrumb("Import Settings", "/_site/import_content");
$page->instructions = "Select the settings you would like to import from a JSON formatted file.";

$request = new \HTTP\Request();
$can_proceed = true;

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
$content = $_REQUEST['content'] ?? null;
if (is_array($content) && !empty($content)) {
	$csrfToken = $_REQUEST['csrfToken'] ?? null;
	if (!$GLOBALS['_SESSION_']->verifyCSRFToken($csrfToken)) {
		$page->addError("Invalid Token");
		$can_proceed = false;
	} else {
		// get JSON data - always overwrite (destructive import)
		$jsonData = json_decode($_REQUEST['jsonData'] ?? '{}', true);


		// Navigation Selected
		if (in_array('Navigation', $content) && $can_proceed) {
			if (isset($jsonData['navigation']) && is_array($jsonData['navigation'])) {

				foreach ($jsonData['navigation'] as $menuKey => $menuData) {
					// Get menu info
					if (!isset($menuData['menuItem']) || !isset($menuData['menuItem']['code'])) {
						$page->addError("Invalid menu data for key: " . $menuKey);
						continue;
					}

					$menuCode = $menuData['menuItem']['code'];
					$menuTitle = $menuData['menuItem']['title'] ?? $menuCode;

					// Find or create menu by code
					$menu = new \Site\Navigation\Menu();
					if (!$menu->getByCode($menuCode)) {
						// Menu doesn't exist, create it
						$menu = new \Site\Navigation\Menu();
						$menu->add(array('code' => $menuCode, 'title' => $menuTitle));
						if ($menu->error()) {
							$page->addError("Error creating menu '{$menuCode}': " . $menu->error());
							continue;
						}
					} else {
						// Update menu title if provided
						if (!empty($menuTitle) && $menu->title != $menuTitle) {
							$menu->update(array('title' => $menuTitle));
							if ($menu->error()) {
								$page->addError("Error updating menu '{$menuCode}': " . $menu->error());
							}
						}
					}

					// Delete all existing navigation items for this menu
					$itemList = new \Site\Navigation\ItemList();
					$existingItems = $itemList->find(array('menu_id' => $menu->id));
					if ($itemList->error()) {
						$page->addError("Error fetching existing items for menu '{$menuCode}': " . $itemList->error());
						continue;
					}

					// Delete all items (delete children first to handle parent-child relationships)
					// Sort items so children (with parent_id > 0) are deleted before parents
					usort($existingItems, function ($a, $b) {
						// Items with higher parent_id (children) come first
						return ($b->parent_id ?? 0) - ($a->parent_id ?? 0);
					});

					foreach ($existingItems as $existingItem) {
						$existingItem->delete();
						if ($existingItem->error()) {
							$page->addError("Error deleting item '{$existingItem->title}': " . $existingItem->error());
						}
					}

					// Import navigation items
					if (isset($menuData['navigationItems']) && is_array($menuData['navigationItems'])) {
						// Create mapping from old IDs to new IDs
						$idMap = array(); // old_id => new_id

						// First pass: add all items with parent_id = 0 (root items)
						foreach ($menuData['navigationItems'] as $itemData) {
							$oldId = $itemData['id'] ?? null;
							$parentId = $itemData['parent_id'] ?? 0;

							// Skip items with parents in first pass
							if ($parentId > 0) {
								continue;
							}

							$item = new \Site\Navigation\Item();
							$parameters = array(
								'menu_id' => $menu->id,
								'title' => $itemData['title'] ?? '',
								'target' => $itemData['target'] ?? '',
								'alt' => $itemData['alt'] ?? '',
								'description' => $itemData['description'] ?? '',
								'view_order' => $itemData['view_order'] ?? 0,
								'parent_id' => 0,
								'external' => isset($itemData['external']) ? (bool)$itemData['external'] : false,
								'ssl' => isset($itemData['ssl']) ? (bool)$itemData['ssl'] : false,
								'required_role_id' => $itemData['required_role_id'] ?? null
							);

							if ($item->add($parameters)) {
								if ($oldId) {
									$idMap[$oldId] = $item->id;
								}
							} else {
								$page->addError("Error adding item '{$itemData['title']}': " . $item->error());
							}
						}

						// Subsequent passes: add items with parents
						// Build list of items that still need to be added
						$remainingItems = array();
						foreach ($menuData['navigationItems'] as $itemData) {
							$oldId = $itemData['id'] ?? null;
							$parentId = $itemData['parent_id'] ?? 0;
							// Only include items with parents that haven't been added yet
							if ($parentId > 0 && !isset($idMap[$oldId])) {
								$remainingItems[] = $itemData;
							}
						}

						$maxIterations = 100; // Prevent infinite loops
						$iteration = 0;
						while (count($remainingItems) > 0 && $iteration < $maxIterations) {
							$itemsAdded = 0;
							$newRemainingItems = array();

							foreach ($remainingItems as $itemData) {
								$oldId = $itemData['id'] ?? null;
								$parentId = $itemData['parent_id'] ?? 0;

								// Check if parent has been added
								if ($parentId > 0 && !isset($idMap[$parentId])) {
									// Parent not yet added, keep for next iteration
									$newRemainingItems[] = $itemData;
									continue;
								}

								$item = new \Site\Navigation\Item();
								$parameters = array(
									'menu_id' => $menu->id,
									'title' => $itemData['title'] ?? '',
									'target' => $itemData['target'] ?? '',
									'alt' => $itemData['alt'] ?? '',
									'description' => $itemData['description'] ?? '',
									'view_order' => $itemData['view_order'] ?? 0,
									'parent_id' => isset($idMap[$parentId]) ? $idMap[$parentId] : 0,
									'external' => isset($itemData['external']) ? (bool)$itemData['external'] : false,
									'ssl' => isset($itemData['ssl']) ? (bool)$itemData['ssl'] : false,
									'required_role_id' => $itemData['required_role_id'] ?? null
								);

								if ($item->add($parameters)) {
									if ($oldId) {
										$idMap[$oldId] = $item->id;
									}
									$itemsAdded++;
								} else {
									$page->addError("Error adding item '{$itemData['title']}': " . $item->error());
								}
							}

							if ($itemsAdded == 0) {
								// No progress made, break to prevent infinite loop
								foreach ($newRemainingItems as $itemData) {
									$page->addError("Could not add item '{$itemData['title']}': parent not found or circular reference");
								}
								break;
							}

							$iteration++;
							$remainingItems = $newRemainingItems;
						}

						$page->appendSuccess("Imported " . count($menuData['navigationItems']) . " navigation items for menu '{$menuCode}'");
					}
				}
			} else {
				$page->addError("No navigation data found in JSON");
			}
		}

		if (in_array('Terms', $content) && $can_proceed) {
			if (isset($jsonData['termsOfUse']) && is_array($jsonData['termsOfUse'])) {

				foreach ($jsonData['termsOfUse'] as $touKey => $touData) {
					// Get Terms of Use item info
					if (!isset($touData['termsOfUseItem']) || !isset($touData['termsOfUseItem']['code'])) {
						$page->addError("Invalid Terms of Use data for key: " . $touKey);
						continue;
					}

					$touCode = $touData['termsOfUseItem']['code'];
					$touName = $touData['termsOfUseItem']['name'] ?? '';
					$touDescription = $touData['termsOfUseItem']['description'] ?? '';

					// Find or create Terms of Use by code
					$tou = new \Site\TermsOfUse();
					if (!$tou->getByCode($touCode)) {
						// Terms of Use doesn't exist, create it
						$tou = new \Site\TermsOfUse();
						$tou->add(array(
							'code' => $touCode,
							'name' => $touName,
							'description' => $touDescription
						));
						if ($tou->error()) {
							$page->addError("Error creating Terms of Use '{$touCode}': " . $tou->error());
							continue;
						}
					} else {
						// Update Terms of Use name and description if provided
						$updateParams = array();
						if (!empty($touName) && $tou->name != $touName) {
							$updateParams['name'] = $touName;
						}
						if (!empty($touDescription) && $tou->description != $touDescription) {
							$updateParams['description'] = $touDescription;
						}
						if (!empty($updateParams)) {
							$tou->update($updateParams);
							if ($tou->error()) {
								$page->addError("Error updating Terms of Use '{$touCode}': " . $tou->error());
							}
						}
					}

					// Delete all existing versions and their related events/actions
					$versionList = new \Site\TermsOfUseVersionList();
					$existingVersions = $versionList->find(array('tou_id' => $tou->id));
					if ($versionList->error()) {
						$page->addError("Error fetching existing versions for Terms of Use '{$touCode}': " . $versionList->error());
						continue;
					}

					// Delete actions, events, and versions
					foreach ($existingVersions as $version) {
						// Delete actions for this version
						$actionList = new \Site\TermsOfUseActionList();
						$actions = $actionList->find(array('version_id' => $version->id));
						foreach ($actions as $action) {
							$action->delete();
							if ($action->error()) {
								$page->addError("Error deleting action for version {$version->id}: " . $action->error());
							}
						}

						// Delete events for this version
						$eventList = new \Site\TermsOfUseEventList();
						$events = $eventList->find(array('version_id' => $version->id));
						foreach ($events as $event) {
							$event->delete();
							if ($event->error()) {
								$page->addError("Error deleting event for version {$version->id}: " . $event->error());
							}
						}

						// Delete the version
						$version->delete();
						if ($version->error()) {
							$page->addError("Error deleting version {$version->id}: " . $version->error());
						}
					}

					// Import Terms of Use versions
					if (isset($touData['termsOfUseVersions']) && is_array($touData['termsOfUseVersions'])) {
						// Flatten the nested array structure (termsOfUseVersions can be an array of arrays)
						$versionsToImport = array();
						foreach ($touData['termsOfUseVersions'] as $versionGroup) {
							if (is_array($versionGroup)) {
								// Check if this is a version object (has 'status' or 'version_number' key) or an array of versions
								if (isset($versionGroup['status']) || isset($versionGroup['version_number'])) {
									// This is a version object itself
									$versionsToImport[] = $versionGroup;
								} else {
									// This is an array of version objects
									foreach ($versionGroup as $versionData) {
										if (is_array($versionData) && (isset($versionData['status']) || isset($versionData['version_number']))) {
											$versionsToImport[] = $versionData;
										}
									}
								}
							}
						}

						$versionsImported = 0;
						foreach ($versionsToImport as $versionData) {
							$version = new \Site\TermsOfUseVersion();
							$parameters = array(
								'tou_id' => $tou->id,
								'version_number' => $versionData['version_number'] ?? null,
								'status' => $versionData['status'] ?? 'NEW',
								'content' => $versionData['content'] ?? ''
							);

							if ($version->add($parameters)) {
								$versionsImported++;
							} else {
								$page->addError("Error adding version for Terms of Use '{$touCode}': " . $version->error());
							}
						}

						$page->appendSuccess("Imported {$versionsImported} version(s) for Terms of Use '{$touCode}'");
					}
				}
			} else {
				$page->addError("No termsOfUse data found in JSON");
			}
		}

		if (in_array('Marketing', $content) && $can_proceed) {
			if (isset($jsonData['marketingContent']) && is_array($jsonData['marketingContent'])) {
				foreach ($jsonData['marketingContent'] as $marketingKey => $marketingData) {
					// Get page info
					if (!isset($marketingData['page']) || !isset($marketingData['page']['index'])) {
						$page->addError("Invalid marketing content data for key: " . $marketingKey . " (missing page or page.index)");
						continue;
					}

					$pageIndex = $marketingData['page']['index'];
					$pageTitle = $marketingData['page']['title'] ?? '';
					$pageDescription = $marketingData['page']['description'] ?? '';
					$pageUrl = $marketingData['page']['url'] ?? '';
					$pageStatus = $marketingData['page']['status'] ?? '';
					$pageTemplate = $marketingData['page']['template'] ?? '';
					$pageParentId = $marketingData['page']['parent_id'] ?? null;

					// Find or create page by index
					$sitePage = new \Site\Page();
					if (!$sitePage->getPage('content', 'index', $pageIndex)) {
						// Page doesn't exist, create it
						$sitePage = new \Site\Page();
						if (!$sitePage->add('content', 'index', $pageIndex)) {
							$page->addError("Error creating page '{$pageIndex}': " . $sitePage->error());
							continue;
						}
					}

					// Delete all existing metadata for this page (destructive import)
					$sitePage->dropAllMetadata();
					if ($sitePage->error()) {
						$page->addError("Error deleting existing metadata for page '{$pageIndex}': " . $sitePage->error());
					}

					// Set page metadata from page object (title, description, url, status, template, parent_id)
					if (!empty($pageTitle)) {
						$sitePage->setMetadata('title', $pageTitle);
						if ($sitePage->error()) {
							$page->addError("Error setting title for page '{$pageIndex}': " . $sitePage->error());
						}
					}
					if (!empty($pageDescription)) {
						$sitePage->setMetadata('description', $pageDescription);
						if ($sitePage->error()) {
							$page->addError("Error setting description for page '{$pageIndex}': " . $sitePage->error());
						}
					}
					if (!empty($pageUrl)) {
						$sitePage->setMetadata('url', $pageUrl);
						if ($sitePage->error()) {
							$page->addError("Error setting url for page '{$pageIndex}': " . $sitePage->error());
						}
					}
					if (!empty($pageStatus)) {
						$sitePage->setMetadata('status', $pageStatus);
						if ($sitePage->error()) {
							$page->addError("Error setting status for page '{$pageIndex}': " . $sitePage->error());
						}
					}
					if (!empty($pageTemplate)) {
						$sitePage->setMetadata('template', $pageTemplate);
						if ($sitePage->error()) {
							$page->addError("Error setting template for page '{$pageIndex}': " . $sitePage->error());
						}
					}
					if ($pageParentId !== null) {
						$sitePage->setMetadata('parent_id', (string)$pageParentId);
						if ($sitePage->error()) {
							$page->addError("Error setting parent_id for page '{$pageIndex}': " . $sitePage->error());
						}
					}

					// Delete existing content blocks for this page
					$contentMessage = new \Content\Message();
					if ($contentMessage->get($pageIndex)) {
						// Content block exists, delete it
						$contentMessage->delete();
						if ($contentMessage->error()) {
							$page->addError("Error deleting content block for page '{$pageIndex}': " . $contentMessage->error());
						}
					}

					// Import content blocks (only one block per page since target is unique)
					if (isset($marketingData['contentBlocks']) && is_array($marketingData['contentBlocks'])) {
						if (count($marketingData['contentBlocks']) > 0) {
							// Import the first content block (target is unique, so only one block per page)
							$blockData = $marketingData['contentBlocks'][0];
							if (count($marketingData['contentBlocks']) > 1) {
								$page->addWarning("Multiple content blocks provided for page '{$pageIndex}', only importing the first one");
							}

							$contentMessage = new \Content\Message();
							$parameters = array(
								'target' => $pageIndex,
								'name' => $blockData['name'] ?? '',
								'title' => $blockData['title'] ?? '',
								'content' => $blockData['content'] ?? ''
							);

							if ($contentMessage->add($parameters)) {
								$page->appendSuccess("Imported content block for page '{$pageIndex}'");
							} else {
								$page->addError("Error adding content block for page '{$pageIndex}': " . $contentMessage->error());
							}
						}
					}

					// Import page metadata
					if (isset($marketingData['pageMetaData']) && is_array($marketingData['pageMetaData'])) {
						$metadataImported = 0;
						foreach ($marketingData['pageMetaData'] as $metaData) {
							$metaKey = $metaData['key'] ?? '';
							$metaValue = $metaData['value'] ?? '';

							if (!empty($metaKey)) {
								$sitePage->setMetadata($metaKey, $metaValue);
								if ($sitePage->error()) {
									$page->addError("Error setting metadata '{$metaKey}' for page '{$pageIndex}': " . $sitePage->error());
								} else {
									$metadataImported++;
								}
							}
						}
						$page->appendSuccess("Imported {$metadataImported} metadata item(s) for page '{$pageIndex}'");
					}
				}
			} else {
				$page->addError("No marketingContent data found in JSON");
			}
		}
	}
}
