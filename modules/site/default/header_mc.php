<?php

	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage headers');

	if (!empty($_REQUEST['id'])) {
		$id = $_REQUEST['id'];
		if (!is_numeric($id)) {
			$page->addError("Invalid header ID format");
			http_response_code(400);
		}
		else {
			$header = new \Site\Header($id);
			if (!$header->exists()) {
				$page->addError("Requested Header not found");
				http_response_code(404);
			}
		}
	}
	else {
		$header = new \Site\Header();
	}

	if (!empty($_REQUEST['btn_submit'])) {
		$csrfToken = $_REQUEST['csrfToken'] ?? '';
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($csrfToken)) {
			$page->addError("Invalid Token");
		}
		else {
			$name = $_REQUEST['name'] ?? '';
			$value = $_REQUEST['value'] ?? '';

			if (empty($name) || empty($value)) {
				$page->addError("Name and Value are required");
			}
			elseif (!$header->validName($name) || !$header->validContent($value)) {
				$page->addError("Invalid Name or Value format");
			}
			else {
				$parameters = array(
					'name' => $name,
					'value' => $value
				);
				if ($header->id() > 0) {
					$header->update($parameters);
					$page->appendSuccess("Header updated successfully");
				}
				else {
					$header->add($parameters);
					$page->appendSuccess("Header created successfully");
				}
			}
		}
	}
print_r($header);
	$page->addBreadcrumb('Headers', '/_site/headers');
	if ($header->id() > 0) {
		$page->title('Edit Header');
		$page->addBreadcrumb('Edit Header', '/_site/header?id='.$header->id());
	}
	else {
		$page->title('Add Header');
		$page->addBreadcrumb('Add Header', '/_site/header');
	}