<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('edit currencies');

	// Handle User Input
	foreach ($_REQUEST['currency_name'] as $currency_id => $currency_name) {
		$currency_symbol = $_REQUEST['currency_symbol'][$currency_id];

		$currency = new \Sales\Currency($currency_id);
		if (! $currency->exists()) {
			$page->addError("Currency not found");
		}
		else {
			$parameters = [];
			if ($currency_name != $currency->name) $parameters['name'] = $currency_name;
			if ($currency_symbol != $currency->symbol) $parameters['symbol'] = $currency_symbol;
			if (count($parameters) > 0) {
				if ($currency->update($parameters)) $page->appendSuccess("Updated currency $currency_name");
				else $page->addError("Error updating currency: ".$currency->error());
			}
		}
	}

	if (!empty($_REQUEST['new_currency_name'])) {
		$currency = new \Sales\Currency();
		$currency->add(array('name' => $_REQUEST['new_currency_name'], 'symbol' => $_REQUEST['new_currency_symbol']));
		if ($currency->error()) $page->addError("Error adding currency: ".$currency->error());
		else $page->appendSuccess("Added currency ".$currency->name);
	}

	// Load Page Data
	$currencyList = new \Sales\CurrencyList();
	$currencies = $currencyList->find();

	// Page Title and Messaging
	$page->title = "Currency Manager";
	$page->instructions = "Select a currency to edit or click Add Currency to create a new one";
	$page->addBreadcrumb("Sales");
