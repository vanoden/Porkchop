<!-- Page Header -->
<?=$page->showBreadcrumbs()?>
<?=$page->showTitle()?>
<?=$page->showMessages()?>
<!-- End Page Header -->
<form method="post">
<?php   if (empty($_REQUEST['template'])) { ?>
<select name="template">
<?php   foreach ($templates as $template) {
    if (preg_match('/^\./',$template)) continue;
    $template = explode(".",$template)[0];
?>
    <option value="<?=$template?>"><?=$template?>
<?php   } ?>
</select>
<input type="submit" name="method" value="Load" />
<?php   }
        elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == "Load") {
            foreach ($fields as $field) { ?>
<div>
    <span><?=$field?></span>
    <input type="text" name="<?=$field?>" />
</div>
<?php       } ?>
<input type="hidden" name="template" value="<?=$_REQUEST['template']?>" />
<input type="submit" name="method" value="Send" />
<?php   }
?>
</form>