<?php
    $page = new \Site\Page();
    $site = new \Site();

    if ($_REQUEST['method'] == "Send") {
        // Initialize Selected Template
        $template = new \Content\Template\Shell();
        $template_name = $_REQUEST['template'];
        $template_config = $GLOBALS['_config']->support->$template_name;

        // Initialize Email Message
        $email = new \Email\Message();
        $email->from($template_config->from);
        $email->subject($template_config->subject);
        $email->html(true);

        // Confirm Template File Exists
        if (! file_exists($template_config->template)) {
            $page->addError("Template file '".$template_config->template."' not found");
        }
        else {
            // Load Template
            $template->content(file_get_contents($template_config->template));

            // Apply parameters to the email body
            foreach ($_POST as $key => $value) {
                if (preg_match('/^([A-Z\_\.]+)$/',$key)) {
                    $key = preg_replace('/\_/','.',$key,1);
                    $template->addParam($key,$value);
                }
            }

            // Load Identified Group from Content (Identified here with ${GROUP} and ${-GROUP} tags)
            // Fields within the group will be prepended with a '-' in the template
			$line_group = $template->group('GROUP');

            // Add a Line to the group
            $line = $line_group->addLine();

            // Apply parameters to the line
            $line->addParam("TICKET.URL",$site->url()."/_support/request_item?id=000123");
            $line->addParam("TICKET.NUMBER","000123");
            $line->addParam("TICKET.PRODUCT_CODE","SF400-XX");
            $line->addParam("TICKET.SERIAL_NUMBER","SN12345");
            $line->addParam("TICKET.DESCRIPTION","This is just broken");

            // Add another line to the group
            $line = $line_group->addLine();

            // Apply parameters to the line
            $line->addParam("TICKET.URL",$site->url()."/_support/request_item?id=000124");
            $line->addParam("TICKET.NUMBER","000124");
            $line->addParam("TICKET.PRODUCT_CODE","MB400-XX");
            $line->addParam("TICKET.SERIAL_NUMBER","SN44345");
            $line->addParam("TICKET.DESCRIPTION","This is also broken");

            // Add template contents to body
            $email->body($template->render());

            // Options for Sending Email
            // Send to a single person
            $GLOBALS['_SESSION_']->customer->notify($email);
            if ($GLOBALS['_SESSION_']->customer->error()) {
                $page->addError("Error delivering email: ".$GLOBALS['_SESSION_']->customer->error());
            }
            else {
                $page->appendSuccess("Email delivered");
            }

            // Send to an organization
            //$GLOBALS['_SESSION_']->customer->organization()->notify($email);

            // Send to members of a role
            //$GLOBALS['_SESSION_']->customer->roles()[0]->notify($email);

            // Show contents on screen
            print "Email contents: <pre>\n";
            print $template->render();
            print "</pre>\n";
        }
    }
    elseif ($_REQUEST['method'] == "Load") {
        // Initialize Selected Template
        $template = new \Content\Template\Shell();
        $template_name = $_REQUEST['template'];
        $template_config = $GLOBALS['_config']->support->$template_name;
		if (empty($template_config)) {
			$page->addError("Configuration support->".$template_name." not found");
		}
        elseif (! file_exists($template_config->template)) {
            $page->addError("Template file '".$template_config->template."' not found");
        }
        else {
            // Load Template Content
            $template->content(file_get_contents($template_config->template));

            // Identify Fields to be Populated - Doesn't support Groups, yet
            $fields = $template->fields();
        }
    }

    $page->instructions = "Templates from ".TEMPLATES."/support";
    $templates = scandir(TEMPLATES."/support");