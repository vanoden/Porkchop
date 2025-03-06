<?php
$page = new \Site\Page();
$page->requirePrivilege('manage content');
$can_proceed = true;

// Create message object for validation
$message = new \Content\Message();

// Validate required parameters
if (empty($_REQUEST['id'])) {
    $page->addError("Message ID is required");
    $can_proceed = false;
} elseif (!$message->validInteger($_REQUEST['id'])) {
    $page->addError("Invalid message ID format");
    $can_proceed = false;
}

if (empty($_REQUEST['object'])) {
    $page->addError("Object type is required");
    $can_proceed = false;
} elseif (!$message->validCode($_REQUEST['object'])) {
    $page->addError("Invalid object type format");
    $can_proceed = false;
}

if (empty($_REQUEST['content'])) {
    $page->addError("Content is required");
    $can_proceed = false;
} elseif (!$message->safeString($_REQUEST['content'])) {
    $page->addError("Invalid content format");
    $can_proceed = false;
}

// Process content if validation passed
if ($can_proceed) {
    $message = new \Content\Message($_REQUEST['id']);
    if (!$message->exists()) {
        $page->addError("Message not found");
        $can_proceed = false;
    } else {
        $message->update(array('content' => $_REQUEST['content']));
        if ($message->error()) {
            $page->addError("Error updating content: " . $message->error());
        } else {
            $page->appendSuccess("Content updated successfully");
        }
    }
}
