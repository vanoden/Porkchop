<?
	require_once(MODULES."/media/_classes/default.php");

	# Check Permissions
	if (! in_array("media manager",$GLOBALS['_SESSION_']->customer->roles))
	{
		header("location: /_media/browse");
		exit;
	}
	
	# Initialize Class
	$_item = new MediaItem();

	# Fetch Code from Query String if not Posted
	if (! $_REQUEST['code'])
		$_REQUEST['code'] = $GLOBALS['_REQUEST_']->query_vars_array[0];

	# Get Media Item
	$item = $_item->get($_REQUEST['code']);

	# Handle Actions
	if ($_REQUEST['submit'])
	{
		app_log("Admin ".$GLOBALS['_SESSION_']->customer->first_name." editing media item ".$_REQUEST['code'],'notice',__FILE__,__LINE__);
		foreach(array('name','icon','description') as $key)
		{
			$_item->setMeta(
				$item->id,
				$key,
				$_REQUEST[$key]
			);
			if ($_item->error)
			{
				app_log("Error updating item: ".$_item->error,'error',__FILE__,__LINE__);
				$GLOBALS['_page']->error = "Error updating Item";
			}
		}
		$item = $_item->get($_REQUEST['code']);
	}
?>
