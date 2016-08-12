<?php
    $_content = new Content();
    if (! $id) $id = $_REQUEST['id'];
    if (! $id) $id = $GLOBALS['_REQUEST_']->query_vars_array[0];
print "ID=$id";
    if ($id)
    {
        $content = $_content->details($id);
    }
?>
