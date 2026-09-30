<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('edit currencies');
	$can_proceed = true;

	$is_post = strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
	$submitted = $is_post && !empty($_REQUEST['btn_submit']);
	$posted_names = (isset($_REQUEST['currency_name']) && is_array($_REQUEST['currency_name']))
		? $_REQUEST['currency_name']
		: array();
	$posted_symbols = (isset($_REQUEST['currency_symbol']) && is_array($_REQUEST['currency_symbol']))
		? $_REQUEST['currency_symbol']
		: array();

	// Handle User Input (form POST only; this view is also embedded via r7)
	if ($submitted) {
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'] ?? '')) {
			$page->addError("Invalid request");
			$can_proceed = false;
		}
		else {
			foreach ($posted_names as $currency_id => $currency_name) {
				$currency_symbol = $posted_symbols[$currency_id] ?? '';

				$currency = new \Sales\Currency();
				if ($currency->validInteger($currency_id)) {
					$currency = new \Sales\Currency($currency_id);
					if (!$currency->exists()) {
						$page->addError("Currency not found");
						$can_proceed = false;
					} else {
						$parameters = [];
						if ($currency_name != $currency->name) {
							$parameters['name'] = $currency_name;
						}
						if ($currency_symbol != $currency->symbol) {
							$parameters['symbol'] = $currency_symbol;
						}
						if (count($parameters) > 0) {
							if ($currency->update($parameters)) {
								$page->appendSuccess("Updated currency $currency_name");
							} else {
								$page->addError("Error updating currency: " . $currency->error());
								$can_proceed = false;
							}
						}
					}
				} else {
					$page->addError("Invalid currency ID");
					$can_proceed = false;
				}
			}

			if ($can_proceed && !empty($_REQUEST['new_currency_name'])) {
				$currency = new \Sales\Currency();
				$currency->add(array(
					'name' => $_REQUEST['new_currency_name'],
					'symbol' => $_REQUEST['new_currency_symbol'] ?? '',
				));
				if ($currency->error()) {
					$page->addError("Error adding currency: " . $currency->error());
				} else {
					$page->appendSuccess("Added currency " . $currency->name);
				}
			}
		}
	}

	// Load Page Data
	$currencyList = new \Sales\CurrencyList();
	$currencies = $currencyList->find();
	if (!is_array($currencies)) $currencies = array();

	// Page Title and Messaging
	$page->title = "Currency Manager";
	$page->setAdminMenuSection("Sales");  // Keep Sales section open
	$page->instructions = "Select a currency to edit or click Add Currency to create a new one";
	$page->addBreadcrumb("Sales");
