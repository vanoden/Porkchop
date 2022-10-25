<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole('support user');

	if ($_REQUEST['organization_id']) {
		$organization = new \Register\Organization($_REQUEST['organization_id']);
		if ($organization->id) {
			$customers = $organization->members('human');
			if ($organization->error) {
				$page->addError("Error finding customers: ".$organization->error);
			} elseif (isset($_REQUEST['btn_submit'])) {
			
				$request = new \Support\Request();
				$request->add(
					array(
						"date_request"	=> $_REQUEST['date_request'],
						"customer_id"	=> $_REQUEST['requestor_id'],
						"type"			=> 'SERVICE',
						"status"		=> $_REQUEST['status']
					)
				);
				if ($request->id) {
				
					if (isset($_REQUEST['description']) && strlen($_REQUEST['description']) > 0) {
						$parameters = array(
								'product_id'	=> 0,
								'line'			=> 0,
								'description'	=> $_REQUEST['description'],
								"quantity"		=> 0
						);
						
						$request->addItem($parameters);
						if ($request->error()) $page->addError("Error adding message: ".$request->error());						
						
                        $body = "The following service request was submitted:
	                        Request: ".$request->code."<br>
	                        Type: ".$_REQUEST['type']."<br>
	                        URL: http://".$GLOBALS['_config']->site->hostname."/_support/request_detail/".$request->code;
					}
					
                    if ($_REQUEST['type'] == 'gas monitor') {
					    foreach ($_REQUEST['product_id'] as $line => $pid) {
						    print "<br>Line $line, Product ".$_REQUEST['product_id'][$line].", Serial ".$_REQUEST['serial_number'][$line];
						    if (! $_REQUEST['product_id'][$line] && ! $_REQUEST['serial_number'][$line] && ! $_REQUEST['line_description'][$line]) continue;
						    $item = array(
							    'line'			=> $line,
							    'product_id'	=> $_REQUEST['product_id'][$line],
							    'serial_number'	=> $_REQUEST['serial_number'][$line],
							    'description'	=> $_REQUEST['line_description'][$line],
							    'quantity'		=> 1
						    );
						        $item = $request->addItem($item);
						    if ($request->error()) $page->addError("Error adding item to request: ".$request->error());
			                    
                                // internal user get the URL, external just the product details
				                $body .= "<br>
	                                Ticket: <a href='http://".$GLOBALS['_config']->site->hostname."/_support/request_item/".$item->id."'>".$item->id."</a><br>
	                                &nbsp;&nbsp;".$item->product->code." ".$item->serial_number.": ".$item->description;

				                $requestorBody .= "<br>&nbsp;&nbsp;".$item->product->code." ".$item->serial_number.": ".$item->description;    
					    }
                    } else {
                        $body .= "<br>
	                        Ticket: <a href='http://".$GLOBALS['_config']->site->hostname."/_support/request_item/".$item->id."'>".$item->id."</a><br>
	                        &nbsp;&nbsp;".$item->description;
					}
                    
                    $message = new \Email\Message(
			            array(
				            'from'	=> 'service@spectrosinstruments.com',
				            'subject'	=> "[SUPPORT] New Request #".$request->code." from ".$request->customer->full_name(),
				            'body'		=> $body
			            )
		            );
		            $message->html(true);
		            
                    $role = new \Register\Role();
                    $role->get('support user');
                    $role->notify($message);
                    if ($role->error) app_log("Error sending request notification: ".$role->error);
                    
	    			$member = new \Register\Person($member->id);
    				app_log("Sending notification to '".$member->code."' about contact form",'debug',__FILE__,__LINE__);
	    			$member->notify($message);                    
                    
				} elseif($request->error()) {
					$page->addError($request->error());
				}
				
				if (! $page->errorCount()) {
					header('location: /_support/request_items');
					exit;
			    }
		    }
		} else {
			$page->addError("Organization not found");
		}
	}
	
	$organizationlist = new \Register\OrganizationList();
	$organizations = $organizationlist->find();

	$productlist = new \Product\ItemList();
	$products = $productlist->find(array('type'=> array('inventory','unique','kit')));
