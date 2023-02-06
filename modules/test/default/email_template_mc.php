<?php
    $page = new \Site\Page();

    if ($_REQUEST['method'] == "Send") {
        $email = new \Email\Message();
        $template = new \Content\Template\Shell();

        $email->from($GLOBALS['_config']->site->support_email);
        $email->subject("Test of the templated email system");

        $template_name = $_REQUEST['template'];
        $template_config = $GLOBALS['_config']->support->$template_name;
        if (! file_exists($template_config->template)) {
            $page->addError("Template file '".$template_config->template."' not found");
        }
        else {
            $template->content(file_get_contents($template_config->template));

            $line = $template->newLine('LINE');

            $line->addParam("TICKET.LINE",1);
            $line->addParam("TICKET.PRODUCT_CODE","SF400-XX");
            $line->addParam("TICKET.SERIAL_NUMBER","SN12345");
            $line->addParam("TICKET.DESCRIPTION","This is just broken");

            $line = $template->newLine('LINE');

            $line->addParam("TICKET.LINE",2);
            $line->addParam("TICKET.PRODUCT_CODE","MB400-XX");
            $line->addParam("TICKET.SERIAL_NUMBER","SN44345");
            $line->addParam("TICKET.DESCRIPTION","This is also broken");

            foreach ($_POST as $key => $value) {
                if (preg_match('/^([A-Z\_\.]+)$/',$key)) {
                    $key = preg_replace('/\_/','.',$key,1);
                    $template->addParam($key,$value);
                }
            }
            print "Email contents: <pre>\n";
            print $template->render();
            print "</pre>\n";
        }
    }
    elseif ($_REQUEST['method'] == "Load") {
        $template = new \Content\Template\Shell();

        $template_name = $_REQUEST['template'];
        $template_config = $GLOBALS['_config']->support->$template_name;
        if (! file_exists($template_config->template)) {
            $page->addError("Template file '".$template_config->template."' not found");
        }
        else {
            $template->content(file_get_contents($template_config->template));
            $fields = $template->fields();
        }
    }

    $page->instructions = "Templates from ".TEMPLATES."/support";
    $templates = scandir(TEMPLATES."/support");