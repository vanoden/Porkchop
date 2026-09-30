<?php
$page = new \Site\Page();
$page->requirePrivilege('manage products');

$validationClass = new \Product\Item();

if ($validationClass->validCode($_REQUEST['code'] ?? null)) {
	$item = new \Product\Item();
	$item->get($_REQUEST['code']);
	if (!$item->id) $page->addError("Item not found");
}
elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0]) && $validationClass->validCode($GLOBALS['_REQUEST_']->query_vars_array[0])) {
	$item = new \Product\Item();
	$item->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
	if (!$item->id) $page->addError("Item not found");
}

if (!isset($item) || !is_object($item)) {
	$item = new \Product\Item();
}

$metadataKeys = $item->getInstanceMetadataKeys();
if (!is_array($metadataKeys)) $metadataKeys = array();

if (!empty($_REQUEST['updateMetadata'])) {
	$csrfToken = $_POST['csrfToken'] ?? '';
	if (!$GLOBALS['_SESSION_']->verifyCSRFToken($csrfToken)) {
		$page->addError("Invalid Request");
	}
	elseif (!$item->id) {
		$page->addError("Invalid product: cannot update metadata without a valid product ID");
	}
	else {
		foreach ($metadataKeys as $meta_field) {
			if (!isset($_REQUEST[$meta_field])) continue;
			$value = trim($_REQUEST[$meta_field]);
			if ($value !== '' && !$item->validText($value) && !$item->validInteger($value)) {
				$page->addError("Invalid value for " . $meta_field);
				continue;
			}
			$currentValue = $item->getMetadata($meta_field);
			if (strval($currentValue) !== strval($value)) {
				$item->setMetadata($meta_field, $value);
				if ($item->error()) $page->addError("Error setting " . $meta_field . ": " . $item->error());
				else $page->appendSuccess("Updated '" . $meta_field . "'");
			}
		}

		if (!empty($_REQUEST['new_metadata_key']) || !empty($_REQUEST['new_metadata_value'])) {
			$newKey = trim($_REQUEST['new_metadata_key'] ?? '');
			$newValue = trim($_REQUEST['new_metadata_value'] ?? '');
			if ($newKey === '') {
				$page->addError("Metadata key is required");
			}
			elseif ($newValue === '') {
				$page->addError("Metadata value is required");
			}
			elseif (!$item->validMetadataKey($newKey)) {
				$page->addError("Invalid metadata key format");
			}
			elseif (!$item->validMetadataValue($newValue)) {
				$page->addError("Invalid metadata value format");
			}
			elseif ($item->getMetadata($newKey) !== '') {
				$page->addError("Metadata key '" . $newKey . "' already exists");
			}
			else {
				$item->setMetadata($newKey, $newValue);
				if ($item->error()) $page->addError("Error adding metadata: " . $item->error());
				else {
					$page->appendSuccess("Added new metadata '" . $newKey . "'");
					$metadataKeys = $item->getInstanceMetadataKeys();
					if (!is_array($metadataKeys)) $metadataKeys = array();
				}
			}
		}
	}
}

if (!empty($_REQUEST['deleteMetadata'])) {
	if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) {
		$page->addError("Invalid Request");
	}
	else {
		$deleteKey = trim($_REQUEST['delete_metadata_key'] ?? '');
		if ($deleteKey === '') {
			$page->addError("Metadata key is required for deletion");
		}
		else {
			$item->unsetMetadata($deleteKey);
			if ($item->error()) $page->addError("Error deleting metadata: " . $item->error());
			else {
				$page->appendSuccess("Deleted metadata field '" . $deleteKey . "'");
				$metadataKeys = $item->getInstanceMetadataKeys();
				if (!is_array($metadataKeys)) $metadataKeys = array();
			}
		}
	}
}

if (!empty($_REQUEST['updateMetadata']) || !empty($_REQUEST['deleteMetadata'])) {
	if (!empty($item->id) && !empty($item->code)) {
		$item->get($item->code);
		$metadataKeys = $item->getInstanceMetadataKeys();
		if (!is_array($metadataKeys)) $metadataKeys = array();
	}
}

$page->title("Product Metadata");
$page->setAdminMenuSection("Products");
$page->addBreadcrumb("Products", "/_product/admin_products");
if (!empty($item->id)) $page->addBreadcrumb($item->code, "/_product/admin_product/" . $item->code);
$page->addBreadcrumb("Metadata", "/_product/admin_product_metadata/" . ($item->code ?? ''));
